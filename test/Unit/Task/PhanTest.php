<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhanTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Phan(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'output_mode' => 'text',
                'output' => null,
                'config_file' => '.phan/config.php',
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
                $this->mockProcessBuilder('phan', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('phan', self::mockProcess(0));
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
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'text',
            ]
        ];
        yield 'config-file' => [
            [
                'config_file' => 'config.php',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                'config.php',
                '--output-mode',
                'text',
            ]
        ];
        yield 'output-mode' => [
            [
                'output_mode' => 'json',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'json',
            ]
        ];
        yield 'output' => [
            [
                'output' => 'file.txt',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phan',
            [
                '--config-file',
                '.phan/config.php',
                '--output-mode',
                'text',
                '--output',
                'file.txt'
            ]
        ];
    }
}
