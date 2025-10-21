<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\PhpArkitect;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class PhpArkitectTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new PhpArkitect(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'config' => null,
                'target_php_version' => null,
                'stop_on_failure' => false,
            ],
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
                $this->mockProcessBuilder('phparkitect', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('phparkitect', self::mockProcess(0));
            }
        ];
    }

    #[Test]
    #[DataProvider('provideSkipsOnStuff')]
    public function it_skips_on_stuff(
        array            $config,
        ContextInterface $context,
        callable         $configurator
    ): void
    {
        self::markTestSkipped('No skip scenarios defined yet');
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-skip-scenarios' => [
            [],
            self::mockContext(RunContext::class),
            function () {
            }
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--no-interaction'
            ]
        ];
        yield 'config' => [
            [
                'config' => 'phparkitect.php'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--config=phparkitect.php',
                '--no-interaction'
            ]
        ];
        yield 'target_php_version' => [
            [
                'target_php_version' => '8.1'
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--target-php-version=8.1',
                '--no-interaction'
            ]
        ];
        yield 'stop_on_failure' => [
            [
                'stop_on_failure' => TRUE
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'phparkitect',
            [
                'check',
                '--stop-on-failure',
                '--no-interaction'
            ]
        ];
    }
}
