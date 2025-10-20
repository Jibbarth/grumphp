<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\FileSize;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Prophet;
use Symfony\Component\Finder\SplFileInfo;

class FileSizeTest extends AbstractTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new FileSize();
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'max_size' => '10M',
                'ignore_patterns' => [],
            ]
        ];

        yield 'invalidcase' => [
            [
                'ignore_patterns' => 'thisisnotanarray'
            ],
            null
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
        yield 'single-invalid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [
                self::mockFile('file1.php', 6),
                self::mockFile('file2.php', 12),
            ]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- file2.php exceeded the maximum size of 10M.'.PHP_EOL,
        ];
        yield 'invalid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [
                self::mockFile('file1.php', 12),
                self::mockFile('file2.php', 12),
            ]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- file1.php exceeded the maximum size of 10M.'.PHP_EOL.
            '- file2.php exceeded the maximum size of 10M.'.PHP_EOL,
        ];
        yield 'invalid-filesizes-custom-size' => [
            [
                'max_size' => '5M'
            ],
            self::mockContext(RunContext::class, [
                self::mockFile('file1.php', 12),
                self::mockFile('file2.php', 12),
            ]),
            function (array $options, ContextInterface $context) {
            },
            'Large files detected:'.PHP_EOL.
            '- file1.php exceeded the maximum size of 5M.'.PHP_EOL.
            '- file2.php exceeded the maximum size of 5M.'.PHP_EOL,
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'valid-filesizes' => [
            [],
            self::mockContext(RunContext::class, [
                self::mockFile('file1.php', 6),
                self::mockFile('file2.php', 6),
            ]),
            function () {
            }
        ];
        yield 'dont-validate-ignored-files' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, [
                self::mockFile('test/file.php', 2323, true),
            ]),
            function () {}
        ];
        yield 'dont-validate-symlinks' => [
            [],
            self::mockContext(RunContext::class, [
                self::mockFile('file.php', 2323, true),
            ]),
            function () {}
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            self::mockContext(RunContext::class),
            function () {
            },
        ];
    }

    private static function mockFile(string $file, int $megaBytes, $isSymlink = false): SplFileInfo
    {
        /** @var SplFileInfo $mock */
        $mock = (new Prophet())->prophesize(SplFileInfo::class);
        $mock->getFilename()->willReturn($file);
        $mock->getRelativePathname()->willReturn($file);
        $mock->isLink()->willReturn($isSymlink);
        $mock->isFile()->willReturn(true);
        $mock->getSize()->willReturn($megaBytes * 1024 * 1024);

        return $mock->reveal();
    }
}
