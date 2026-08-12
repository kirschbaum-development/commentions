<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Actions\StoreCommentAttachments;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Concerns\HasAttachments;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Kirschbaum\Commentions\Livewire\Concerns\HasRatings;
use Kirschbaum\Commentions\Livewire\Concerns\HasSidebar;
use Kirschbaum\Commentions\Livewire\Concerns\HasToolbarButtons;
use Kirschbaum\Commentions\Livewire\Concerns\IsReadonly;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

class Comments extends Component
{
    use HasAttachments;
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use HasRatings;
    use HasSidebar;
    use HasToolbarButtons;
    use IsReadonly;
    use WithFileUploads;

    public Model $record;

    public string $commentBody = '';

    public ?string $tipTapCssClasses = null;

    public ?int $rating = null;

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save(): void
    {
        if ($this->isReadonly()) {
            return;
        }

        $user = Config::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        $this->validate();

        $ratingsEnabled = $this->ratingsAreEnabled();

        if ($ratingsEnabled) {
            $this->validate([
                'rating' => ['nullable', 'integer', 'min:1', 'max:' . $this->getMaxRating()],
            ]);
        }

        if ($this->attachmentsAreEnabled() && $this->attachments !== []) {
            $this->validate($this->attachmentValidationRules());
        }

        $comment = SaveComment::run(
            $this->record,
            $user,
            $this->commentBody,
            $ratingsEnabled ? $this->rating : null,
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

    public function clear(): void
    {
        $this->commentBody = '';
        $this->rating = null;
        $this->attachments = [];

        $this->dispatch('comments:content:cleared');
    }

    public function getPlaceholder(): string
    {
        return __('commentions::comments.placeholder');
    }

    public function getTipTapCssClasses(): ?string
    {
        return $this->tipTapCssClasses ?? Config::getTipTapCssClasses();
    }
}
