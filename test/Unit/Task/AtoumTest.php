<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Atoum;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class AtoumTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Atoum(
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
                'bootstrap_file' => null,
                'directories' => [],
                'files' => [],
                'namespaces' => [],
                'methods' => [],
                'tags' => [],
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
                $this->mockProcessBuilder('atoum', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('atoum', self::mockProcess(0));
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
            'atoum',
            []
        ];
        yield 'config' => [
            [
                'config_file' => 'configfile'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '-c',
                'configfile',
            ]
        ];
        yield 'bootstrap-file' => [
            [
                'bootstrap_file' => 'bootstrapfile'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--bootstrap-file',
                'bootstrapfile',
            ]
        ];
        yield 'directories' => [
            [
                'directories' => ['src', 'tst']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--directories',
                'src',
                'tst',
            ]
        ];
        yield 'files' => [
            [
                'files' => ['hello.php', 'hello2.php']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--files',
                'hello.php',
                'hello2.php',
            ]
        ];
        yield 'namespaces' => [
            [
                'namespaces' => ['ns1', 'ns2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--namespaces',
                'ns1',
                'ns2',
            ]
        ];
        yield 'methods' => [
            [
                'methods' => ['method1', 'method2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--methods',
                'method1',
                'method2',
            ]
        ];
        yield 'tags' => [
            [
                'tags' => ['tag1', 'tag2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'atoum',
            [
                '--tags',
                'tag1',
                'tag2',
            ]
        ];
    }
}
