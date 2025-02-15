<?php

namespace Kirschbaum\FilamentComments\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Contracts\Commenter;

class CommentList extends Component
{
    public Model $record;

    /**
     * @var Commenter[]
     */
    public array $mentionables = [];

    public function render()
    {
        return view('filament-comments::comment-list');
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->record->comments()->latest()->with('author')->get();
    }

    #[On('comment:saved')]
    #[On('comment:updated')]
    #[On('comment:deleted')]
    public function reloadComments(): void
    {
        unset($this->comments);
    }
}
