<?php

namespace Kirschbaum\Commentions\Filament\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\Commenter;

trait HasSidebar
{
    protected bool $sidebarEnabled = true;

    protected bool $showSubscribers = true;

    /** @var (Closure(Model): string)|string|null */
    protected $subscribeLabelOverride = null;

    /** @var (Closure(Model): string)|string|null */
    protected $unsubscribeLabelOverride = null;

    /** @var (Closure(Model): string)|string|null */
    protected $subscribeIconOverride = null;

    /** @var (Closure(Model): string)|string|null */
    protected $unsubscribeIconOverride = null;

    /**
     * @var array{
     *   action: array{subscribe: (Closure(Model): string)|string|null, unsubscribe: (Closure(Model): string)|string|null},
     *   table: array{subscribe: (Closure(Model): string)|string|null, unsubscribe: (Closure(Model): string)|string|null}
     * }
     */
    protected array $subscriptionColorOverrides = [
        'action' => ['subscribe' => null, 'unsubscribe' => null],
        'table' => ['subscribe' => null, 'unsubscribe' => null],
    ];

    public function disableSidebar(bool $condition = true): static
    {
        $this->sidebarEnabled = ! $condition;

        return $this;
    }

    public function isSidebarEnabled(): bool
    {
        return $this->sidebarEnabled;
    }

    public function hideSubscribers(bool $condition = true): static
    {
        $this->showSubscribers = ! $condition;

        return $this;
    }

    public function showSubscribers(): bool
    {
        return $this->showSubscribers;
    }

    public function subscribeLabel(Closure|string $label): static
    {
        $this->subscribeLabelOverride = $label;

        return $this;
    }

    public function unsubscribeLabel(Closure|string $label): static
    {
        $this->unsubscribeLabelOverride = $label;

        return $this;
    }

    public function subscribeIcon(Closure|string $icon): static
    {
        $this->subscribeIconOverride = $icon;

        return $this;
    }

    public function unsubscribeIcon(Closure|string $icon): static
    {
        $this->unsubscribeIconOverride = $icon;

        return $this;
    }

    public function subscribeColor(Closure|string $color, string $context = 'action'): static
    {
        $this->subscriptionColorOverrides[$context]['subscribe'] = $color;

        return $this;
    }

    public function unsubscribeColor(Closure|string $color, string $context = 'action'): static
    {
        $this->subscriptionColorOverrides[$context]['unsubscribe'] = $color;

        return $this;
    }

    protected function resolveCurrentUser(): ?Commenter
    {
        return Config::resolveAuthenticatedUser();
    }

    protected function computeSubscriptionLabel(Model $record): string
    {
        $user = $this->resolveCurrentUser();

        if (! $user) {
            return $this->evaluateSubscriptionOverride($this->subscribeLabelOverride, $record) ?? __('commentions::comments.subscribe');
        }

        if ($record->isSubscribed($user)) {
            return $this->evaluateSubscriptionOverride($this->unsubscribeLabelOverride, $record) ?? __('commentions::comments.unsubscribe');
        }

        return $this->evaluateSubscriptionOverride($this->subscribeLabelOverride, $record) ?? __('commentions::comments.subscribe');
    }

    protected function computeSubscriptionIcon(Model $record): string
    {
        $user = $this->resolveCurrentUser();

        if (! $user) {
            return $this->evaluateSubscriptionOverride($this->subscribeIconOverride, $record) ?? 'heroicon-o-bell';
        }

        if ($record->isSubscribed($user)) {
            return $this->evaluateSubscriptionOverride($this->unsubscribeIconOverride, $record) ?? 'heroicon-s-bell-slash';
        }

        return $this->evaluateSubscriptionOverride($this->subscribeIconOverride, $record) ?? 'heroicon-o-bell';
    }

    protected function computeSubscriptionColor(Model $record, string $context = 'action'): string
    {
        $user = $this->resolveCurrentUser();

        if (! $user) {
            return $this->evaluateSubscriptionOverride($this->subscriptionColorOverrides[$context]['subscribe'] ?? null, $record) ?? ($context === 'table' ? 'primary' : 'gray');
        }

        if ($record->isSubscribed($user)) {
            return $this->evaluateSubscriptionOverride($this->subscriptionColorOverrides[$context]['unsubscribe'] ?? null, $record) ?? ($context === 'table' ? 'danger' : 'gray');
        }

        return $this->evaluateSubscriptionOverride($this->subscriptionColorOverrides[$context]['subscribe'] ?? null, $record) ?? ($context === 'table' ? 'primary' : 'gray');
    }

    /**
     * @param  (Closure(Model): string)|string|null  $override
     */
    protected function evaluateSubscriptionOverride(Closure|string|null $override, Model $record): ?string
    {
        if ($override instanceof Closure) {
            return (string) $override($record);
        }

        return $override;
    }

    protected function toggleSubscriptionForRecord(Model $record): ?bool
    {
        $user = $this->resolveCurrentUser();

        if (! $user) {
            return null;
        }

        if ($record->isSubscribed($user)) {
            $record->unsubscribe($user);

            return false;
        }

        $record->subscribe($user);

        return true;
    }
}
