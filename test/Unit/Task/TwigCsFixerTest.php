<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\TwigCsFixer;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class TwigCsFixerTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new TwigCsFixer(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'triggered_by' => ['twig'],
                'paths' => [],
                'level' => null,
                'config' => null,
                'report' => 'text',
                'no-cache' => false,
                'verbose' => false,
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
            $this->mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twig-cs-fixer', $process = $this->mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twig-cs-fixer', $this->mockProcess(0));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {
            }
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            $this->mockContext(RunContext::class, ['notatwigfile.php']),
            function () {
            }
        ];
        yield 'no-files-in-paths' => [
            ['paths' => ['src']],
            $this->mockContext(RunContext::class, ['other/hello.twig']),
            function () {
            }
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--report=text',
            ]
        ];

        yield 'paths' => [
            [
                'paths' => ['src', 'templates'],
            ],
            $this->mockContext(RunContext::class, ['templates/hello.twig', 'templates/hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                'src',
                'templates',
                '--report=text',
            ]
        ];

        yield 'precommit' => [
            [
                'paths' => ['templates'],
            ],
            $this->mockContext(GitPreCommitContext::class, ['templates/hello.twig', 'templates/hello2.twig', 'other/hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                'templates/hello.twig',
                'templates/hello2.twig',
                '--report=text',
            ]
        ];

        yield 'level' => [
            [
                'level' => 'warning',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--level=warning',
                '--report=text',
            ]
        ];

        yield 'config' => [
            [
                'config' => 'twig-cs-fixer.php',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--config=twig-cs-fixer.php',
                '--report=text',
            ]
        ];

        yield 'no-cache' => [
            [
                'no-cache' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--report=text',
                '--no-cache',
            ]
        ];

        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--report=text',
                '--verbose',
            ]
        ];

        yield 'report' => [
            [
                'report' => 'json',
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                '--report=json',
            ]
        ];

        yield 'default report' => [
            [
                'report' => null,
            ],
            $this->mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
            ]
        ];

        yield 'multiple options' => [
            [
                'paths' => ['src', 'templates'],
                'level' => 'warning',
                'config' => 'twig-cs-fixer.php',
                'no-cache' => true,
                'verbose' => true,
            ],
            $this->mockContext(RunContext::class, ['templates/hello.twig', 'templates/hello2.twig']),
            'twig-cs-fixer',
            [
                'lint',
                'src',
                'templates',
                '--level=warning',
                '--config=twig-cs-fixer.php',
                '--report=text',
                '--no-cache',
                '--verbose',
            ]
        ];
    }
}
