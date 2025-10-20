<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpspec;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpspecTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Phpspec(
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
                'format' => null,
                'stop_on_failure' => false,
                'verbose' => false,
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
                $this->mockProcessBuilder('phpspec', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('phpspec', self::mockProcess(0));
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
            'phpspec',
            [
                'run',
                '--no-interaction',
            ]
        ];
        yield 'config' => [
            [
                'config_file' => 'configile.yml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--config=configile.yml'
            ]
        ];
        yield 'format' => [
            [
                'format' => 'dot',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--format=dot'
            ]
        ];
        yield 'stop-on-failure' => [
            [
                'stop_on_failure' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--stop-on-failure'
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpspec',
            [
                'run',
                '--no-interaction',
                '--verbose'
            ]
        ];
    }
}
