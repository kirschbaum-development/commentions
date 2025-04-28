<?php

namespace Kirschbaum\Commentions\Livewire;

use Kirschbaum\Commentions\Contracts\RenderableComment;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class RenderableCommentSynth extends Synth
{
    public static $key = 'renderable-comment';

    public function dehydrate($target)
    {
        return [[
            //
        ], []];
    }

    public function hydrate($value)
    {
        $instance = new RenderableComment();

        return $instance;
    }

    public static function match($target)
    {
        return $target instanceof RenderableComment;
    }
}
