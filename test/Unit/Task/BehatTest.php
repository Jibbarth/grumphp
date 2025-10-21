<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Behat;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class BehatTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Behat(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => null,
                'format' => null,
                'suite' => null,
                'profile' => null,
                'stop_on_failure' => false,
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
                $this->mockProcessBuilder('behat', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('behat', self::mockProcess(0));
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
            'behat',
            []
        ];
        yield 'config' => [
            [
                'config' => 'configfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--config=configfile'
            ]
        ];
        yield 'format' => [
            [
                'format' => 'myformat',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--format=myformat'
            ]
        ];
        yield 'suite' => [
            [
                'suite' => 'suite',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--suite=suite'
            ]
        ];
        yield 'profile' => [
            [
                'profile' => 'profile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--profile=profile'
            ]
        ];
        yield 'stop-on-failure' => [
            [
                'stop_on_failure' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'behat',
            [
                '--stop-on-failure'
            ]
        ];
    }
}
