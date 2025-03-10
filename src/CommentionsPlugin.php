<?php

namespace Kirschbaum\Commentions;

use Filament\Contracts\Plugin;
use Filament\Panel;

class CommentionsPlugin implements Plugin
{
    protected bool $allowEdits = true;
    
    protected bool $allowDeletes = true;

    public function getId(): string
    {
        return CommentionsServiceProvider::$name;
    }

    public function register(Panel $panel): void 
    {
        Config::allowEdits($this->allowEdits);
        Config::allowDeletes($this->allowDeletes);
    }

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
        $this->allowEdits = false;
        
        return $this;
    }
    
    public function disallowDeletes(): static
    {
        $this->allowDeletes = false;
        
        return $this;
    }
}
