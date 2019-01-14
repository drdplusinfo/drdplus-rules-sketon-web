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
    public function I_can_get_body(): void
    {
        self::assertNotEmpty($this->getRulesWebContent()->getHtmlDocument()->body->innerHTML);
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