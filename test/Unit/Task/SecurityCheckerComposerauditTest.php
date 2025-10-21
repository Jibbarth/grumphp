<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityCheckerComposeraudit;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class SecurityCheckerComposerauditTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new SecurityCheckerComposeraudit(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'abandoned' => null,
                'format' => null,
                'locked' => true,
                'no_dev' => false,
                'run_always' => false,
                'working_dir' => null,
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
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
            }
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            self::mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
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
        yield 'no-composer-file' => [
            [],
            self::mockContext(RunContext::class, ['thisisnotacomposerfile.lock']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--locked',
            ]
        ];

        yield 'format' => [
            [
                'format' => 'json',
            ],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--format=json',
                '--locked',
            ]
        ];

        yield 'locked' => [
            [
                'locked' => false,
            ],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
            ]
        ];

        yield 'no-dev' => [
            [
                'no_dev' => true,
            ],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--locked',
                '--no-dev',
            ]
        ];

        yield 'abandoned' => [
            [
                'abandoned' => 'ignore',
            ],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--abandoned=ignore',
                '--locked',
            ]
        ];

        yield 'working-dir' => [
            [
                'working_dir' => 'dir',
            ],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'audit',
                '--locked',
                '--working-dir=dir',
            ]
        ];
    }
}
