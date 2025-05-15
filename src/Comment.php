<?php

namespace Kirschbaum\Commentions;

use Carbon\Carbon;
use Closure;
use DateTime;
use Filament\Models\Contracts\HasAvatar;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Actions\HtmlToMarkdown;
use Kirschbaum\Commentions\Actions\ParseComment;
use Kirschbaum\Commentions\Actions\ToggleCommentReaction;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\Contracts\Commenter;
use Kirschbaum\Commentions\Contracts\RenderableComment;
use Kirschbaum\Commentions\Database\Factories\CommentFactory;
use Spatie\Color\Rgb;

/**
 * @property int $id
 * @property string $body
 * @property string $body_markdown
 * @property string $body_parsed
 * @property int $author_id
 * @property Model|Commenter $author
 * @property Commentable $commentable
 * @property-read DateTime|Carbon $created_at
 * @property-read DateTime|Carbon $updated_at
 */
class Comment extends Model implements RenderableComment
{
    use HasFactory;

    protected $fillable = [
        'body',
        'author_type',
        'author_id',
    ];

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function bodyParsed(): Attribute
    {
        return Attribute::make(
            get: fn () => ParseComment::run($this->body),
        );
    }

    public function bodyMarkdown(): Attribute
    {
        return Attribute::make(
            get: fn () => HtmlToMarkdown::run($this->body),
        );
    }

    public function getBodyMarkdown(?Closure $mentionedCallback = null): string
    {
        return HtmlToMarkdown::run(
            html: $this->body,
            mentionedCallback: $mentionedCallback,
        );
    }

    public function isAuthor(Commenter $author)
    {
        return $this->author_id === $author->getKey();
    }

    /**
     * Get the IDs of users mentioned in the comment body.
     *
     * @return Collection<Commenter>
     */
    public function getMentioned(): Collection
    {
        $commenterModel = Config::getCommenterModel();

        preg_match_all(
            '/<span[^>]*data-type="mention"[^>]*data-id="(\d+)"[^>]*>/',
            $this->body,
            $matches
        );

        return collect($matches[1] ?? [])
            ->map(fn ($userId) => $commenterModel::find($userId))
            ->filter(fn ($mentioned) => $mentioned !== null);
    }

    public function isComment(): bool
    {
        return true;
    }

    public function getId(): string|int|null
    {
        return $this->id;
    }

    public function getAuthorName(): string
    {
        return $this->author->name;
    }

    public function getAuthorAvatar(): string
    {
        $avatar = null;

        if ($this->author instanceof HasAvatar) {
            $avatar = $this->author->getFilamentAvatarUrl();
        }

        if (! is_null($avatar)) {
            return $avatar;
        }

        $name = str(Manager::getName($this->author))
            ->trim()
            ->explode(' ')
            ->map(fn (string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
            ->join(' ');

        $backgroundColor = Rgb::fromString('rgb(' . FilamentColor::getColors()['gray'][950] . ')')->toHex();

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=' . str($backgroundColor)->after('#');
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParsedBody(): string
    {
        return $this->body_parsed;
    }

    public function getCreatedAt(): DateTime|Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTime|Carbon
    {
        return $this->updated_at;
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function toggleReaction(string $reaction): void
    {
        ToggleCommentReaction::run($this, $reaction, Config::resolveAuthenticatedUser());
    }

    public function getLabel(): ?string
    {
        return null;
    }

    public function getContentHash(): string
    {
        return md5(json_encode([
            'body' => $this->body,
            'reactions' => $this->reactions->pluck('id'),
        ]));
    }

    protected static function newFactory()
    {
        return CommentFactory::new();
    }
}
