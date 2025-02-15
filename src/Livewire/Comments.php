<?php

namespace Kirschbaum\FilamentComments\Livewire;

use Closure;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\FilamentComments\Actions\SaveComment;
use Kirschbaum\FilamentComments\Contracts\Commenter;
use Kirschbaum\FilamentComments\FilamentComments;

class Comments extends Component
{
    public Model $record;

    /**
     * @var Commenter[]
     */
    public array|Collection $mentionables = [];

    public string $commentBody = '';

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    public function save()
    {
        $this->validate();

        SaveComment::run(
            $this->record, 
            FilamentComments::resolveAuthenticatedUser(), 
            $this->commentBody
        );

        $this->clear();
        $this->dispatch('comment:saved');
    }

    public function render()
    {
        return view('filament-comments::comments');
    }

    #[On('body:updated')]
    public function updateCommentBodyContent($value): void
    {
        $this->commentBody = $value;
    }

    public function clear(): void
    {
        $this->commentBody = '';

        $this->dispatch('comments:content:cleared');
    }

    #[Computed]
    public function mentions()
    {
        return is_callable($this->mentionables)
            ? call_user_func_array($this->mentionables, [$this->record])
            : $this->mentionables;
    }
}
