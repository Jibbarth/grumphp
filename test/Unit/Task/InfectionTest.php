<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Infection;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class InfectionTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Infection(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'threads' => null,
                'test_framework' => null,
                'only_covered' => false,
                'show_mutations' => false,
                'verbose' => false,
                'configuration' => null,
                'min_msi' => null,
                'min_covered_msi' => null,
                'mutators' => [],
                'ignore_patterns' => [],
                'triggered_by' => ['php'],
                'skip_initial_tests' => false,
                'coverage' => null
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
                $this->mockProcessBuilder('infection', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('infection', self::mockProcess(0));
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
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
            ]
        ];
        yield 'threads' => [
            [
                'threads' => 5,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--threads=5'
            ]
        ];
        yield 'test-framework' => [
            [
                'test_framework' => 'phpunit',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--test-framework=phpunit'
            ]
        ];
        yield 'only-covered' => [
            [
                'only_covered' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--only-covered'
            ]
        ];
        yield 'show-mutations' => [
            [
                'show_mutations' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--show-mutations'
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '-v'
            ]
        ];
        yield 'configuration' => [
            [
                'configuration' => 'file',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--configuration=file'
            ]
        ];
        yield 'min-msi' => [
            [
                'min_msi' => 100,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--min-msi=100'
            ]
        ];
        yield 'min-covered-msi' => [
            [
                'min_covered_msi' => 100,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--min-covered-msi=100'
            ]
        ];
        yield 'mutators' => [
            [
                'mutators' => ['A', 'B', 'C'],
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--mutators=A,B,C'
            ]
        ];
        yield 'with_filtered_files' => [
            [
            ],
            self::mockContext(GitPreCommitContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--filter=hello.php,hello2.php'
            ]
        ];
        yield 'skip-initial-tests' => [
            [
                'skip_initial_tests' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--skip-initial-tests'
            ]
        ];
        yield 'coverage' => [
            [
                'coverage' => '/path/to/coverage',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'infection',
            [
                '--no-interaction',
                '--ignore-msi-with-no-mutations',
                '--coverage=/path/to/coverage'
            ]
        ];
    }
}
