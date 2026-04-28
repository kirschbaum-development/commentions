<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Kirschbaum\Commentions\Config;
use Kirschbaum\Commentions\Contracts\Commenter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;

trait HasSidebar
{
    public ?bool $sidebarEnabled = null;

    public ?bool $showSubscribers = null;

    public function mountHasSidebar(): void
    {
        $this->sidebarEnabled ??= (bool) config('commentions.subscriptions.show_sidebar');
        $this->showSubscribers ??= (bool) config('commentions.subscriptions.show_subscribers', true);
    }

    #[Computed]
    public function resolvedSidebarEnabled(): bool
    {
        return $this->sidebarEnabled ?? true;
    }

    #[Computed]
    public function resolvedShowSubscribers(): bool
    {
        return $this->showSubscribers ?? (bool) config('commentions.subscriptions.show_subscribers', true);
    }

    #[Computed]
    public function isSubscribed(): bool
    {
        $user = $this->getCurrentUser();

        if (! $user) {
            return false;
        }

        return $this->record->isSubscribed($user);
    }

    #[Computed]
    public function canSubscribe(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    #[Computed]
    public function subscribers(): Collection
    {
        return $this->record->getSubscribers();
    }

    #[Renderless]
    public function refreshSubscribers(): void
    {
        unset($this->isSubscribed);
        unset($this->subscribers);
    }

    #[Renderless]
    public function toggleSubscription(): void
    {
        $user = $this->getCurrentUser();

        if (! $user) {
            return;
        }

        if ($this->record->isSubscribed($user)) {
            $this->record->unsubscribe($user);

            Notification::make()
                ->title(__('commentions::comments.notification_unsubscribed'))
                ->success()
                ->send();
        } else {
            $this->record->subscribe($user);

            Notification::make()
                ->title(__('commentions::comments.notification_subscribed'))
                ->success()
                ->send();
        }

        $this->refreshSubscribers();

        $this->dispatch('commentions:subscription:toggled')->to(Config::getComponentPrefix() . 'subscription-sidebar');
    }

    protected function getCurrentUser(): ?Commenter
    {
        return Config::resolveAuthenticatedUser();
    }
}
