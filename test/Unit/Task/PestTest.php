<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Pest;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PestTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Pest(
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
                'testsuite' => null,
                'group' => [],
                'always_execute' => false,
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
                $this->mockProcessBuilder('pest', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('pest', self::mockProcess(0));
            }
        ];
        yield 'no-files-but-always-execute' => [
            [
                'always_execute' => true,
            ],
            self::mockContext(RunContext::class, []),
            function () {
                $this->mockProcessBuilder('pest', self::mockProcess(0));
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
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'pest',
            []
        ];
        yield 'config-file' => [
            [
                'config_file' => 'config.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'pest',
            [
                '--configuration=config.xml',
            ]
        ];
        yield 'testsuite' => [
            [
                'testsuite' => 'suite',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'pest',
            [
                '--testsuite=suite',
            ]
        ];
        yield 'group' => [
            [
                'group' => ['group1','group2',],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'pest',
            [
                '--group=group1,group2',
            ]
        ];
    }
}
