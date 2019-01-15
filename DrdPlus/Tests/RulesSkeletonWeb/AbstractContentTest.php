<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeletonWeb;

use DrdPlus\RulesSkeletonWeb\RulesWebContent;
use Granam\Tests\Tools\TestWithMockery;
use Granam\WebContentBuilder\Dirs;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\HtmlHelper;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\CssFiles;
use Granam\WebContentBuilder\Web\Head;
use Granam\WebContentBuilder\Web\JsFiles;
use Granam\WebContentBuilder\Web\WebFiles;

abstract class AbstractContentTest extends TestWithMockery
{
    protected function getHtmlDocument(): HtmlDocument
    {
        return $this->getRulesWebContent()->getHtmlDocument();
    }

    protected function getRulesWebContent(): RulesWebContent
    {
        static $rulesWebContent;
        if ($rulesWebContent === null) {
            $htmlHelper = new HtmlHelper($this->getDirs());
            $head = new Head($htmlHelper, new CssFiles($this->getDirs(), true), new JsFiles($this->getDirs(), true));
            $body = new Body(new WebFiles($this->getDirs()->getWebRoot()));

            $rulesWebContent = new RulesWebContent($htmlHelper, $head, $body);
        }

        return $rulesWebContent;
    }

    protected function getContent(): string
    {
        static $content;
        if ($content === null) {
            $content = $this->getRulesWebContent()->getValue();
        }

        return $content;
    }

    protected function isSkeletonChecked(string $skeletonProjectRoot = null): bool
    {
        static $skeletonChecked;
        if ($skeletonChecked === null) {
            $projectRootRealPath = \realpath($this->getProjectRoot());
            self::assertNotEmpty($projectRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getProjectRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonProjectRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));

            $skeletonChecked = $projectRootRealPath === $skeletonRootRealPath;
        }

        return $skeletonChecked;
    }

    protected function getProjectRoot(): string
    {
        static $projectRoot;
        if ($projectRoot === null) {
            self::assertDirectoryExists(\DRD_PLUS_PROJECT_ROOT, 'Project root has not been found');
            $projectRoot = \DRD_PLUS_PROJECT_ROOT;
        }

        return $projectRoot;
    }

    protected function getDirs(): Dirs
    {
        static $dirs;
        if ($dirs === null) {
            $dirs = new Dirs($this->getProjectRoot());
        }

        return $dirs;
    }

    protected function getTestsConfiguration(): WebTestsConfiguration
    {
        static $testsConfiguration;
        if ($testsConfiguration === null) {
            $testsConfiguration = WebTestsConfiguration::createFromYaml(\DRD_PLUS_TESTS_ROOT . '/tests_configuration.yml');
        }

        return $testsConfiguration;
    }
}