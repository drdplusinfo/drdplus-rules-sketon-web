<?php
declare(strict_types=1);

namespace DrdPlus\RulesSkeletonWeb;

use Granam\WebContentBuilder\HtmlDocument;
use Granam\WebContentBuilder\Web\Content;

class RulesWebContent extends Content
{
    protected function buildHtmlDocument(string $content): HtmlDocument
    {
        $htmlDocument = parent::buildHtmlDocument($content);
        $htmlDocument->body->classList->add('container');

        return $htmlDocument;
    }
}