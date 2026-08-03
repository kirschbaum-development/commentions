<?php

use Kirschbaum\Commentions\Filament\Concerns\IsReadonly as FilamentIsReadonly;
use Kirschbaum\Commentions\Livewire\Concerns\IsReadonly as LivewireIsReadonly;

// Mock class to test Filament IsReadonly trait
class MockFilamentAction
{
    use FilamentIsReadonly;
}

// Mock class to test Livewire IsReadonly trait
class MockLivewireComponent
{
    use LivewireIsReadonly;
}

test('Filament IsReadonly trait has correct default value', function () {
    $mock = new MockFilamentAction();

    expect($mock->isReadonly())->toBeFalse();
});

test('Filament IsReadonly trait readonly method sets value correctly', function () {
    $mock = new MockFilamentAction();

    // Test setting to true
    $result = $mock->readonly(true);
    expect($mock->isReadonly())->toBeTrue();
    expect($result)->toBe($mock); // Should return self for fluent interface

    // Test setting to false
    $mock->readonly(false);
    expect($mock->isReadonly())->toBeFalse();

    // Test default parameter (should be true)
    $mock->readonly();
    expect($mock->isReadonly())->toBeTrue();
});

test('Filament IsReadonly trait returns fluent interface', function () {
    $mock = new MockFilamentAction();

    $result = $mock->readonly(true);

    expect($result)->toBeInstanceOf(MockFilamentAction::class);
    expect($result)->toBe($mock);
});

test('Livewire IsReadonly trait has correct default value', function () {
    $mock = new MockLivewireComponent();

    expect($mock->isReadonly())->toBeFalse();
});

test('Livewire IsReadonly trait readonly property can be set', function () {
    $mock = new MockLivewireComponent();

    // Test setting to true
    $mock->readonly = true;
    expect($mock->isReadonly())->toBeTrue();

    // Test setting to false
    $mock->readonly = false;
    expect($mock->isReadonly())->toBeFalse();
});

test('Livewire IsReadonly trait isReadonly method returns correct value', function () {
    $mock = new MockLivewireComponent();

    // Default should be false
    expect($mock->isReadonly())->toBeFalse();

    // After setting to true
    $mock->readonly = true;
    expect($mock->isReadonly())->toBeTrue();

    // After setting back to false
    $mock->readonly = false;
    expect($mock->isReadonly())->toBeFalse();
});

test('traits work independently', function () {
    $filamentMock = new MockFilamentAction();
    $livewireMock = new MockLivewireComponent();

    // Set one to readonly
    $filamentMock->readonly(true);

    // The other should remain non-readonly
    expect($filamentMock->isReadonly())->toBeTrue();
    expect($livewireMock->isReadonly())->toBeFalse();

    // Set the other to readonly
    $livewireMock->readonly = true;

    expect($filamentMock->isReadonly())->toBeTrue();
    expect($livewireMock->isReadonly())->toBeTrue();

    // Change first back to non-readonly
    $filamentMock->readonly(false);

    expect($filamentMock->isReadonly())->toBeFalse();
    expect($livewireMock->isReadonly())->toBeTrue();
});
