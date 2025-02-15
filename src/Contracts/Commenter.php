<?php

namespace Kirschbaum\FilamentComments\Contracts;

interface Commenter
{
    /**
     * Get the identifier key for the object. Usually the primary key.
     *
     * @return int|string|null
     */
    public function getKey();
    
    /**
     * @return string
     */
    public function getMorphClass();
}
