<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Ecs;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class EcsTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Ecs(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'paths' => [],
                'clear-cache' => false,
                'no-progress-bar' => true,
                'config' => null,
                'level' => null,
                'triggered_by' => ['php'],
                'files_on_pre_commit' => false,
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
                $this->mockProcessBuilder('ecs', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('ecs', self::mockProcess(0));
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
        yield 'no-files-after-path' => [
            [
                'paths' => ['src']
            ],
            self::mockContext(RunContext::class, ['test/notinsource.php']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'ecs',
            [
                'check',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
            ]
        ];

        yield 'paths' => [
            [
                'paths' => ['src/', 'test/'],
            ],
            self::mockContext(RunContext::class, ['src/hello.php', 'test/hello2.php']),
            'ecs',
            [
                'check',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
                'src/',
                'test/',
            ]
        ];

        yield 'files_on_pre_commit_in_run_context' => [
            [
                'paths' => ['src/'],
                'files_on_pre_commit' => true,
            ],
            self::mockContext(RunContext::class, ['src/hello.php', 'test/hello2.php']),
            'ecs',
            [
                'check',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
                'src/',
            ]
        ];

        yield 'files_on_pre_commit' => [
            [
                'paths' => ['src/'],
                'files_on_pre_commit' => true,
            ],
            self::mockContext(GitPreCommitContext::class, ['src/hello.php', 'test/hello2.php']),
            'ecs',
            [
                'check',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
                'src/hello.php',
            ]
        ];

        yield 'clear-cache' => [
            [
                'clear-cache' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'ecs',
            [
                'check',
                '--clear-cache',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
            ]
        ];
        yield 'progress-bar' => [
            [
                'no-progress-bar' => false,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'ecs',
            [
                'check',
                '--ansi',
                '--no-interaction',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'configfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'ecs',
            [
                'check',
                '--config=configfile',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
            ]
        ];
        yield 'level' => [
            [
                'level' => 'PSR-2',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'ecs',
            [
                'check',
                '--level=PSR-2',
                '--no-progress-bar',
                '--ansi',
                '--no-interaction',
            ]
        ];
    }
}
