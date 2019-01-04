<?php
declare(strict_types=1);

namespace DrdPlus\Web\RulesSkeleton;

use Granam\Strict\Object\StrictObject;
use Granam\String\StringInterface;
use Granam\WebContentBuilder\Dirs;
use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Body;
use Granam\WebContentBuilder\Web\Content;
use Granam\WebContentBuilder\Web\CssFiles;
use Granam\WebContentBuilder\Web\Head;
use Granam\WebContentBuilder\Web\JsFiles;
use Granam\WebContentBuilder\Web\WebFiles;

class RulesContent extends StrictObject implements StringInterface
{
    /**
     * @var Content
     */
    private $content;
    /**
     * @var Dirs
     */
    private $dirs;
    /**
     * @var HtmlHelper
     */
    private $htmlHelper;

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
        return $this->content->getValue();
    }

    protected function getContent(): Content
    {
        if (!$this->content) {
            $this->content = $this->buildContent(
                $this->htmlHelper,
                new Head($this->htmlHelper, new CssFiles($this->dirs, true), new JsFiles($this->dirs, true)),
                new Body(new WebFiles($this->dirs->getWebRoot()))
            );
        }

        return $this->content;
    }

    protected function buildContent(HtmlHelper $htmlHelper, Head $head, Body $body): Content
    {
        return new class($htmlHelper, $head, $body) extends Content
        {
            public function __construct(HtmlHelper $htmlHelper, Head $head, Body $body)
            {
                parent::__construct($htmlHelper, $head, $body);
            }

            protected function buildHtmlDocument(string $content): HtmlDocument
            {
                $htmlDocument = parent::buildHtmlDocument($content);
                /** @var HtmlHelper $htmlHelper */
                $htmlHelper = $this->htmlHelper;
                $htmlHelper->addIdsToTablesAndHeadings($htmlDocument);
                $htmlHelper->markExternalLinksByClass($htmlDocument);
                $htmlHelper->injectIframesWithRemoteTables($htmlDocument);

                return $htmlDocument;
            }
        };
    }

}