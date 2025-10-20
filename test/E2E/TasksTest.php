<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

class TasksTest extends AbstractE2ETestCase
{
    #[Test]
    function it_can_configure_a_task_under_an_alias()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }

    #[Test]
    function it_can_resolve_task_config_With_env_vars()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'grumphp' => [
                'environment' => [
                    'variables' => [
                        'SHOULD_DUMMY_SUCCEED' => 1,
                    ]
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableDummyTask($grumphpFile, $this->rootDir, [
            'should_succeed' => '%env(bool:SHOULD_DUMMY_SUCCEED)%',
        ]);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }
}
