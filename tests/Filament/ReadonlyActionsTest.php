<?php

use Kirschbaum\Commentions\Filament\Actions\CommentsAction;
use Tests\Models\User;

// Filament 4 specific tests for readonly functionality in actions

test('Filament 4 CommentsAction readonly method sets readonly state', function () {
    $action = CommentsAction::make();

    // Default should be false
    expect($action->isReadonly())->toBeFalse();

    // Setting readonly to true
    $action->readonly(true);
    expect($action->isReadonly())->toBeTrue();

    // Setting readonly to false
    $action->readonly(false);
    expect($action->isReadonly())->toBeFalse();

    // Calling readonly() without parameter should default to true
    $action->readonly();
    expect($action->isReadonly())->toBeTrue();
});

test('Filament 4 CommentsAction readonly method returns fluent interface', function () {
    $action = CommentsAction::make();

    $result = $action->readonly();

    expect($result)->toBeInstanceOf(CommentsAction::class);
    expect($result)->toBe($action); // Should return the same instance
});

test('Filament 4 CommentsAction can chain readonly with other methods', function () {
    $users = User::factory()->count(3)->create();

    $action = CommentsAction::make()
        ->readonly()
        ->mentionables($users)
        ->label('Read-only Comments');

    expect($action->isReadonly())->toBeTrue();
    expect($action->getMentionables())->toBe($users);
});

test('Filament 4 CommentsAction readonly state is properly maintained', function () {
    $users = User::factory()->count(2)->create();

    $action = CommentsAction::make()
        ->readonly()
        ->mentionables($users);

    expect($action->isReadonly())->toBeTrue();

    // Test chaining doesn't break readonly state
    $action->label('Custom Label');
    expect($action->isReadonly())->toBeTrue();
});

test('Filament 4 CommentsAction conditional readonly functionality', function () {
    $action = CommentsAction::make();

    // Test conditional readonly based on some logic
    $shouldBeReadonly = true;
    $action->readonly($shouldBeReadonly);
    expect($action->isReadonly())->toBeTrue();

    $shouldBeReadonly = false;
    $action->readonly($shouldBeReadonly);
    expect($action->isReadonly())->toBeFalse();
});
