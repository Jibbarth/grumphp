<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\SecurityCheckerRoave;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use GrumPHP\Util\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Symfony\Component\Process\Process;

class SecurityCheckerRoaveTest extends AbstractExternalTaskTestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy */
    private $filesystem;

    protected function provideTask(): TaskInterface
    {
        $this->filesystem = $this->prophesize(Filesystem::class);

        return new SecurityCheckerRoave(
            $this->processBuilder->reveal(),
            $this->formatter->reveal(),
            $this->filesystem->reveal()
        );
    }

    private function mockComposerJsonWithRoaveSecurityAdvisories(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(true);
        $this->filesystem->readPath(Argument::exact('./composer.json'))->willReturn(
            json_encode([
                'require' => ['roave/security-advisories'=>'dev-latest'],
            ])
        );
    }

    private function mockComposerJsonWithoutRoaveSecurityAdvisories(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(true);
        $this->filesystem->readPath(Argument::exact('./composer.json'))->willReturn(
            json_encode([
                'require' => [],
            ])
        );
    }

    private function mockMissingComposerJson(): void
    {
        $this->filesystem->isFile(Argument::exact('./composer.json'))->willReturn(false);
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'jsonfile' => './composer.json',
                'lockfile' => './composer.lock',
                'run_always' => false,
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
                $this->mockProcessBuilder('composer', $process = self::mockProcess(1));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
                $this->formatter->format($process)->willReturn('nope');
            },
            'nope'
        ];
        yield 'no-roave-security-advisories' =>
        [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->mockComposerJsonWithoutRoaveSecurityAdvisories();
            },
            'This task is only available when roave/security-advisories is installed as a library.'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            },
        ];
        yield 'exitCode0WhenRunAlways' => [
            [
                'run_always' => true
            ],
            self::mockContext(RunContext::class, ['notrelated.php']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {
                $this->mockComposerJsonWithRoaveSecurityAdvisories();
            }
        ];
        yield 'no-composer.json-file' => [
            [
                'run_always' => true
            ],
            self::mockContext(RunContext::class, []),
            function () {
                $this->mockMissingComposerJson();
            }
        ];
    }

    #[DataProvider('provideExternalTaskRuns')]
    #[Test]
    public function it_runs_external_task(
        array $config,
        ContextInterface $context,
        string $taskName,
        array $cliArguments,
        ?Process $process = null
    ): void
    {
        $configurator = function () {
            $this->mockComposerJsonWithRoaveSecurityAdvisories();
        };
        \Closure::bind($configurator, $this)();

        parent::it_runs_external_task($config,$context,$taskName,$cliArguments,$process);
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.lock']),
            'composer',
            [
                'update',
                '--dry-run',
                'roave/security-advisories',
            ]
        ];
    }
}
