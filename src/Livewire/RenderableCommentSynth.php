<?php

namespace Kirschbaum\Commentions\Livewire;

use Kirschbaum\Commentions\Contracts\RenderableComment;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class RenderableCommentSynth extends Synth
{
    public static $key = 'renderable-comment';

    public static function match($target)
    {
        return $target instanceof RenderableComment;
    }

    public function dehydrate($target)
    {
        return [[
            //
        ], []];
    }

    public function hydrate($value)
    {
        $instance = new RenderableComment;

        // $instance->street = $value['street'];
        // $instance->city = $value['city'];
        // $instance->state = $value['state'];
        // $instance->zip = $value['zip'];

        return $instance;
    }
}
