<?php

namespace Kirschbaum\Commentions;

use Livewire\Wireable;
use Kirschbaum\Commentions\Contracts\RenderableComment as RenderableCommentContract;
use DateTime;
use Carbon\Carbon;

class RenderableComment implements RenderableCommentContract, Wireable
{
    protected bool $isComment;
    protected string|int|null $id;
    protected string $authorName;
    protected string $authorAvatar;
    protected string $body;
    protected ?string $parsedBody;
    protected DateTime|Carbon $createdAt;
    protected DateTime|Carbon $updatedAt;
    protected bool $canEdit;
    protected bool $canDelete;

    public function __construct(
        bool $isComment = false,
        string|int|null $id = null,
        string $authorName = '',
        string $authorAvatar = '',
        string $body = '',
        ?string $parsedBody = null,
        DateTime|Carbon $createdAt = new Carbon(),
        DateTime|Carbon $updatedAt = new Carbon(),
        bool $canEdit = false,
        bool $canDelete = false
    ) {
        $this->isComment = $isComment;
        $this->id = $id;
        $this->authorName = $authorName;
        $this->authorAvatar = $authorAvatar;
        $this->body = $body;
        $this->parsedBody = $parsedBody;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->canEdit = $canEdit;
        $this->canDelete = $canDelete;
    }

    public function isComment(): bool
    {
        return $this->isComment;
    }

    public function getId(): string|int|null
    {
        return $this->id;
    }

    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    public function getAuthorAvatar(): string
    {
        return $this->authorAvatar;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getParsedBody(): string
    {
        return $this->parsedBody ?? $this->body;
    }

    public function getCreatedAt(): DateTime|Carbon
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime|Carbon
    {
        return $this->updatedAt;
    }

    public function canEdit(): bool
    {
        return $this->canEdit;
    }

    public function canDelete(): bool
    {
        return $this->canDelete;
    }

    public function toLivewire()
    {
        return [
            'isComment' => $this->isComment,
            'id' => $this->id,
            'authorName' => $this->authorName,
            'authorAvatar' => $this->authorAvatar,
            'body' => $this->body,
            'parsedBody' => $this->parsedBody,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            $value['isComment'],
            $value['id'],
            $value['authorName'],
            $value['authorAvatar'],
            $value['body'],
            $value['parsedBody'],
            new Carbon($value['createdAt']),
            new Carbon($value['updatedAt']),
            $value['canEdit'],
            $value['canDelete']
        );
    }
}
