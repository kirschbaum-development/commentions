<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Actions\StoreCommentAttachments;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Kirschbaum\Commentions\Livewire\Concerns\HasSidebar;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

class Comments extends Component
{
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use HasSidebar;
    use WithFileUploads;

    public Model $record;

    public string $commentBody = '';

    public ?string $tipTapCssClasses = null;

    // Resolved once from the per-component setting (or config) at mount and
    // serialized so it survives subsequent requests. #[Locked] lets the client
    // read it but not change it, so a closure-based gate such as
    // enableAttachments(fn () => $user->isAdmin()) cannot be flipped on by
    // tampering with the request payload.
    #[Locked]
    public ?bool $attachmentsEnabled = null;

    /** @var array<mixed> */
    public array $attachments = [];

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save()
    {
        $user = Config::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        $this->validate();

        if ($this->attachmentsAreEnabled() && $this->attachments !== []) {
            $this->validate($this->attachmentValidationRules());
        }

        $comment = SaveComment::run(
            $this->record,
            $user,
            $this->commentBody
        );

        if ($this->attachmentsAreEnabled() && $this->attachments !== []) {
            StoreCommentAttachments::run($comment, $this->attachments);
        }

        $this->clear();
        $this->dispatch('comment:saved');
    }

    public function render()
    {
        return view('commentions::comments');
    }

    #[On('body:updated')]
    #[Renderless]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachments[$index]);

        $this->attachments = array_values($this->attachments);
    }

    public function clear(): void
    {
        $this->commentBody = '';
        $this->attachments = [];

        $this->dispatch('comments:content:cleared');
    }

    public function attachmentsAreEnabled(): bool
    {
        return $this->attachmentsEnabled ?? (bool) config('commentions.attachments.enabled', false);
    }

    public function getPlaceholder(): string
    {
        return __('commentions::comments.placeholder');
    }

    public function getTipTapCssClasses(): ?string
    {
        return $this->tipTapCssClasses ?? Config::getTipTapCssClasses();
    }

    /**
     * Validation rules applied to pending attachment uploads.
     *
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
