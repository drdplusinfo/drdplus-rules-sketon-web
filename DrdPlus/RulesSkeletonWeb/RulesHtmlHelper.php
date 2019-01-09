<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeletonWeb;

use Granam\WebContentBuilder\HtmlDocument;
use Gt\Dom\Element;

class RulesHtmlHelper extends \Granam\WebContentBuilder\HtmlHelper
{
    public const EXTERNAL_URL_CLASS = 'external-url';
    public const HIDDEN_CLASS = 'hidden';
    public const DATA_HAS_MARKED_EXTERNAL_URLS = 'data-has-marked-external-urls';

    /**
     * Turn link into local version
     * @param string $link
     * @return string
     */
    public static function turnToLocalLink(string $link): string
    {
        return \preg_replace('~https?://((?:[^.]+[.])*)drdplus\.info~', 'http://$1drdplus.loc', $link);
    }

    public function addIdsToTablesAndHeadings(HtmlDocument $htmlDocument): HtmlDocument
    {
        $this->addIdsToHeadings($htmlDocument);
        /** @var Element $headerCell */
        foreach ($htmlDocument->getElementsByTagName('th') as $headerCell) {
            if ($headerCell->getAttribute('id')) {
                continue;
            }
            if (\strpos(\trim($headerCell->textContent), 'Tabulka') === false) {
                continue;
            }
            $id = false;
            /** @var \DOMNode $childNode */
            foreach ($headerCell->childNodes as $childNode) {
                if ($childNode->nodeType === \XML_TEXT_NODE) {
                    $id = \trim($childNode->nodeValue);
                    break;
                }
            }
            if (!$id) {
                continue;
            }
            $headerCell->setAttribute('id', $id);
        }

        return $htmlDocument;
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @param array|string[] $requiredIds filter of required tables by their IDs
     * @return array|Element[]
     */
    public function findTablesWithIds(HtmlDocument $htmlDocument, array $requiredIds = []): array
    {
        $requiredIds = \array_filter($requiredIds, 'trim');
        $requiredIds = \array_unique($requiredIds);
        $unifiedRequiredIds = [];
        foreach ($requiredIds as $requiredId) {
            if ($requiredId === '') {
                continue;
            }
            $unifiedRequiredId = static::toId($requiredId);
            $unifiedRequiredIds[$unifiedRequiredId] = $unifiedRequiredId;
        }
        $tablesWithIds = [];
        /** @var Element $table */
        foreach ($htmlDocument->getElementsByTagName('table') as $table) {
            $tableId = $table->getAttribute('id');
            if (!$tableId) {
                foreach ($table->getElementsByTagName('th') as $th) {
                    $tableId = $th->getAttribute('id');
                    if ($tableId) {
                        break;
                    }
                }
                if (!$tableId) {
                    continue;
                }
            }
            $unifiedTableId = static::toId($tableId);
            $tablesWithIds[$unifiedTableId] = $table;
        }
        if (!$unifiedRequiredIds) {
            return $tablesWithIds; // all of them, no filter
        }

        return \array_intersect_key($tablesWithIds, $unifiedRequiredIds);
    }

    public function markExternalLinksByClass(HtmlDocument $htmlDocument): HtmlDocument
    {
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByTagName('a') as $anchor) {
            if (!$anchor->classList->contains(self::INTERNAL_URL_CLASS)
                && \preg_match('~^(https?:)?//[^#]~', $anchor->getAttribute('href') ?? '')
            ) {
                $anchor->classList->add(self::EXTERNAL_URL_CLASS);
            }
        }
        $htmlDocument->body->setAttribute(self::DATA_HAS_MARKED_EXTERNAL_URLS, '1');

        return $htmlDocument;
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return array|Element[]
     */
    protected function getExternalAnchors(HtmlDocument $htmlDocument): array
    {
        $externalAnchors = [];
        foreach ($htmlDocument->getElementsByTagName('a') as $anchor) {
            if ($this->isAnchorExternal($anchor)) {
                $externalAnchors[] = $anchor;
            }
        }

        return $externalAnchors;
    }

    protected function isAnchorExternal(Element $anchor): bool
    {
        if ($anchor->tagName !== 'a') {
            throw new Exceptions\ExpectedAnchorElement(
                sprintf('Expected anchor element, got %s (%s)', $anchor->tagName, $anchor->innerHTML)
            );
        }

        return !$anchor->classList->contains(self::INTERNAL_URL_CLASS)
            && ($anchor->classList->contains(self::EXTERNAL_URL_CLASS) || $this->isLinkExternal($anchor->getAttribute('href')));
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     * @throws \LogicException
     */
    public function injectIframesWithRemoteTables(HtmlDocument $htmlDocument): HtmlDocument
    {
        $remoteDrdPlusLinks = [];
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByTagName('a') as $anchor) {
            if ($anchor->classList->contains(self::INTERNAL_URL_CLASS)
                || !$anchor->classList->contains(self::EXTERNAL_URL_CLASS)
                || !$this->isLinkExternal($anchor->getAttribute('href'))
            ) {
                continue;
            }
            if (!\preg_match('~(?:https?:)?//(?<host>[[:alpha:]]+\.drdplus\.info)/[^#]*#(?<tableId>tabulka_\w+)~', $anchor->getAttribute('href'), $matches)) {
                continue;
            }
            $remoteDrdPlusLinks[$matches['host']][] = $matches['tableId'];
        }
        if (\count($remoteDrdPlusLinks) === 0) {
            return $htmlDocument;
        }
        $body = $htmlDocument->body;
        foreach ($remoteDrdPlusLinks as $remoteDrdPlusHost => $tableIds) {
            $iFrame = $htmlDocument->createElement('iframe');
            $body->appendChild($iFrame);
            $iFrame->setAttribute('id', $remoteDrdPlusHost); // we will target that iframe via JS by remote host name
            $iFrame->setAttribute(
                'src',
                "https://{$remoteDrdPlusHost}/?tables=" . \htmlspecialchars(\implode(',', \array_unique($tableIds)))
            );
            $iFrame->setAttribute('class', static::HIDDEN_CLASS);
        }

        return $htmlDocument;
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     */
    public function makeDrdPlusLinksLocal(HtmlDocument $htmlDocument): HtmlDocument
    {
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByTagName('a') as $anchor) {
            $anchor->setAttribute('href', static::turnToLocalLink($anchor->getAttribute('href')));
        }
        /** @var Element $iFrame */
        foreach ($htmlDocument->getElementsByTagName('iframe') as $iFrame) {
            $iFrame->setAttribute('src', static::turnToLocalLink($iFrame->getAttribute('src')));
            $iFrame->setAttribute('id', \str_replace('drdplus.info', 'drdplus.loc', $iFrame->getAttribute('id')));
        }

        return $htmlDocument;
    }
}