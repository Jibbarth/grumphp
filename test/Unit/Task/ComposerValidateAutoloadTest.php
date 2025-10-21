<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\ComposerValidateAutoload;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ComposerValidateAutoloadTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new ComposerValidateAutoload(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'file' => './composer.json',
                'strict_ambiguous' => false,
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
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder(
                    'composer',
                    self::mockProcess(1, '', 'nope'),
                );
            },
            'nope',
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess());
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
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.json']),
            'composer',
            [
                'dump-autoload',
                '--optimize',
                '--dry-run',
                '--strict-psr',
            ]
        ];
        yield 'strict-ambiguous' => [
            ['strict_ambiguous' => true],
            self::mockContext(RunContext::class, ['composer.json']),
            'composer',
            [
                'dump-autoload',
                '--optimize',
                '--dry-run',
                '--strict-psr',
                '--strict-ambiguous',
            ]
        ];
    }
}
