<?php

namespace Kirschbaum\Commentions\Contracts;

interface RenderableComment
{
    public function isComment(): bool;
    public function getId(): string|int|null;
    public function getAuthorName(): string;
    public function getAuthorAvatar(): string;
    public function getBody(): string;
    public function getParsedBody(): string;
    public function getCreatedAt(): \DateTime|\Carbon\Carbon;
    public function getUpdatedAt(): \DateTime|\Carbon\Carbon;
    public function canEdit(): bool;
    public function canDelete(): bool;
}
