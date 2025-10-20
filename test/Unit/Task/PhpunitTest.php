<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Phpunit;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class PhpunitTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Phpunit(
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
                'testsuite' => null,
                'group' => [],
                'exclude_group' => [],
                'always_execute' => false,
                'order' => null,
                'coverage-clover' => null,
                'coverage-html' => null,
                'coverage-php' => null,
                'coverage-xml' => null,
            ]
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            $this->mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            $this->mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            $this->mockContext()
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpunit', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->mockProcessBuilder('phpunit', $this->mockProcess(0));
            }
        ];
        yield 'no-files-but-always-execute' => [
            [
                'always_execute' => true,
            ],
            $this->mockContext(RunContext::class, []),
            function () {
                $this->mockProcessBuilder('phpunit', $this->mockProcess(0));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            []
        ];
        yield 'config-file' => [
            [
                'config_file' => 'config.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--configuration=config.xml',
            ]
        ];
        yield 'testsuite' => [
            [
                'testsuite' => 'suite',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--testsuite=suite',
            ]
        ];
        yield 'group' => [
            [
                'group' => ['group1','group2',],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--group=group1,group2',
            ]
        ];
        yield 'exclude-group' => [
            [
                'exclude_group' => ['group1','group2',],
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--exclude-group=group1,group2',
            ]
        ];
        yield 'random order' => [
            [
                'order' => 'random',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                'order' => '--order-by=random',
            ]
        ];
        yield 'coverage-clover' => [
            [
                'coverage-clover' => 'clover.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--coverage-clover=clover.xml',
            ]
        ];
        yield 'coverage-html' => [
            [
                'coverage-html' => 'coverage.html',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--coverage-html=coverage.html',
            ]
        ];
        yield 'coverage-php' => [
            [
                'coverage-php' => 'coverage.php',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--coverage-php=coverage.php',
            ]
        ];
        yield 'coverage-xml' => [
            [
                'coverage-xml' => 'coverage.xml',
            ],
            $this->mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phpunit',
            [
                '--coverage-xml=coverage.xml',
            ]
        ];
    }
}
