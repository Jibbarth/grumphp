<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Task\CloverCoverage;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use GrumPHP\Util\Filesystem;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CloverCoverageTest extends AbstractTaskTestCase
{
    /**
     * @var Filesystem|ObjectProphecy
     */
    private $filesystem;

    protected function provideTask(): TaskInterface
    {
        $this->filesystem = $this->prophesize(Filesystem::class);

        return new CloverCoverage(
            $this->filesystem->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [
                'clover_file' => 'coverage.xml',
            ],
            [
                'minimum_level' => 100.0,
                'target_level' => null,
                'clover_file' => 'coverage.xml',
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
        yield 'fileDoesntExist' => [
            [
                'clover_file' => 'coverage.xml',
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(false);
            },
            'File "coverage.xml" doesn\'t exists.'
        ];
        yield 'level0' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 0,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
            },
            'You must provide a positive minimum level between 1-100 for code coverage.'
        ];
        yield 'levelNotReached' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 100,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
            'Code coverage is 60%, which is below the accepted 100%'
        ];
        yield 'targetLevelNotReached' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 50,
                'target_level' => 70,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
            'Code coverage is 60%, which is below the target 70%',
            TaskResult::class,
            TaskResult::NONBLOCKING_FAILED,
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'levelReached' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 50,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
        ];
        yield 'allLevelsReached' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 50,
                'target_level' => 55,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/60-percent-coverage.xml')
                );
            },
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'noMetricElements' => [
            [
                'clover_file' => 'coverage.xml',
                'minimum_level' => 50,
            ],
            $this->mockContext(RunContext::class, ['coverage.xml']),
            function () {
                $this->filesystem->exists('coverage.xml')->willReturn(true);
                $this->filesystem->readFromFileInfo(Argument::which('getBasename', 'coverage.xml'))->willReturn(
                    file_get_contents(TEST_BASE_PATH.'/fixtures/clover_coverage/0-elements.xml')
                );
            }
        ];
    }
}
