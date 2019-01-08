<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Web\RulesContent;
use DrdPlus\RulesSkeleton\Web\RulesHtmlHelper;
use Granam\Tests\Tools\TestWithMockery;
use Granam\WebContentBuilder\Dirs;
use Granam\WebContentBuilder\HtmlDocument;

abstract class AbstractContentTest extends TestWithMockery
{
    /** @var bool */
    private static $skeletonChecked;
    /** @var TestsConfiguration */
    private $testsConfiguration;
    /** @var Dirs */
    private $dirs;
    /** @var RulesContent */
    private $rulesContent;

    protected function getHtmlDocument(): HtmlDocument
    {
        if ($this->rulesContent === null) {
            $this->rulesContent = new RulesContent($this->getDirs(), new RulesHtmlHelper($this->getDirs()));
        }
        return $this->rulesContent->getHtmlDocument();
    }

    protected function isSkeletonChecked(string $skeletonDocumentRoot = null): bool
    {
        if (self::$skeletonChecked === null) {
            $documentRootRealPath = \realpath($this->getDocumentRoot());
            self::assertNotEmpty($documentRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getDocumentRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonDocumentRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));

            self::$skeletonChecked = $documentRootRealPath === $skeletonRootRealPath;
        }

        return self::$skeletonChecked;
    }

    protected function getDocumentRoot(): string
    {
        return \DRD_PLUS_DOCUMENT_ROOT;
    }

    protected function getDirs(): Dirs
    {
        if ($this->dirs === null) {
            $this->dirs = new Dirs($this->getDocumentRoot());
        }

        return $this->dirs;
    }

    protected function getTestsConfiguration(): TestsConfiguration
    {
        if ($this->testsConfiguration === null) {
            $this->testsConfiguration = TestsConfiguration::createFromYaml(\DRD_PLUS_TESTS_ROOT . '/tests_configuration.yml');
        }

        return $this->testsConfiguration;
    }
}