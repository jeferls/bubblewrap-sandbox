<?php

namespace Greenn\Sandbox\Laravel;

use Illuminate\Support\Facades\Facade;

/**
 * Facade to access the BubblewrapSandbox binding.
 */
class Sandbox extends Facade
{
    /**
     * Get the container binding key for the facade.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sandbox.bwrap';
    }
}
