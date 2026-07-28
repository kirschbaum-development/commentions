<?php

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Filament\Infolists\Components\CommentsEntry;
use Livewire\Component;
use Tests\Models\Post;
use Tests\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Config::resolveAuthenticatedUserUsing(fn () => Auth::user());
});

/**
 * Minimal Livewire host that renders the package's `CommentsEntry`
 * inside a Filament schema, exercising the real infolist blade view.
 */
class CommentsEntryTestHarness extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public Post $record;

    public bool $disableSidebar = false;

    public bool $hideSubscribers = false;

    public function commentsInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                CommentsEntry::make('comments')
                    ->disableSidebar($this->disableSidebar)
                    ->hideSubscribers($this->hideSubscribers),
            ]);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>{{ $this->getSchema('commentsInfolist') }}</div>
        BLADE;
    }
}

test('CommentsEntry disableSidebar removes the subscription sidebar', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Post $post */
    $post = Post::factory()->create();
    $post->subscribe(User::factory()->create(['name' => 'Subscriber Sam']));

    livewire(CommentsEntryTestHarness::class, [
        'record' => $post,
        'disableSidebar' => false,
    ])->assertSee('Subscriber Sam');

    livewire(CommentsEntryTestHarness::class, [
        'record' => $post,
        'disableSidebar' => true,
    ])->assertDontSee('Subscriber Sam');
});

test('CommentsEntry hideSubscribers hides the subscribers list', function () {
    /** @var User $user */
    $user = User::factory()->create();
    actingAs($user);

    /** @var Post $post */
    $post = Post::factory()->create();
    $post->subscribe(User::factory()->create(['name' => 'Subscriber Sam']));

    livewire(CommentsEntryTestHarness::class, [
        'record' => $post,
        'hideSubscribers' => false,
    ])->assertSee('Subscriber Sam');

    livewire(CommentsEntryTestHarness::class, [
        'record' => $post,
        'hideSubscribers' => true,
    ])->assertDontSee('Subscriber Sam');
});
