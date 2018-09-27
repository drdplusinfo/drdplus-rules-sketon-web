<?php
declare(strict_types=1);

namespace DrdPlus\Tests\Web\RulesSkeleton;

use DrdPlus\Web\RulesSkeleton\HtmlDocument;
use DrdPlus\Web\RulesSkeleton\YamlReader;
use Granam\Tests\Tools\TestWithMockery;

class AbstractContentTest extends TestWithMockery
{
    /** @var bool */
    private static $skeletonChecked;
    /** @var TestsConfiguration */
    private $testsConfiguration;

    protected function getHtmlDocument(): HtmlDocument
    {
        return new HtmlDocument();
    }

    protected function isSkeletonChecked(string $skeletonDocumentRoot = null): bool
    {
        if (self::$skeletonChecked === null) {
            $documentRootRealPath = \realpath($this->getDocumentRoot());
            self::assertNotEmpty($documentRootRealPath, 'Can not find out real path of document root ' . \var_export($this->getDocumentRoot(), true));
            $skeletonRootRealPath = \realpath($skeletonDocumentRoot ?? __DIR__ . '/../../../..');
            self::assertNotEmpty($skeletonRootRealPath, 'Can not find out real path of skeleton root ' . \var_export($skeletonRootRealPath, true));
            self::assertSame('rules-skeleton', \basename($skeletonRootRealPath), 'Expected different trailing dir of skeleton document root');

            self::$skeletonChecked = $documentRootRealPath === $skeletonRootRealPath;
        }

        return self::$skeletonChecked;
    }

    protected function getDocumentRoot(): string
    {
        return \DRD_PLUS_DOCUMENT_ROOT;
    }

    protected function getTestsConfiguration(): TestsConfiguration
    {
        if ($this->testsConfiguration === null) {
            $this->testsConfiguration = new TestsConfiguration(
                (new YamlReader(\DRD_PLUS_TESTS_ROOT . '/tests_configuration.yml'))->getValues()
            );
        }

        return $this->testsConfiguration;
    }
}