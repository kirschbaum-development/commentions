<?php

namespace Kirschbaum\Commentions\Filament\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\Commentions\Filament\Concerns\HasSidebar;

class SubscriptionTableAction extends Action
{
    use HasSidebar;

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => $this->computeSubscriptionLabel($record))
            ->icon(fn (Model $record): string => $this->computeSubscriptionIcon($record))
            ->color(fn (Model $record): string => $this->computeSubscriptionColor($record, 'table'))
            ->action(function (Model $record) {
                $subscribed = $this->toggleSubscriptionForRecord($record);

                if ($subscribed === null) {
                    return;
                }

                $this->successNotificationTitle(
                    $subscribed ? __('commentions::comments.notification_subscribed') : __('commentions::comments.notification_unsubscribed')
                );

                $this->success();
            })
            ->requiresConfirmation(false);
    }

    public static function getDefaultName(): ?string
    {
        return 'subscriptionList';
    }
}
