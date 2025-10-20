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
            self::mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twig-cs-fixer', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope',
            FixableTaskResult::class
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twig-cs-fixer', self::mockProcess(0));
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {
            }
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            self::mockContext(RunContext::class, ['notatwigfile.php']),
            function () {
            }
        ];
        yield 'no-files-in-paths' => [
            ['paths' => ['src']],
            self::mockContext(RunContext::class, ['other/hello.twig']),
            function () {
            }
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['templates/hello.twig', 'templates/hello2.twig']),
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
            self::mockContext(GitPreCommitContext::class, ['templates/hello.twig', 'templates/hello2.twig', 'other/hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
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
            self::mockContext(RunContext::class, ['templates/hello.twig', 'templates/hello2.twig']),
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
