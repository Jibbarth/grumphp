<?php
namespace GrumPHPE2E;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Config\EmptyTaskConfig;
use GrumPHP\Task\Config\TaskConfigInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\TaskInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DummyTask implements TaskInterface
{
    /**
     * @var TaskConfigInterface
     */
    private $config;

    public function __construct()
    {
        $this->config = new EmptyTaskConfig();
    }

    public function getConfig(): TaskConfigInterface
    {
        return $this->config;
    }

    public function withConfig(TaskConfigInterface $config): TaskInterface
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired('should_succeed');
        $resolver->setAllowedTypes('should_succeed', 'bool');

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return true;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        $options = $this->config->getOptions();
        if (!$options['should_succeed']) {
            return TaskResult::createFailed($this, $context, 'Dummy task failed based on should_succeed configuration.');
        }

        return TaskResult::createPassed($this, $context);
    }
}
