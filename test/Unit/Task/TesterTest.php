<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\Tester;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class TesterTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Tester(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'path' => '.',
                'always_execute' => false,
                'log' => null,
                'show_information_about_skipped_tests' => false,
                'stop_on_fail' => false,
                'parallel_processes' => null,
                'output' => null,
                'temp' => null,
                'setup' => null,
                'colors' => null,
                'coverage' => null,
                'coverage_src' => null,
                'php_ini_configuration_path' => null,
                'default_php_ini_configuration' => false,
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
            self::mockContext(RunContext::class, ['helloTest.php']),
            function () {
                $this->mockProcessBuilder('tester', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['helloTest.php']),
            function () {
                $this->mockProcessBuilder('tester', self::mockProcess(0));
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
        yield 'no-files-after-name_match' => [
            [],
            self::mockContext(RunContext::class, ['notatestfile.php']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
            ]
        ];
        yield 'path' => [
            [
                'path' => 'src',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                'src',
            ]
        ];
        yield 'always_execute_with_files' => [
            [
                'always_execute' => true,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
            ]
        ];
        yield 'always_execute_without_files' => [
            [
                'always_execute' => true,
            ],
            self::mockContext(RunContext::class, []),
            'tester',
            [
                '.',
            ]
        ];
        yield 'log' => [
            [
                'log' => 'logfile.txt',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--log',
                'logfile.txt',
            ]
        ];
        yield 'show_information_about_skipped_tests' => [
            [
                'show_information_about_skipped_tests' => true,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-s',
            ]
        ];
        yield 'stop_on_fail' => [
            [
                'stop_on_fail' => true,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--stop-on-fail',
            ]
        ];
        yield 'parallel_processes' => [
            [
                'parallel_processes' => 2,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-j',
                '2'
            ]
        ];
        yield 'output' => [
            [
                'output' => 'console',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-o',
                'console',
            ]
        ];
        yield 'temp' => [
            [
                'temp' => '/tmp',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--temp',
                '/tmp',
            ]
        ];
        yield 'setup' => [
            [
                'setup' => 'setup.php',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--setup',
                'setup.php',
            ]
        ];
        yield 'colors' => [
            [
                'colors' => 4,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--colors',
                '4',
            ]
        ];
        yield 'coverage' => [
            [
                'coverage' => 'coverageFile',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--coverage',
                'coverageFile',
            ]
        ];
        yield 'coverage_src' => [
            [
                'coverage_src' => 'coverageSrdFile',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '--coverage-src',
                'coverageSrdFile',
            ]
        ];
        yield 'php_ini_configuration_path' => [
            [
                'php_ini_configuration_path' => 'customPhpIniFile',
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-c',
                'customPhpIniFile',
            ]
        ];
        yield 'default_php_ini_configuration' => [
            [
                'default_php_ini_configuration' => true,
            ],
            self::mockContext(RunContext::class, ['helloTest.php', 'hello2Test.php']),
            'tester',
            [
                '.',
                '-C',
            ]
        ];
    }
}
