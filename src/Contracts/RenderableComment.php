<?php

namespace Kirschbaum\Commentions\Contracts;

use Carbon\CarbonInterface;

interface RenderableComment
{
    public function isComment(): bool;

    public function getId(): string|int|null;

    public function getAuthorName(): string;

    public function getAuthorAvatar(): ?string;

    public function getBody(): string;

    public function getParsedBody(): string;

    public function getCreatedAt(): \DateTime|CarbonInterface;

    public function getUpdatedAt(): \DateTime|CarbonInterface;

    public function getLabel(): ?string;

    public function getContentHash(): string;
}
