<?php

namespace Kirschbaum\Commentions\Actions;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes the raw HTML stored for a comment body.
 *
 * Comment bodies are persisted as HTML and later rendered unescaped, so the
 * client-supplied markup must be reduced to a safe allowlist. The allowlist
 * matches exactly what the TipTap editor can produce (StarterKit + Underline +
 * Link + Mention) — anything else (scripts, event handlers, iframes, unsafe
 * link schemes) is stripped.
 */
class SanitizeCommentHtml
{
    public function __invoke(string $body): string
    {
        return $this->sanitizer()->sanitize($body);
    }

    protected function sanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('s')
            ->allowElement('u')
            ->allowElement('code')
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('hr')
            ->allowElement('pre')
            ->allowElement('blockquote')
            ->allowElement('h1')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a', ['href', 'target', 'rel', 'class'])
            ->allowElement('span', ['class', 'data-type', 'data-id', 'data-label'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowRelativeLinks()
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(500_000);

        return new HtmlSanitizer($config);
    }

    public static function run(...$args)
    {
        return (new self())(...$args);
    }
}
