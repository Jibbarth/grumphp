<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Formatter\PhpCsFixerFormatter;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCsFixer;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PhpCsFixerTest extends AbstractExternalTaskTestCase
{
    /**
     * @var PhpCsFixerFormatter|ObjectProphecy
     */
    protected $formatter;

    protected function provideTask(): TaskInterface
    {
        $this->formatter = $this->prophesize(PhpCsFixerFormatter::class);

        return new PhpCsFixer(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'allow_risky' => null,
                'cache_file' => null,
                'config' => null,
                'rules' => [],
                'using_cache' => null,
                'config_contains_finder' => true,
                'verbose' => true,
                'diff' => false,
                'triggered_by' => ['php'],
            ]
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            self::mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            self::mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            self::mockContext()
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('php-cs-fixer', $process = self::mockProcess(1));

                $this->formatter->resetCounter()->shouldBeCalled();
                $this->formatter->format($process)->willReturn($message = 'message');
            },
            'message',
            FixableTaskResult::class
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->formatter->resetCounter()->shouldBeCalled();
                $this->mockProcessBuilder('php-cs-fixer', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--verbose',
                'fix',
            ]
        ];
        yield 'allow-risky' => [
            [
                'allow_risky' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--allow-risky=yes',
                '--verbose',
                'fix',
            ]
        ];
        yield 'cache-file' => [
            [
                'cache_file' => 'cachefile'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--cache-file=cachefile',
                '--verbose',
                'fix',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'config.php'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--config=config.php',
                '--verbose',
                'fix',
            ]
        ];
        yield 'rules-list' => [
            [
                'rules' => ['foo', 'bar']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--rules=foo,bar',
                '--verbose',
                'fix',
            ]
        ];
        yield 'rules-object' => [
            [
                'rules' => $rules = [
                    'foo' => [
                        'bar',
                    ],
                ]
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--rules='.json_encode($rules),
                '--verbose',
                'fix',
            ]
        ];
        yield 'use-cache' => [
            [
                'using_cache' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--using-cache=yes',
                '--verbose',
                'fix',
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => false,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                'fix',
            ]
        ];
        yield 'diff' => [
            [
                'diff' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--verbose',
                '--diff',
                'fix',
            ]
        ];
        yield 'can-intersect-run-with-finder' => [
            [
                'config_contains_finder' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--verbose',
                'fix',
            ]
        ];
        yield 'can-intersect-run-without-finder' => [
            [
                'config_contains_finder' => false,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--verbose',
                'fix',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'can-intersect-pre-commit-without-finder' => [
            [
                'config_contains_finder' => false,
            ],
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--verbose',
                'fix',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'can-intersect-pre-commit-with-finder' => [
            [
                'config_contains_finder' => true,
            ],
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'php-cs-fixer',
            [
                '--format=json',
                '--dry-run',
                '--path-mode=intersection',
                '--verbose',
                'fix',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
