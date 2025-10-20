<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Codeception;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class CodeceptionTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Codeception(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config_file' => null,
                'suite' => null,
                'test' => null,
                'fail_fast' => false,
                'xml' => false,
                'html' => false,
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
                $this->mockProcessBuilder('codecept', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('codecept', self::mockProcess(0));
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
            'codecept',
            [
                'run',
            ]
        ];
        yield 'config' => [
            [
                'config_file' => 'configfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--config=configfile'
            ]
        ];
        yield 'fail-fast' => [
            [
                'fail_fast' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--fail-fast'
            ]
        ];
        yield 'suite' => [
            [
                'suite' => 'suite',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                'suite'
            ]
        ];
        yield 'test' => [
            [
                'test' => 'test',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                'test'
            ]
        ];
        yield 'xml' => [
            [
                'xml' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--xml'
            ]
        ];
        yield 'html' => [
            [
                'html' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'codecept',
            [
                'run',
                '--html'
            ]
        ];
    }
}
