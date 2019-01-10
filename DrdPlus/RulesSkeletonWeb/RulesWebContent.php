<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeletonWeb;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringInterface;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\HtmlHelper;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\Content;
use Granam\WebContentBuilder\Web\Head;

class RulesWebContent extends StrictObject implements StringInterface
{
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Body */
    private $body;
    /** @var Head */
    private $head;
    /** @var Content */
    private $content;

    public function __construct(HtmlHelper $htmlHelper, Head $head, Body $body)
    {
        $this->htmlHelper = $htmlHelper;
        $this->head = $head;
        $this->body = $body;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->getContent()->getValue();
    }

    public function getHtmlDocument(): HtmlDocument
    {
        return $this->getContent()->getHtmlDocument();
    }

    protected function getContent(): Content
    {
        if (!$this->content) {
            $this->content = $this->buildContent($this->htmlHelper, $this->head, $this->body);
        }

        return $this->content;
    }

    protected function buildContent(HtmlHelper $htmlHelper, Head $head, Body $body): Content
    {
        return new class($htmlHelper, $head, $body) extends Content
        {
            protected function buildHtmlDocument(string $content): HtmlDocument
            {
                $htmlDocument = parent::buildHtmlDocument($content);
                $htmlDocument->body->classList->add('container');

                return $htmlDocument;
            }
        };
    }

}