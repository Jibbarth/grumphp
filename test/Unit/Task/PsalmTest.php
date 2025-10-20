<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Psalm;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PsalmTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Psalm(
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
                'ignore_patterns' => [],
                'no_cache' => false,
                'report' => null,
                'output_format' => null,
                'threads' => null,
                'triggered_by' => ['php'],
                'show_info' => false,
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
                $this->mockProcessBuilder('psalm', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('psalm', self::mockProcess(0));
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
            'psalm',
            [
                '--show-info=false',
            ]
        ];
        yield 'output-format' => [
            [
                'output_format' => 'emacs',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--output-format=emacs',
                '--show-info=false',
            ]
        ];
        yield 'config' => [
            [
                'config' => 'psalm.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--config=psalm.xml',
                '--show-info=false',
            ]
        ];
        yield 'report' => [
            [
                'report' => 'reportfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--report=reportfile',
                '--show-info=false',
            ]
        ];
        yield 'no-cache' => [
            [
                'no_cache' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--no-cache',
                '--show-info=false',
            ]
        ];
        yield 'threads' => [
            [
                'threads' => 10,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--threads=10',
                '--show-info=false',
            ]
        ];
        yield 'show-info' => [
            [
                'show_info' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--show-info=true',
            ]
        ];
        yield 'with-files' => [
            [],
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'psalm',
            [
                '--show-info=false',
                'hello.php',
                'hello2.php',
            ]
        ];
    }
}
