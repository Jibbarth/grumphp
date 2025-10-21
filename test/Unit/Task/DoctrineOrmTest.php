<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\DoctrineOrm;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class DoctrineOrmTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new DoctrineOrm(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'skip_property_types' => false,
                'skip_mapping' => false,
                'skip_sync' => false,
                'triggered_by' => ['php', 'xml', 'yml'],
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
                $this->mockProcessBuilder('doctrine', $process = self::mockProcess(1));
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
                $this->mockProcessBuilder('doctrine', self::mockProcess(0));
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
            'doctrine',
            [
                'orm:validate-schema',
            ]
        ];
        yield 'skip-mapping' => [
            [
                'skip_mapping' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'doctrine',
            [
                'orm:validate-schema',
                '--skip-mapping',
            ]
        ];
        yield 'skip-sync' => [
            [
                'skip_sync' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'doctrine',
            [
                'orm:validate-schema',
                '--skip-sync',
            ]
        ];
        yield 'skip-property-types' => [
            [
                'skip_property_types' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php', 'hello2.php']),
            'doctrine',
            [
                'orm:validate-schema',
                '--skip-property-types',
            ]
        ];
    }
}
