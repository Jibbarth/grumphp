<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Grunt;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class GruntTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Grunt(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'grunt_file' => null,
                'task' => null,
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
                $this->mockProcessBuilder('grunt', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('grunt', self::mockProcess(0));
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
            'grunt',
            []
        ];

        yield 'grunt-file' => [
            [
                'grunt_file' => 'Gruntfile'
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'grunt',
            [
                '--gruntfile=Gruntfile'
            ]
        ];

        yield 'task' => [
            [
                'task' => 'mytask',
            ],
            self::mockContext(RunContext::class, ['hello.js', 'hello2.js']),
            'grunt',
            [
                'mytask',
            ]
        ];
    }
}
