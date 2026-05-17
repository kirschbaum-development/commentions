<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Kirschbaum\Commentions\Livewire\Concerns\HasSidebar;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Comments extends Component
{
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use HasSidebar;

    public Model $record;

    public string $commentBody = '';

    public ?string $tipTapCssClasses = null;

    public ?bool $ratingsEnabled = null;

    public ?int $maxRating = null;

    public ?int $rating = null;

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    #[Renderless]
    public function save()
    {
        $user = Config::resolveAuthenticatedUser();

        if (! $user) {
            return;
        }

        $this->validate();

        if ($this->ratingsAreEnabled()) {
            $this->validate([
                'rating' => ['nullable', 'integer', 'min:1', 'max:' . $this->getMaxRating()],
            ]);
        }

        SaveComment::run(
            $this->record,
            $user,
            $this->commentBody,
            $this->ratingsAreEnabled() ? $this->rating : null,
        );

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

    #[Renderless]
    public function clear(): void
    {
        $this->commentBody = '';
        $this->rating = null;

        $this->dispatch('comments:content:cleared');
    }

    public function ratingsAreEnabled(): bool
    {
        return $this->ratingsEnabled ?? (bool) config('commentions.ratings.enabled', false);
    }

    public function getMaxRating(): int
    {
        return $this->maxRating ?? (int) config('commentions.ratings.max', 5);
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
