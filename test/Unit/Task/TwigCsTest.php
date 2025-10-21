<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TwigCs;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class TwigCsTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new TwigCs(
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
                'severity' => 'warning',
                'display' => 'all',
                'ruleset' => 'FriendsOfTwig\Twigcs\Ruleset\Official',
                'triggered_by' => ['twig'],
                'exclude' => [],
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
                $this->mockProcessBuilder('twigcs', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['hello.twig']),
            function () {
                $this->mockProcessBuilder('twigcs', self::mockProcess(0));
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
            self::mockContext(RunContext::class, ['notatwigfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'path' => [
            [
                'path' => 'src',
            ],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                'src',
            ]
        ];

        yield 'severity' => [
            [
                'severity' => 'error',
            ],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=error',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'display' => [
            [
                'display' => 'blocking',
            ],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=blocking',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '.',
            ]
        ];

        yield 'exclude' => [
            [
                'exclude' => ['src/', 'test/', null, '', false],
            ],
            self::mockContext(RunContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                '--exclude=src/',
                '--exclude=test/',
                '.',
            ]
        ];

        yield 'precommi' => [
            [
                'path' => 'src',
            ],
            self::mockContext(GitPreCommitContext::class, ['hello.twig', 'hello2.twig']),
            'twigcs',
            [
                '--severity=warning',
                '--display=all',
                '--ruleset=FriendsOfTwig\Twigcs\Ruleset\Official',
                '--ansi',
                'hello.twig',
                'hello2.twig',
            ]
        ];
    }
}
