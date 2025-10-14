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

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'file' => './composer.json',
                'strict_ambiguous' => false,
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
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder(
                    'composer',
                    $this->mockProcess(1, '', 'nope'),
                );
            },
            'nope',
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $this->mockProcess());
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
            $this->mockContext(RunContext::class, ['composer.json']),
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
            $this->mockContext(RunContext::class, ['composer.json']),
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
