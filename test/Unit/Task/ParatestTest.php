<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Paratest;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ParatestTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Paratest(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'group' => [],
                'config' => null,
                'processes' => null,
                'functional' => false,
                'phpunit' => null,
                'configuration' => null,
                'always_execute' => false,
                'runner' => null,
                'coverage-clover' => null,
                'coverage-html' => null,
                'coverage-php' => null,
                'coverage-xml' => null,
                'log-junit' => null,
                'testsuite' => null,
                'verbose' => false,
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
                $this->mockProcessBuilder('paratest', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('paratest', self::mockProcess(0));
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
            'paratest',
            []
        ];
        yield 'processes' => [
            [
                'processes' => 10,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--processes=10',
            ]
        ];
        yield 'functional' => [
            [
                'functional' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--functional',
            ]
        ];
        yield 'configuration' => [
            [
                'configuration' => 'phpunit.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--configuration=phpunit.xml',
            ]
        ];
        yield 'runner' => [
            [
                'runner' => 'WrapperRunner',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--runner=WrapperRunner',
            ]
        ];
        yield 'coverage-clover' => [
            [
                'coverage-clover' => 'clover.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-clover=clover.xml',
            ]
        ];
        yield 'coverage-html' => [
            [
                'coverage-html' => 'coverage.html',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-html=coverage.html',
            ]
        ];
        yield 'coverage-php' => [
            [
                'coverage-php' => 'coverage.php',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-php=coverage.php',
            ]
        ];
        yield 'coverage-xml' => [
            [
                'coverage-xml' => 'coverage.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--coverage-xml=coverage.xml',
            ]
        ];
        yield 'testsuite' => [
            [
                'testsuite' => 'testsuite',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--testsuite=testsuite',
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--verbose',
            ]
        ];
        yield 'group' => [
            [
                'group' => ['group1', 'group2'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'paratest',
            [
                '--group=group1,group2',
            ]
        ];
    }
}
