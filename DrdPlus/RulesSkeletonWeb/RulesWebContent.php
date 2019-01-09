<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeletonWeb;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringInterface;
use Granam\WebContentBuilder\Dirs;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\HtmlHelper;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\Content;
use Granam\WebContentBuilder\Web\CssFiles;
use Granam\WebContentBuilder\Web\Head;
use Granam\WebContentBuilder\Web\JsFiles;
use Granam\WebContentBuilder\Web\WebFiles;

class RulesWebContent extends StrictObject implements StringInterface
{
    /** @var Content */
    private $content;
    /** @var Dirs */
    private $dirs;
    /** @var HtmlHelper */
    private $htmlHelper;
    /** @var Body */
    private $body;
    /** @var Head */
    private $head;

    public function __construct(Dirs $dirs, HtmlHelper $htmlHelper)
    {
        $this->dirs = $dirs;
        $this->htmlHelper = $htmlHelper;
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

    public function getBody(): Body
    {
        if ($this->body === null) {
            $this->body = new Body(new WebFiles($this->dirs->getWebRoot()));
        }

        return $this->body;
    }

    public function getHead(): Head
    {
        if ($this->head === null) {
            $this->head = new Head($this->htmlHelper, new CssFiles($this->dirs, true), new JsFiles($this->dirs, true));
        }

        return $this->head;
    }

    protected function getContent(): Content
    {
        if (!$this->content) {
            $this->content = $this->buildContent($this->htmlHelper, $this->getHead(), $this->getBody());
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