<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\NpmScript;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Process\Process;

class NpmScriptTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new NpmScript(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [
                'script' => 'script',
            ],
            [
                'script' => 'script',
                'triggered_by' => ['js', 'jsx', 'coffee', 'ts', 'less', 'sass', 'scss'],
                'working_directory' => './',
                'is_run_task' => false,
                'silent' => false,
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
                'script' => 'script'
            ],
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('npm', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [
                'script' => 'script'
            ],
            self::mockContext(RunContext::class, ['hello.js']),
            function () {
                $this->mockProcessBuilder('npm', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [
                'script' => 'script'
            ],
            self::mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [
                'script' => 'script'
            ],
            self::mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [
                'script' => 'script'
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'script'
            ]
        ];
        yield 'run-task' => [
            [
                'script' => 'script',
                'is_run_task' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'run',
                'script'
            ]
        ];
        yield 'silent' => [
            [
                'script' => 'script',
                'silent' => true,
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'npm',
            [
                'script',
                '--silent',
            ]
        ];
    }
}
