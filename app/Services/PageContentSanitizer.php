<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class PageContentSanitizer
{
    public function sanitize(?string $content): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')->allowElement('br')->allowElement('hr')
            ->allowElement('h2')->allowElement('h3')->allowElement('h4')
            ->allowElement('strong')->allowElement('b')->allowElement('em')->allowElement('i')
            ->allowElement('u')->allowElement('s')->allowElement('blockquote')
            ->allowElement('ul')->allowElement('ol')->allowElement('li')
            ->allowElement('pre')->allowElement('code')
            ->allowElement('table')->allowElement('thead')->allowElement('tbody')->allowElement('tr')
            ->allowElement('th', ['colspan', 'rowspan'])->allowElement('td', ['colspan', 'rowspan'])
            ->allowElement('a', ['href', 'title', 'target'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(500000);

        return (new HtmlSanitizer($config))->sanitize($content ?? '');
    }
}
