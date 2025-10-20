<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpMnd;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpMndTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpMnd(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'directory' => '.',
                'whitelist_patterns' => [],
                'exclude' => [],
                'exclude_name' => [],
                'exclude_path' => [],
                'extensions' => [],
                'hint' => false,
                'ignore_funcs' => [],
                'ignore_numbers' => [],
                'ignore_strings' => [],
                'strings' => false,
                'triggered_by' => ['php'],
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
                $this->mockProcessBuilder('phpmnd', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('phpmnd', self::mockProcess(0));
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
        yield 'no-files-after-whitelist' => [
            [
                'whitelist_patterns' => ['src/'],
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
            'phpmnd',
            [
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'directory' => [
            [
                'directory' => 'directory'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--suffixes=php',
                'directory',
            ]
        ];
        yield 'exclude' => [
            [
                'exclude' => ['exclude1', 'exclude2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude=exclude1',
                '--exclude=exclude2',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'exclude_name' => [
            [
                'exclude_name' => ['exclude1', 'exclude2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude-file=exclude1',
                '--exclude-file=exclude2',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'exclude_path' => [
            [
                'exclude_path' => ['exclude1', 'exclude2']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--exclude-path=exclude1',
                '--exclude-path=exclude2',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'extensions' => [
            [
                'extensions' => ['php', 'phtml']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--extensions=php,phtml',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'hint' => [
            [
                'hint' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--hint',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'ignore_funcs' => [
            [
                'ignore_funcs' => ['intval', 'floatval', 'strval']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-funcs=intval,floatval,strval',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'ignore_numbers' => [
            [
                'ignore_numbers' => [0,1],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-numbers=0,1',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'ignore_strings' => [
            [
                'ignore_strings' => ['0', '1']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--ignore-strings=0,1',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'strings' => [
            [
                'strings' => true
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--strings',
                '--suffixes=php',
                '.',
            ]
        ];
        yield 'triggered-by' => [
            [
                'triggered_by' => ['php', 'phtml']
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpmnd',
            [
                '--suffixes=php,phtml',
                '.',
            ]
        ];
    }
}
