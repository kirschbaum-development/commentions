<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Livewire\Concerns\HasAvatars;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPagination;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Kirschbaum\Commentions\Livewire\Concerns\HasRatings;
use Kirschbaum\Commentions\Livewire\Concerns\HasReactions;
use Kirschbaum\Commentions\Livewire\Concerns\HasToolbarButtons;
use Kirschbaum\Commentions\Livewire\Concerns\IsReadonly;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    use HasAvatars;
    use HasMentions;
    use HasPagination;
    use HasPolling;
    use HasRatings;
    use HasReactions;
    use HasToolbarButtons;
    use IsReadonly;

    public Model $record;

    public ?string $tipTapCssClasses = null;

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->getComments($this->paginate ? $this->perPage : null);
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    #[On('commentions:refresh')]
    public function reloadComments(): void
    {
        unset($this->comments);
    }
}
