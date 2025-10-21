<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task;

use GrumPHP\Collection\ParseErrorsCollection;
use GrumPHP\Exception\RuntimeException;
use GrumPHP\Parser\Php\PhpParser as Parser;
use GrumPHP\Parser\Php\PhpParserError;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Task\PhpParser;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class PhpParserTest extends AbstractTaskTestCase
{
    /**
     * @var Parser|ObjectProphecy
     */
    private $parser;

    protected function provideTask(): TaskInterface
    {
        $this->parser = $this->prophesize(Parser::class);
        $this->parser->isInstalled()->willReturn(true);

        return new PhpParser($this->parser->reveal());
    }

    public static function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'visitors' => [],
                'triggered_by' => ['php'],
                'ignore_patterns' => [],
                'php_version' => null,
                'kind' => null,
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
        $prefix = "Some errors occured while parsing your PHP files:\n";

        yield 'invalid-file' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()->first())->willReturn(new ParseErrorsCollection([
                    self::createParseError('hello.php'),
                    self::createParseError('hello.php'),
                ]));
            },
            $prefix.(new ParseErrorsCollection([
                self::createParseError('hello.php'),
                self::createParseError('hello.php'),
            ]))
        ];
        yield 'invalid-files' => [
            [],
            self::mockContext(RunContext::class, ['hello.php', 'world.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([
                    self::createParseError('hello.php'),
                    self::createParseError('hello.php'),
                ]));
                $this->parser->parse($context->getFiles()[1])->willReturn(new ParseErrorsCollection([
                    self::createParseError('world.php'),
                ]));
            },
            $prefix.(new ParseErrorsCollection([
                self::createParseError('hello.php'),
                self::createParseError('hello.php'),
                self::createParseError('world.php'),
            ]))
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'no-lint-errors' => [
            [],
            self::mockContext(RunContext::class, ['hello.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([]));
            }
        ];
        yield 'no-files-after-ignore-patterns' => [
            [
                'ignore_patterns' => ['test/'],
            ],
            self::mockContext(RunContext::class, ['test/file.php']),
            function (array $options, ContextInterface $context) {
                $this->assumeParserConfig($options);
                $this->parser->parse($context->getFiles()[0])->willReturn(new ParseErrorsCollection([]));
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
            self::mockContext(RunContext::class, ['notaymlfile.txt']),
            function () {}
        ];
    }

    private function assumeParserConfig(array $options)
    {
        $this->parser->setParserOptions($options)->shouldBeCalled();
    }

    private static function createParseError(string $fileName): PhpParserError
    {
        return new PhpParserError(PhpParserError::TYPE_ERROR, 'error', $fileName);
    }
}
