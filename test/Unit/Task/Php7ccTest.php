<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Php7cc;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class Php7ccTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Php7cc(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'exclude' => [],
                'level' => null,
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
        yield 'exitCode1ErrorOutput' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $process = self::mockProcess(1, 'File: hello.php ....');
                $this->mockProcessBuilder('php7cc', $process);
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0NoOutput' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('php7cc', self::mockProcess(0, ''));
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
            'php7cc',
            [
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'level' => [
            [
                'level' => 'error'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php7cc',
            [
                '--level=error',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'except' => [
            [
                'exclude' => ['exclude1', 'exclude2'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'php7cc',
            [
                '--except',
                'exclude1',
                '--except',
                'exclude2',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
