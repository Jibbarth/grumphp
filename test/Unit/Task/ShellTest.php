<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Shell;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ShellTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Shell(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'scripts' => [],
                'ignore_patterns' => [],
                'whitelist_patterns' => [],
                'triggered_by' => ['php'],
            ]
        ];
        yield 'string_script' => [
            [
                'scripts' => ['phpunit'],
            ],
            [
                'scripts' => [
                    ['phpunit']
                ],
                'ignore_patterns' => [],
                'whitelist_patterns' => [],
                'triggered_by' => ['php'],
            ]
        ];
        yield 'array_script' => [
            [
                'scripts' => [
                    ['phpunit', 'tests']
                ],
            ],
            [
                'scripts' => [
                    ['phpunit', 'tests']
                ],
                'ignore_patterns' => [],
                'whitelist_patterns' => [],
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
        yield 'exitCode1On1Task' => [
            [
                'scripts' => ['phpunit']
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'exitCode1On2Task' => [
            [
                'scripts' => [
                    'phpunit',
                    'phpspec'
                ]
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'.PHP_EOL.'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'noScript' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', self::mockProcess(0));
            }
        ];
        yield 'exitCode0On1Task' => [
            [
                'scripts' => ['phpunit']
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('sh', self::mockProcess(0));
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
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function() {
            }
        ];
        yield 'no-files-after-whitelist-patterns' => [
            [
                'whitelist_patterns' => ['src/'],
            ],
            self::mockContext(RunContext::class, ['config/file.php']),
            function() {
            }
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notatwigfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'string-script' => [
            [
                'scripts' => ['phpunit']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'sh',
            [
                'phpunit',
            ]
        ];

        yield 'array-script' => [
            [
                'scripts' => [['phpunit', 'tests']]
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'sh',
            [
                'phpunit',
                'tests'
            ]
        ];
    }
}
