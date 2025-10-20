<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityCheckerEnlightn;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class SecurityCheckerEnlightnTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new SecurityCheckerEnlightn(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'lockfile' => './composer.lock',
                'run_always' => false,
                'allow_list' => [],
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
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('security-checker', $process = self::mockProcess(1));
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('security-checker', self::mockProcess(0));
            }
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            self::mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('security-checker', self::mockProcess(0));
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
        yield 'no-composer-file' => [
            [],
            self::mockContext(RunContext::class, ['thisisnotacomposerfile.lock']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            'security-checker',
            [
                'security:check',
                './composer.lock',
            ]
        ];

        yield 'with_allow_list' => [
            ['allow_list' => ['allow_advisory_1', 'allow_advisory_2']],
            self::mockContext(RunContext::class, ['composer.lock']),
            'security-checker',
            [
                'security:check',
                './composer.lock',
                '--allow-list=allow_advisory_1',
                '--allow-list=allow_advisory_2',
            ]
        ];
    }
}
