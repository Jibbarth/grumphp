<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\PhpVersion;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use GrumPHP\Util\PhpVersion as PhpVersionUtility;
use Prophecy\Prophecy\ObjectProphecy;

class PhpVersionTest extends AbstractTaskTestCase
{
    /**
     * @var PhpVersionUtility|ObjectProphecy
     */
    private $versionUtility;

    protected function provideTask(): TaskInterface
    {
        $this->versionUtility = $this->prophesize(PhpVersionUtility::class);

        return new PhpVersion($this->versionUtility->reveal());
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'project' => null,
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
        yield 'current-version-not-supported' => [
            [
                'project' => '7.4'
            ],
            self::mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(false);
            },
            'PHP version '.PHP_VERSION.' is unsupported'
        ];
        yield 'project-version-bigger' => [
            [
                'project' => '99.99.1'
            ],
            self::mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(true);
                $this->versionUtility->isSupportedProjectVersion(PHP_VERSION, $options['project'])->willReturn(false);
            },
            'This project requires PHP version 99.99.1, you have '.PHP_VERSION
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'project-version-lower' => [
            [
                'project' => '4.0'
            ],
            self::mockContext(RunContext::class, ['hello.xml']),
            function (array $options, ContextInterface $context) {
                $this->versionUtility->isSupportedVersion(PHP_VERSION)->willReturn(true);
                $this->versionUtility->isSupportedProjectVersion(PHP_VERSION, $options['project'])->willReturn(true);
            }
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'no-project' => [
            [],
            self::mockContext(RunContext::class),
            function () {}
        ];
    }
}
