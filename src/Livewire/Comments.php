<?php

namespace Kirschbaum\Commentions\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Kirschbaum\Commentions\Config;
use Livewire\Attributes\Renderless;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Actions\SaveComment;
use Kirschbaum\Commentions\Livewire\Concerns\HasMentions;

class Comments extends Component
{
    use HasMentions;

    public Model $record;

    public string $commentBody = '';

    public bool $pollingEnabled = false;

    public int $pollingInterval = 60;

    protected $rules = [
        'commentBody' => 'required|string',
    ];

    #[Renderless]
    public function save()
    {
        $this->validate();

        SaveComment::run(
            $this->record,
            Config::resolveAuthenticatedUser(),
            $this->commentBody
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

        $this->dispatch('comments:content:cleared');
    }
}
