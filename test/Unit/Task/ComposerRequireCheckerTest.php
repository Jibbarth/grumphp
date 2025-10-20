<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\ComposerRequireChecker;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ComposerRequireCheckerTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new ComposerRequireChecker(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'composer_file' => 'composer.json',
                'config_file' => null,
                'ignore_parse_errors' => false,
                'triggered_by' => ['composer.json', 'composer.lock', '*.php'],
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
                $this->mockProcessBuilder('composer-require-checker', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('composer-require-checker', self::mockProcess(0));
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
            'composer-require-checker',
            [
                'check',
                '--no-interaction',
                'composer.json',
            ]
        ];
        yield 'composer-file' => [
            [
                'composer_file' => 'src/composer.json',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--no-interaction',
                'src/composer.json',
            ]
        ];
        yield 'config-file' => [
            [
                'config_file' => 'configfile',
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--config-file=configfile',
                '--no-interaction',
                'composer.json',
            ]
        ];
        yield 'ignore-parse-errors' => [
            [
                'ignore_parse_errors' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'composer-require-checker',
            [
                'check',
                '--ignore-parse-errors',
                '--no-interaction',
                'composer.json',
            ]
        ];
    }
}
