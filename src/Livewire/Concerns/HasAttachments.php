<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

/**
 * Pending-attachment upload plumbing for Livewire components that accept files
 * alongside a comment. Pair with Livewire's WithFileUploads on the component.
 */
trait HasAttachments
{
    use HasAttachmentSetting;

    /** @var array<mixed> */
    public array $attachments = [];

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);

        $this->attachments = array_values($this->attachments);

        $this->resetValidation('attachments');
    }

    /**
     * @return array<string, mixed>
     */
    protected function attachmentValidationRules(): array
    {
        $fileRules = ['file', 'max:' . (int) config('commentions.attachments.max_size', 10240)];

        $mimeTypes = (array) config('commentions.attachments.accepted_mime_types', []);

        if ($mimeTypes !== []) {
            $fileRules[] = 'mimetypes:' . implode(',', $mimeTypes);
        }

        return [
            'attachments' => ['array', 'max:' . (int) config('commentions.attachments.max_files', 5)],
            'attachments.*' => $fileRules,
        ];
    }
}
