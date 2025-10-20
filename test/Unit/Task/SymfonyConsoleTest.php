<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SymfonyConsole;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

final class SymfonyConsoleTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new SymfonyConsole(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'default' => [
            [
                'command' => ['task:run'],
            ],
            [
                'bin' => './bin/console',
                'command' => ['task:run'],
                'ignore_patterns' => [],
                'whitelist_patterns' => [],
                'triggered_by' => ['php', 'yml', 'xml'],
                'run_always' => false,
            ]
        ];

        yield 'with-array-command' => [
            [
                'command' => ['task:run', '--env', 'dev', '-vvv'],
            ],
            [
                'bin' => './bin/console',
                'command' => [
                    'task:run',
                    '--env',
                    'dev',
                    '-vvv'
                ],
                'ignore_patterns' => [],
                'whitelist_patterns' => [],
                'triggered_by' => ['php', 'yml', 'xml'],
                'run_always' => false,
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
            [
                'command' => ['--version']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            function() {
                $process = self::mockProcess(1);
                $this->mockProcessBuilder('php', $process);
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [
                'command' => ['--version']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            function() {
                $this->mockProcessBuilder('php', self::mockProcess());
            }
        ];

        yield 'exitCode0WhenRunAlways' => [
            [
                'command' => ['--version'],
                'run_always' => true,
            ],
            self::mockContext(RunContext::class, ['non-related.log']),
            function() {
                $this->mockProcessBuilder('php', self::mockProcess());
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [
                'command' => ['task:run']
            ],
            self::mockContext(RunContext::class),
            function() {
            }
        ];

        yield 'no-files-after-ignore-patterns' => [
            [
                'command' => ['task:run'],
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function() {
            }
        ];

        yield 'no-files-after-whitelist-patterns' => [
            [
                'command' => ['task:run'],
                'whitelist_patterns' => ['src/'],
            ],
            self::mockContext(RunContext::class, ['config/file.php']),
            function() {
            }
        ];

        yield 'no-files-after-triggered-by' => [
            [
                'command' => ['task:run'],
            ],
            self::mockContext(RunContext::class, ['non-trigger-extension.log']),
            function() {
            }
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'single-command' => [
            [
                'command' => ['lint:container']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php',
            [
                './bin/console',
                'lint:container',
            ]
        ];

        yield 'array-command' => [
            [
                'command' => [
                    'task:run',
                    '--env',
                    'dev',
                    '-vvv'
                ]
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php',
            [
                './bin/console',
                'task:run',
                '--env',
                'dev',
                '-vvv'
            ]
        ];
    }
}
