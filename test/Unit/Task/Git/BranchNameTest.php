<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task\Git;

use Gitonomy\Git\Exception\ProcessException;
use GrumPHP\Git\GitRepository;
use GrumPHP\Task\Git\BranchName;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class BranchNameTest extends AbstractTaskTestCase
{
    /**
     * @var GitRepository|ObjectProphecy
     */
    private $repository;

    protected function provideTask(): TaskInterface
    {
        $this->repository = $this->prophesize(GitRepository::class);

        return new BranchName(
            $this->repository->reveal()
        );
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'blacklist' => [],
                'whitelist' => [],
                'additional_modifiers' => '',
                'allow_detached_head' => true,
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
        yield 'no-detachedHead' => [
            [
                'allow_detached_head' => false,
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willThrow(ProcessException::class);
            },
            'Branch naming convention task is not allowed on a detached HEAD.'
        ];
        yield 'blacklist' => [
            [
                'blacklist' => ['master'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('master');
            },
            'Matched blacklist rule: master'
        ];
        yield 'whitelist' => [
            [
                'whitelist' => ['develop'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('master');
            },
            'Whitelist rule not matched: develop'
        ];
        yield 'multi-whitelist' => [	
            [	
                'whitelist' => ['master', 'develop'],	
            ],	
            self::mockContext(RunContext::class, ['hello.php']),	
            function () {	
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('feature/other');	
            },	
            'Whitelist rule not matched: master'.PHP_EOL.'Whitelist rule not matched: develop'	
        ];
        yield 'blacklist-and-whitelist' => [	
            [	
                'blacklist' => ['feature/other'],
                'whitelist' => ['master', 'feature/*'],	
            ],	
            self::mockContext(RunContext::class, ['hello.php']),	
            function () {	
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('feature/other');	
            },	
            'Matched blacklist rule: feature/other'.PHP_EOL.'Whitelist rule not matched: master'.PHP_EOL.'Matched whitelist rule: feature/* (IGNORED due to presence in blacklist)'	
        ];
        yield 'mixed' => [
            [
                'whitelist' => ['JIRA-2'],
                'blacklist' => ['JIRA-1'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('JIRA-1');
            },
            'Matched blacklist rule: JIRA-1'.PHP_EOL.'Whitelist rule not matched: JIRA-2'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'detachedHead' => [
            [
                'allow_detached_head' => true,
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willThrow(ProcessException::class);
            }
        ];
        yield 'blacklist' => [
            [
                'blacklist' => ['master'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('develop');
            },
        ];
        yield 'whitelist' => [
            [
                'whitelist' => ['develop'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('develop');
            }
        ];
        yield 'multi-whitelist' => [
            [
                'whitelist' => ['feature/*', 'JIRA-1', '/JIRA-\d+/'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('JIRA-1');
            },
        ];
        yield 'mixed' => [
            [
                'whitelist' => ['/JIRA-\d+/'],
                'blacklist' => ['JIRA-1'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('JIRA-2');
            },
        ];
        yield 'additional_modifiers' => [
            [
                'whitelist' => ['/JIRÄ-\d+/u'],
                'blacklist' => ['/JIRÄ-1/u'],
            ],
            self::mockContext(RunContext::class, ['hello.php']),
            function () {
                $this->repository->run('symbolic-ref', ['HEAD', '--short'])->willReturn('JIRÄ-2');
            },
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        return [];
    }
}
