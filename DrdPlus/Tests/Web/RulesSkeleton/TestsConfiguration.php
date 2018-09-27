<?php
declare(strict_types=1);

namespace DrdPlus\Tests\Web\RulesSkeleton;

use DrdPlus\Web\RulesSkeleton\HtmlHelper;
use Granam\Strict\Object\StrictObject;

class TestsConfiguration extends StrictObject
{
    public const PUBLIC_URL = 'public_url';
    public const HAS_TABLES = 'has_tables';
    public const SOME_EXPECTED_TABLE_IDS = 'some_expected_table_ids';
    public const HAS_TABLE_OF_CONTENTS = 'has_table_of_contents';

    /** @var string */
    private $publicUrl;
    /** @var string */
    private $localUrl;
    /** @var bool */
    private $hasTables = true;
    /** @var array|string[] */
    private $someExpectedTableIds = [];
    /** @var bool */
    private $hasTableOfContents = true;

    /**
     * @param array $values
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\InvalidLocalUrl
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\InvalidPublicUrl
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\PublicUrlShouldUseHttps
     */
    public function __construct(array $values)
    {
        $this->setPublicUrl($values);
        $this->setLocalUrl($this->getPublicUrl());
        $this->setHasTables($values);
        $this->setSomeExpectedTableIds($values, $this->hasTables());
        $this->setHasTableOfContents($values);
    }

    private function setPublicUrl(array $values): void
    {
        $publicUrl = $values[self::PUBLIC_URL] ?? null;
        if ($publicUrl === null) {
            throw new Exceptions\InvalidPublicUrl('Expected valid public URL, got nothing');
        }
        try {
            $this->guardValidUrl($values[self::PUBLIC_URL]);
        } catch (Exceptions\InvalidUrl $invalidUrl) {
            throw new Exceptions\InvalidPublicUrl(
                "Given public URL is not valid: '$publicUrl'", $invalidUrl->getCode(),
                $invalidUrl
            );
        }
        if (\strpos($publicUrl, 'https://') !== 0) {
            throw new Exceptions\PublicUrlShouldUseHttps("Given public URL should use HTTPS: '$publicUrl'");
        }
        $this->publicUrl = $publicUrl;
    }

    /**
     * @param string $url
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\InvalidUrl
     */
    private function guardValidUrl(string $url): void
    {
        if (!\filter_var($url, \FILTER_VALIDATE_URL)) {
            throw new Exceptions\InvalidUrl("Given URL is not valid: '$url'");
        }
    }

    /**
     * @param string $publicUrl
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\InvalidLocalUrl
     */
    private function setLocalUrl(string $publicUrl): void
    {
        $localUrl = HtmlHelper::turnToLocalLink($publicUrl);
        if (!$this->isLocalLinkAccessible($localUrl)) {
            throw new Exceptions\InvalidLocalUrl("Given local URL can not be reached or is not local: '$localUrl'");
        }
        $localUrl = $this->extendPortToLocalUrl($localUrl);
        $this->guardValidUrl($localUrl);
        $this->localUrl = $localUrl;
    }

    private function isLocalLinkAccessible(string $localUrl): bool
    {
        $host = \parse_url($localUrl, \PHP_URL_HOST);

        return $host !== false
            && !\filter_var($host, \FILTER_VALIDATE_IP)
            && \gethostbyname($host) === '127.0.0.1';
    }

    private function extendPortToLocalUrl(string $localUrl): string
    {
        if (\preg_match('~:\d+$~', $localUrl)) {
            return $localUrl; // already with port
        }

        return $localUrl . ':88';
    }

    /**
     * @param array $values
     */
    private function setHasTables(array $values): void
    {
        $this->hasTables = (bool)($values[self::HAS_TABLES] ?? true);
    }

    /**
     * @param array $values
     * @param bool $hasTables
     * @throws \DrdPlus\Tests\Web\RulesSkeleton\Exceptions\InvalidSomeExpectedTableIdsTestsConfiguration
     */
    private function setSomeExpectedTableIds(array $values, bool $hasTables): void
    {
        if (!$hasTables) {
            $this->someExpectedTableIds = [];

            return;
        }
        $someExpectedTableIds = $values[self::SOME_EXPECTED_TABLE_IDS] ?? [];
        if (!\is_array($someExpectedTableIds)) {
            throw new Exceptions\InvalidSomeExpectedTableIdsTestsConfiguration(
                "Expected some '" . self::SOME_EXPECTED_TABLE_IDS . "', got "
                . ($someExpectedTableIds === null
                    ? 'nothing'
                    : \var_export($someExpectedTableIds, true)
                )
            );
        }
        $structureOk = true;
        foreach ($someExpectedTableIds as $someExpectedTableId) {
            if (!\is_string($someExpectedTableId)) {
                $structureOk = false;
                break;
            }
        }
        if (!$structureOk) {
            throw new Exceptions\InvalidSomeExpectedTableIdsTestsConfiguration(
                "Expected flat array of strings for '" . self::SOME_EXPECTED_TABLE_IDS . "', got "
                . \var_export($someExpectedTableIds, true)
            );
        }
        $this->someExpectedTableIds = $someExpectedTableIds;
    }

    /**
     * @param array $values
     */
    private function setHasTableOfContents(array $values): void
    {
        $this->hasTableOfContents = (bool)($values[self::HAS_TABLE_OF_CONTENTS] ?? true);
    }

    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    /**
     * @return string
     */
    public function getLocalUrl(): string
    {
        return $this->localUrl;
    }

    public function hasTables(): bool
    {
        return $this->hasTables;
    }

    public function getSomeExpectedTableIds(): array
    {
        return $this->someExpectedTableIds;
    }

    public function hasTableOfContents(): bool
    {
        return $this->hasTableOfContents;
    }

}