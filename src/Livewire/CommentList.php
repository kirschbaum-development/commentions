<?php

namespace Kirschbaum\Commentions\Livewire;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;
use Kirschbaum\Commentions\Livewire\Concerns\HasPolling;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class CommentList extends Component
{
    use HasMentions;
    use HasPolling;

    public Model $record;

    public function render()
    {
        return view('commentions::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        info('Getting comments');
        return $this->record->getComments();
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    #[On('comment:reactions-updated')]
    public function reloadComments(): void
    {
        info('Reloading comments');
        unset($this->comments);
    }
}
