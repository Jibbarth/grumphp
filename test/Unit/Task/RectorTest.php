<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Formatter\RawProcessFormatter;
use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Rector;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class RectorTest extends AbstractExternalTaskTestCase
{
    /**
     * @var ProcessFormatterInterface|ObjectProphecy
     */
    protected $formatter;

    protected function provideTask(): TaskInterface
    {
        $this->formatter = $this->prophesize(RawProcessFormatter::class);

        return new Rector(
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
                'triggered_by' => ['php'],
                'ignore_patterns' => [],
                'clear_cache' => true,
                'no_diffs' => false,
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
                $this->mockProcessBuilder('rector', $process = self::mockProcess(1));

                $this->formatter->format($process)->willReturn($message = 'message');
            },
            'message',
            FixableTaskResult::class
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('rector', self::mockProcess(0));
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
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--ansi',
                '--no-progress-bar',
                '--clear-cache',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'rector-config.php',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--ansi',
                '--no-progress-bar',
                '--config=rector-config.php',
                '--clear-cache',
            ]
        ];
        yield 'no-clear-cache' => [
            [
                'clear_cache' => false,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--ansi',
                '--no-progress-bar',
            ]
        ];
        yield 'no-diffs' => [
            [
                'no_diffs' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'rector',
            [
                'process',
                '--dry-run',
                '--ansi',
                '--no-progress-bar',
                '--clear-cache',
                '--no-diffs'
            ]
        ];
    }
}
