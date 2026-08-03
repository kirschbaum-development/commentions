<?php

namespace Kirschbaum\Commentions\Livewire\Concerns;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\ResolvesDynamicLivewireProperties;

trait InteractsWithCommentSchemas {}

if (trait_exists(InteractsWithSchemas::class)) {
    trait InteractsWithCommentSchemasBridge
    {
        use InteractsWithSchemas;
        use ResolvesDynamicLivewireProperties;
    }
} else {
    trait InteractsWithCommentSchemasBridge
    {
        use InteractsWithForms;
    }
}
