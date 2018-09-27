<?php
declare(strict_types=1);

namespace DrdPlus\Web\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringTools;
use Gt\Dom\Element;
use Gt\Dom\HTMLCollection;

class HtmlHelper extends StrictObject
{
    public const INVISIBLE_ID_CLASS = 'invisible-id';
    public const EXTERNAL_URL_CLASS = 'external-url';
    public const INTERNAL_URL_CLASS = 'internal-url';
    public const HIDDEN_CLASS = 'hidden';
    public const DATA_ORIGINAL_ID = 'data-original-id';
    public const DATA_HAS_MARKED_EXTERNAL_URLS = 'data-has-marked-external-urls';

    /**
     * Turn link into local version
     * @param string $link
     * @return string
     */
    public static function turnToLocalLink(string $link): string
    {
        return \preg_replace('~https?://((?:[^.]+[.])*)drdplus\.info~', 'http://$1drdplus.loc:88', $link);
    }

    /**
     * Turn link into local version
     * @param string $name
     * @return string
     * @throws \DrdPlus\Web\RulesSkeleton\Exceptions\NameToCreateHtmlIdFromIsEmpty
     */
    public static function toId(string $name): string
    {
        if ($name === '') {
            throw new Exceptions\NameToCreateHtmlIdFromIsEmpty('Expected some name to create HTML ID from');
        }

        return StringTools::toSnakeCaseId($name);
    }

    /**
     * @param HtmlDocument $html
     */
    public function addIdsToTablesAndHeadings(HtmlDocument $html): void
    {
        $elementNames = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'th'];
        foreach ($elementNames as $elementName) {
            /** @var Element $headerCell */
            foreach ($html->getElementsByTagName($elementName) as $headerCell) {

                if ($headerCell->getAttribute('id')) {
                    continue;
                }
                if ($elementName === 'th' && \strpos(\trim($headerCell->textContent), 'Tabulka') === false) {
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
        }
    }

    public function replaceDiacriticsFromIds(HtmlDocument $html): void
    {
        $this->replaceDiacriticsFromChildrenIds($html->body->children);
    }

    private function replaceDiacriticsFromChildrenIds(HTMLCollection $children): void
    {
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenIds($child->children);
            $id = $child->getAttribute('id');
            if (!$id) {
                continue;
            }
            $idWithoutDiacritics = static::toId($id);
            if ($idWithoutDiacritics === $id) {
                continue;
            }
            $child->setAttribute(self::DATA_ORIGINAL_ID, $id);
            $child->setAttribute('id', $this->sanitizeId($idWithoutDiacritics));
            $child->appendChild($invisibleId = new Element('span'));
            $invisibleId->setAttribute('id', $this->sanitizeId($id));
            $invisibleId->className = self::INVISIBLE_ID_CLASS;
        }
    }

    private function sanitizeId(string $id): string
    {
        return \str_replace('#', '_', $id);
    }

    public function replaceDiacriticsFromAnchorHashes(HtmlDocument $html): void
    {
        $this->replaceDiacriticsFromChildrenAnchorHashes($html->getElementsByTagName('a'));
    }

    private function replaceDiacriticsFromChildrenAnchorHashes(\Traversable $children): void
    {
        /** @var Element $child */
        foreach ($children as $child) {
            // recursion
            $this->replaceDiacriticsFromChildrenAnchorHashes($child->children);
            $href = $child->getAttribute('href');
            if (!$href) {
                continue;
            }
            $hashPosition = \strpos($href, '#');
            if ($hashPosition === false) {
                continue;
            }
            $hash = substr($href, $hashPosition + 1);
            if ($hash === '') {
                continue;
            }
            $hashWithoutDiacritics = static::toId($hash);
            if ($hashWithoutDiacritics === $hash) {
                continue;
            }
            $hrefWithoutDiacritics = substr($href, 0, $hashPosition) . '#' . $hashWithoutDiacritics;
            $child->setAttribute('href', $hrefWithoutDiacritics);
        }
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     */
    public function addAnchorsToIds(HtmlDocument $htmlDocument): HtmlDocument
    {
        $this->addAnchorsToChildrenWithIds($htmlDocument->body->children);

        return $htmlDocument;
    }

    private function addAnchorsToChildrenWithIds(HTMLCollection $children): void
    {
        /** @var Element $child */
        foreach ($children as $child) {
            if (!\in_array($child->nodeName, ['a', 'button'], true)
                && $child->getAttribute('id')
                && $child->getElementsByTagName('a')->length === 0 // already have some anchors, skipp it to avoid wrapping them by another one
                && !$child->prop_get_classList()->contains(self::INVISIBLE_ID_CLASS)
            ) {
                $toMove = [];
                /** @var \DOMElement $grandChildNode */
                foreach ($child->childNodes as $grandChildNode) {
                    if (!\in_array($grandChildNode->nodeName, ['span', 'strong', 'b', 'i', '#text'], true)) {
                        break;
                    }
                    $toMove[] = $grandChildNode;
                }
                if (\count($toMove) > 0) {
                    $anchorToSelf = new Element('a');
                    $child->replaceChild($anchorToSelf, $toMove[0]); // pairs anchor with parent element
                    $anchorToSelf->setAttribute('href', '#' . $child->getAttribute('id'));
                    foreach ($toMove as $index => $item) {
                        $anchorToSelf->appendChild($item);
                    }
                }
            }
            // recursion
            $this->addAnchorsToChildrenWithIds($child->children);
        }
    }

    private function containsOnlyTextAndSpans(\DOMNode $element): bool
    {
        if (!$element->hasChildNodes()) {
            return true;
        }
        /** @var \DOMNode $childNode */
        foreach ($element->childNodes as $childNode) {
            if ($childNode->nodeName !== 'span' && $childNode->nodeType !== XML_TEXT_NODE) {
                return false;
            }
            if (!$this->containsOnlyTextAndSpans($childNode)) {
                return false;
            }
        }

        return true;
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

    /**
     * @param HTMLCollection $children
     * @return string|bool
     */
    private function getChildId(HTMLCollection $children)
    {
        foreach ($children as $child) {
            if ($child->getAttribute('id')) {
                return $child->getAttribute('id');
            }
            $grandChildId = $this->getChildId($child->children);
            if ($grandChildId !== false) {
                return $grandChildId;
            }
        }

        return false;
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
     * @throws \DrdPlus\Web\RulesSkeleton\Exceptions\ExternalUrlsHaveToBeMarkedFirst
     */
    public function externalLinksTargetToBlank(HtmlDocument $htmlDocument): void
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
            if (!$anchor->getAttribute('target')) {
                $anchor->setAttribute('target', '_blank');
            }
        }
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     * @throws \LogicException
     */
    public function injectIframesWithRemoteTables(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        $remoteDrdPlusLinks = [];
        /** @var Element $anchor */
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
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

    private function hasMarkedExternalUrls(HtmlDocument $htmlDocument): bool
    {
        return (bool)$htmlDocument->body->getAttribute(self::DATA_HAS_MARKED_EXTERNAL_URLS);
    }

    /**
     * @param HtmlDocument $htmlDocument
     * @return HtmlDocument
     */
    public function makeExternalDrdPlusLinksLocal(HtmlDocument $htmlDocument): HtmlDocument
    {
        if (!$this->hasMarkedExternalUrls($htmlDocument)) {
            throw new Exceptions\ExternalUrlsHaveToBeMarkedFirst(
                'External links have to be marked first, use markExternalLinksByClass method for that'
            );
        }
        foreach ($htmlDocument->getElementsByClassName(self::EXTERNAL_URL_CLASS) as $anchor) {
            $anchor->setAttribute('href', static::turnToLocalLink($anchor->getAttribute('href')));
        }
        foreach ($htmlDocument->getElementsByClassName(self::INTERNAL_URL_CLASS) as $anchor) {
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