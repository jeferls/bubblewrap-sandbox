<?php

namespace Greenn\Libs;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel facade exposed as Greenn\Libs\BubblewrapSandbox.
 *
 * The underlying implementation is bound to the container as "sandbox.bwrap".
 */
class BubblewrapSandbox extends Facade
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
