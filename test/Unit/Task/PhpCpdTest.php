<?php

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpCpd;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpCpdTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpCpd(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=.php',
                '.',
            ],
        ];

        yield 'directory' => [
            [
                'directory' => ['folder-1', 'folder-2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=.php',
                'folder-1',
                'folder-2',
            ],
        ];

        yield 'exclude' => [
            [
                'exclude' => ['folder-1', 'folder-2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=folder-1',
                '--exclude=folder-2',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=.php',
                '.',
            ],
        ];

        yield 'fuzzy' => [
            [
                'fuzzy' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=.php',
                '--fuzzy',
                '.',
            ],
        ];

        yield 'min_lines' => [
            [
                'min_lines' => 10
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=10',
                '--min-tokens=70',
                '--suffix=.php',
                '.',
            ],
        ];

        yield 'min_tokens' => [
            [
                'min_tokens' => 10
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=10',
                '--suffix=.php',
                '.',
            ],
        ];

        yield 'triggered_by' => [
            [
                'triggered_by' => ['php', 'html']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpcpd',
            [
                '--exclude=vendor',
                '--min-lines=5',
                '--min-tokens=70',
                '--suffix=.php',
                '--suffix=.html',
                '.',
            ],
        ];
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'directory' => ['.'],
                'exclude' => ['vendor'],
                'fuzzy' => false,
                'min_lines' => 5,
                'min_tokens' => 70,
                'triggered_by' => ['php'],
            ],
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            self::mockContext(RunContext::class),
        ];

        yield 'pre-commit-context' => [
            true,
            self::mockContext(GitPreCommitContext::class),
        ];

        yield 'other' => [
            false,
            self::mockContext(),
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcpd', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            TaskResult::class,
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpcpd', self::mockProcess(0));
            },
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {
            },
        ];

        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {
            },
        ];
    }
}
