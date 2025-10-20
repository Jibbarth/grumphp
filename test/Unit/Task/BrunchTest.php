<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Brunch;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class BrunchTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Brunch(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'task' => 'build',
                'env' => 'production',
                'jobs' => 4,
                'debug' => false,
                'triggered_by' => ['js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss'],
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
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('brunch', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('brunch', self::mockProcess(0));
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
            self::mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '4',
            ]
        ];
        yield 'task' => [
            [
                'task' => 'sleep',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'sleep',
                '--env',
                'production',
                '--jobs',
                '4',
            ]
        ];
        yield 'env' => [
            [
                'env' => 'acceptation',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'acceptation',
                '--jobs',
                '4',
            ]
        ];
        yield 'jobs' => [
            [
                'jobs' => 10,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '10',
            ]
        ];
        yield 'debug' => [
            [
                'debug' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'brunch',
            [
                'build',
                '--env',
                'production',
                '--jobs',
                '4',
                '--debug',
            ]
        ];
    }
}
