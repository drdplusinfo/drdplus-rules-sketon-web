<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeleton\Web;

use DrdPlus\RulesSkeleton\Web\RulesContent;
use DrdPlus\RulesSkeleton\Web\RulesHtmlHelper;

class RulesContentTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_get_content(): void
    {
        self::assertSame($this->getHtmlDocument()->saveHTML(), $this->getRulesContent()->getValue());
    }

    private function getRulesContent(): RulesContent
    {
        return new RulesContent($this->getDirs(), new RulesHtmlHelper($this->getDirs()));
    }

    /**
     * @test
     */
    public function I_can_get_head(): void
    {
        $percents = $this->getTextsSimilarityPercents(
            $this->getHtmlDocument()->head->innerHTML,
            $this->getRulesContent()->getHead()->getHeadString()
        );
        self::assertGreaterThan(75, $percents);
    }

    private function getTextsSimilarityPercents(string $someText, string $anotherText): float
    {
        \similar_text(\str_replace("\n", '', \trim($someText)), \str_replace("\n", '', \trim($anotherText)), $percents);

        return $percents;
    }

    /**
     * @test
     */
    public function I_can_get_body(): void
    {
        $percents = $this->getTextsSimilarityPercents(
            $this->getHtmlDocument()->body->innerHTML,
            $this->getRulesContent()->getBody()->getBodyString()
        );
        self::assertGreaterThan(75, $percents);
    }
}