<?php
declare(strict_types=1);

namespace DrdPlus\Tests\RulesSkeletonWeb;

class RulesWebContentTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_get_content(): void
    {
        self::assertSame($this->getHtmlDocument()->saveHTML(), $this->getRulesWebContent()->getValue());
    }

    /**
     * @test
     */
    public function I_can_get_head(): void
    {
        $percents = $this->getTextsSimilarityPercents(
            $this->getHtmlDocument()->head->innerHTML,
            $this->getRulesWebContent()->getHtmlDocument()->head->innerHTML
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
            $this->getRulesWebContent()->getHtmlDocument()->body->innerHTML
        );
        self::assertGreaterThan(75, $percents);
    }

    /**
     * @test
     */
    public function Body_has_container_bootstrap_class(): void
    {
        self::assertTrue(
            $this->getRulesWebContent()->getHtmlDocument()->body->classList->contains('container'),
            'Body should has "container" class to be usable for Bootstrap'
        );
    }
}