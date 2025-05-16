<?php

namespace Kirschbaum\Commentions\Actions;

class ParseComment
{
    public function __invoke(string $body)
    {
        $body = $this->parseMentions($body);

        return $body;
    }

    protected function parseMentions(string $body)
    {
        return preg_replace_callback(
            // 1) Match <span>
            // 2) Containing class="... mention ..." (any order of classes)
            // 3) Containing data-type="mention"
            '/<span[^>]*class="[^"]*\bmention\b[^"]*"[^>]*data-type="mention"[^>]*>/i',
            function ($match) {
                $originalTag = $match[0];

                // Inside that tag, find the class="..." portion and append " text-xs"
                // if "text-xs" is not already present.
                $updatedTag = preg_replace_callback(
                    '/class="([^"]*)"/i',
                    fn () => 'class="comm:p-1 comm:bg-blue-100 comm:text-gray-600 comm:rounded-lg comm:text-xs"',
                    $originalTag,
                    1,
                );

                return $updatedTag;
            },
            $body
        );
    }

    public static function run(...$args)
    {
        return (new static())(...$args);
    }
}
