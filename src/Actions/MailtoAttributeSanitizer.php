<?php

namespace Kirschbaum\Commentions\Actions;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

class MailtoAttributeSanitizer implements AttributeSanitizerInterface
{
    public function getSupportedElements(): ?array
    {
        return ['a'];
    }

    public function getSupportedAttributes(): ?array
    {
        return ['href'];
    }

    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
    {
        if (str_starts_with($value, 'mailto:')) {
            $lowerValue = strtolower(rawurldecode($value));

            if (str_contains($lowerValue, '<') ||
                str_contains($lowerValue, 'javascript:') ||
                str_contains($lowerValue, 'data:') ||
                str_contains($lowerValue, 'vbscript:') ||
                str_contains($lowerValue, 'file:') ||
                preg_match('/[?&]on\w+\s*=/i', $lowerValue) ||
                preg_match('/[?&]on\w+\s*%3d/i', $lowerValue) ||
                preg_match('/\son\w+\s*=/i', $lowerValue) ||
                preg_match('/\son\w+\s*%3d/i', $lowerValue)
            ) {
                // By returning null, the HtmlSanitizer will keep the attribute but set its value to null.
                // In Symfony's Node::renderAttributes(), an attribute with a null value is skipped.
                // If we want to remove the entire link but keep its text content, we would need a different approach
                // as the current HtmlSanitizer version (6.x) doesn't easily allow an attribute sanitizer to trigger
                // an element "block" action. However, removing the href attribute effectively disables the link.
                return null;
            }
        }

        return $value;
    }
}
