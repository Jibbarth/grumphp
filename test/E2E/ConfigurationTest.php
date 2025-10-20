<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

use PHPUnit\Framework\Attributes\Test;

class ConfigurationTest extends AbstractE2ETestCase
{
    #[Test]
    function it_should_be_able_to_resolve_env_variable_in_configuration()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'parameters' => [
                'env(GRUMPHP_PARAMETER_TEST)' => '~'
            ],
            'grumphp' => [
                'ascii' => [
                    'succeeded' => '%env(string:GRUMPHP_PARAMETER_TEST)%',
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $process = $this->runGrumphp(projectPath: $this->rootDir, environment: [
            'GRUMPHP_PARAMETER_TEST' => 'succeeded.txt',
        ]);

        $this->assertStringContainsString(
            file_get_contents(PROJECT_BASE_PATH . '/resources/ascii/succeeded.txt'),
            $process->getOutput(),
        );
    }

    #[Test]
    function it_should_be_able_to_resolve_internal_dotenv_environment_configuations()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'parameters' => [
                'env(GRUMPHP_PARAMETER_TEST)' => '~'
            ],
            'grumphp' => [
                'ascii' => [
                    'succeeded' => '%env(string:GRUMPHP_PARAMETER_TEST)%',
                ],
                'environment' => [
                    'variables' => [
                        'GRUMPHP_PARAMETER_TEST' => 'succeeded.txt',
                    ]
                ],
            ],
        ]);

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll();
        $process = $this->runGrumphp(projectPath: $this->rootDir);

        $this->assertStringContainsString(
            file_get_contents(PROJECT_BASE_PATH . '/resources/ascii/succeeded.txt'),
            $process->getOutput(),
        );
    }

    #[Test]
    function it_should_be_able_to_resolve_dist_file_imports()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);

        // grumphp.yml file.
        $grumphpFile = $this->initializeGrumphpConfig(path: $this->rootDir, customConfig: [
            'imports' => [
                ['resource' => 'grumphp.yml.dist']
            ],
        ]);
        // grumphp.yml.dist file which is imported, doesn't matter what's in this imported file.
        $grumphpDistFile = $this->initializeGrumphpConfig(path: $this->rootDir, fileName: 'grumphp.yml.dist');

        $this->installComposer($this->rootDir);
        $this->ensureHooksExist($this->rootDir);

        $this->enableValidatePathsTask($grumphpFile, $this->rootDir);

        $this->commitAll($this->rootDir);
        $this->runGrumphp($this->rootDir);
    }
}
