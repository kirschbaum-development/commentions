<?php

namespace Kirschbaum\Commentions\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;

class CommentList extends Component
{
    use HasMentions;

    public Model $record;

    public bool $pollingEnabled = false;

    public int $pollingInterval = 60;

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->getComments();
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    public function reloadComments(): void
    {
        unset($this->comments);
    }
}
