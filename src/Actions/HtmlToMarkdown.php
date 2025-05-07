<?php

namespace Kirschbaum\Commentions\Actions;

use Closure;
use League\HTMLToMarkdown\HtmlConverter;

class HtmlToMarkdown
{
    public function __invoke(string $html, ?Closure $mentionedCallback = null): string
    {
        $converter = new HtmlConverter();
        $markdown = $converter->convert($html);
        $markdown = $this->transformMentionsToMarkdown($markdown, $mentionedCallback);

        return $markdown;
    }

    protected function transformMentionsToMarkdown(string $markdown, ?Closure $mentionedCallback = null): string
    {
        if ($mentionedCallback) {
            return preg_replace_callback(
                '/<span class="mention" data-id="(.*?)" data-label="(.*?)" data-type="mention">@(.*?)<\/span>/',
                fn ($matches) => $mentionedCallback($matches[1], $matches[2]),
                $markdown
            );
        }

        return preg_replace(
            '/<span class="mention" data-id="(.*?)" data-label="(.*?)" data-type="mention">@(.*?)<\/span>/',
            '*@$2*',
            $markdown
        );
    }

    public static function run(...$args)
    {
        return (new static())(...$args);
    }
}
