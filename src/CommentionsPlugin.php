<?php

namespace Kirschbaum\Commentions;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CommentionsPlugin implements Plugin
{
    public function getId(): string
    {
        return CommentionsServiceProvider::$name;
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void {}

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }
    
    public function disallowEdits(): static
    {
        Config::allowEdits(false);
        
        return $this;
    }
    
    public function disallowDeletes(): static
    {
        Config::allowDeletes(false);
        
        return $this;
    }
}
