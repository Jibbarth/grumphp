<?php

declare(strict_types=1);

namespace GrumPHPTest\E2E;

use PHPUnit\Framework\Attributes\Test;

class ExtensionsTest extends AbstractE2ETestCase
{
    #[Test]
    function it_can_configure_an_extension()
    {
        $this->initializeGitInRootDir();
        $this->initializeComposer($this->rootDir);
        $grumphpFile = $this->initializeGrumphpConfig($this->rootDir);
        $this->installComposer($this->rootDir);
        $this->ensureHooksExist();

        $this->enableCustomExtension($grumphpFile, $this->rootDir);

        $this->commitAll();
        $this->runGrumphp($this->rootDir);
    }
}
