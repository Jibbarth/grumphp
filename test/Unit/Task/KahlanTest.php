<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Kahlan;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class KahlanTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Kahlan(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => 'kahlan-config.php',
                'src' => ['src'],
                'spec' => ['spec'],
                'pattern' => '*Spec.php',
                'reporter' => null,
                'coverage' => null,
                'clover' => null,
                'istanbul' => null,
                'lcov' => null,
                'ff' => 0,
                'no_colors' => false,
                'no_header' => false,
                'include' => ['*'],
                'exclude' => [],
                'persistent' => true,
                'cc' => false,
                'autoclear' => [
                    'Kahlan\Plugin\Monkey',
                    'Kahlan\Plugin\Call',
                    'Kahlan\Plugin\Stub',
                    'Kahlan\Plugin\Quit',
                ],
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
                $this->mockProcessBuilder('kahlan', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('kahlan', self::mockProcess(0));
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

    /**
     * TODO : This task seems to be bogus ... Needs some fixin
     */
    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'kahlan',
            [
                'config',
                'src',
                'src',
                'spec',
                'spec',
                '--pattern',
                '--persistent',
                '--autoclear',
            ]
        ];
    }
}
