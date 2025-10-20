<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\ComposerNormalize;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class ComposerNormalizeTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new ComposerNormalize(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'use_standalone' => false,
                'indent_size' => null,
                'indent_style' => null,
                'no_check_lock' => false,
                'no_update_lock' => true,
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
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', $process = self::mockProcess(1));
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
            self::mockContext(RunContext::class, ['composer.json']),
            function () {
                $this->mockProcessBuilder('composer', self::mockProcess(0));
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
        yield 'no-files-after-no-composer-json' => [
            [],
            self::mockContext(RunContext::class, ['notaphpfile.txt']),
            function () {}
        ];
    }

    public static function provideExternalTaskRuns(): iterable
    {
        yield 'defaults' => [
            [],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
                '--no-update-lock',
            ]
        ];
        yield 'no-indent-on-missing-size' => [
            [
                'indent_style' => 'space',
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
                '--no-update-lock',
            ]
        ];
        yield 'no-indent-on-missing-style' => [
            [
                'indent_size' => 2,
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
                '--no-update-lock',
            ]
        ];
        yield 'indent' => [
            [
                'indent_style' => 'space',
                'indent_size' => 2,
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
                '--indent-style=space',
                '--indent-size=2',
                '--no-update-lock',
            ]
        ];
        yield 'update-lock' => [
            [
                'no_update_lock' => false,
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
            ]
        ];
        yield 'verbose' => [
            [
                'verbose' => true,
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer',
            [
                'normalize',
                '--dry-run',
                '--no-update-lock',
                '-q'
            ]
        ];
        yield 'use_standalone' => [
            [
                'use_standalone' => true,
            ],
            self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
            'composer-normalize',
            [
                '--dry-run',
                '--no-update-lock',
            ]
        ];
        yield 'no-check-lock' => [
        [
          'no_check_lock' => true,
        ],
        self::mockContext(RunContext::class, ['composer.json', 'hello2.php']),
        'composer',
        [
          'normalize',
          '--dry-run',
          '--no-check-lock',
          '--no-update-lock',
        ]
      ];
    }
}
