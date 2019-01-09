<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeletonWeb;

use DrdPlus\RulesSkeletonWeb\RulesWebContent;
use DrdPlus\RulesSkeletonWeb\RulesHtmlHelper;
use Granam\Tests\Tools\TestWithMockery;
use Granam\WebContentBuilder\Dirs;
use Granam\WebContentBuilder\HtmlDocument;

abstract class AbstractContentTest extends TestWithMockery
{
    /** @var bool */
    private static $skeletonChecked;
    /** @var WebTestsConfiguration */
    private $testsConfiguration;
    /** @var Dirs */
    private $dirs;
    /** @var RulesWebContent */
    private $rulesContent;

    protected function getHtmlDocument(): HtmlDocument
    {
        if ($this->rulesContent === null) {
            $this->rulesContent = new RulesWebContent($this->getDirs(), new RulesHtmlHelper($this->getDirs()));
        }

        return $this->rulesContent->getHtmlDocument();
    }

    protected function isSkeletonChecked(string $skeletonProjectRoot = null): bool
    {
        if (self::$skeletonChecked === null) {
            $projectRootRealPath = \realpath($this->getProjectRoot());
            self::assertNotEmpty($projectRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getProjectRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonProjectRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));

            self::$skeletonChecked = $projectRootRealPath === $skeletonRootRealPath;
        }

        return self::$skeletonChecked;
    }

    protected function getProjectRoot(): string
    {
        self::assertDirectoryExists(\DRD_PLUS_PROJECT_ROOT, 'Project root has not been found');

        return \DRD_PLUS_PROJECT_ROOT;
    }

    protected function getDirs(): Dirs
    {
        if ($this->dirs === null) {
            $this->dirs = new Dirs($this->getProjectRoot());
        }

        return $this->dirs;
    }

    protected function getTestsConfiguration(): WebTestsConfiguration
    {
        if ($this->testsConfiguration === null) {
            $this->testsConfiguration = WebTestsConfiguration::createFromYaml(\DRD_PLUS_TESTS_ROOT . '/tests_configuration.yml');
        }

        return $this->testsConfiguration;
    }
}