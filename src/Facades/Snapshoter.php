<?php

namespace MakiDizajnerica\Snapshoter\Facades;

use Illuminate\Support\Facades\Facade;

class Snapshoter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'makidizajnerica-snapshoter';
    }
}
