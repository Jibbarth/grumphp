<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Deptrac;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class DeptracTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Deptrac(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'cache_file' => null,
                'depfile' => null,
                'formatter' => null,
                'output' => null,
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
                $this->mockProcessBuilder('deptrac', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('deptrac', self::mockProcess(0));
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
            'deptrac',
            [
                'analyse',
            ]
        ];
        yield 'cache-file' => [
            [
                'cache_file' => 'example.cache',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--cache-file=example.cache',
            ]
        ];
        yield 'formatter-graphviz' => [
            [
                'formatter' => 'graphviz-display',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-display',
            ]
        ];
        yield 'formatter-graphviz-dump-image' => [
            [
                'formatter' => 'graphviz-image',
                'output' => 'file.jpg',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-image',
                '--output=file.jpg',
            ]
        ];
        yield 'formatter-graphviz-dump-dot' => [
            [
                'formatter' => 'graphviz-dot',
                'output' => 'file.dot',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-dot',
                '--output=file.dot',
            ]
        ];
        yield 'formatter-graphviz-dump-html' => [
            [
                'formatter' => 'graphviz-html',
                'output' => 'file.html',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=graphviz-html',
                '--output=file.html',
            ]
        ];
        yield 'depfile' => [
            [
                'depfile' => 'depfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--config-file=depfile',
            ]
        ];
        yield 'formatter-junit' => [
            [
                'formatter' => 'junit',
                'output' => 'junit.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=junit',
                '--output=junit.xml',
            ]
        ];
        yield 'formatter-xml' => [
            [
                'formatter' => 'xml',
                'output' => 'file.xml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=xml',
                '--output=file.xml',
            ]
        ];
        yield 'formatter-baseline' => [
            [
                'formatter' => 'baseline',
                'output' => 'baseline.yaml',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'deptrac',
            [
                'analyse',
                '--formatter=baseline',
                '--output=baseline.yaml',
            ]
        ];
    }
}
