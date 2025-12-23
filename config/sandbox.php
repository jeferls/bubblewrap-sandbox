<?php

return array(
    /*
    |--------------------------------------------------------------------------
    | bwrap binary path
    |--------------------------------------------------------------------------
    |
    | Binary used to spawn the sandbox. Keep "bwrap" when it is already
    | available in the system PATH.
    */
    'binary' => 'bwrap',

    /*
    |--------------------------------------------------------------------------
    | Base arguments
    |--------------------------------------------------------------------------
    |
    | Adjust bubblewrap default parameters here. Avoid removing
    | --unshare-all, --die-with-parent, and the /proc and /dev mounts.
    */
    'base_args' => array(
        '--unshare-all',
        '--die-with-parent',
        '--new-session',
        '--proc',
        '/proc',
        '--dev',
        '/dev',
        '--tmpfs',
        '/tmp',
        '--tmpfs',
        '/run',
        '--setenv',
        'PATH',
        '/usr/bin:/bin:/usr/sbin:/sbin',
        '--chdir',
        '/tmp',
    ),

    /*
    |--------------------------------------------------------------------------
    | Read-only bind mounts
    |--------------------------------------------------------------------------
    |
    | Host paths that will be mounted as read-only inside the sandbox.
    */
    'read_only_binds' => array(
        '/usr',
        '/bin',
        '/lib',
        '/lib64',
        '/sbin',
        '/etc/resolv.conf',
        '/etc/ssl',
    ),

    /*
    |--------------------------------------------------------------------------
    | Writable bind mounts
    |--------------------------------------------------------------------------
    |
    | Host paths exposed with write access inside the sandbox.
    */
    'write_binds' => array(
        '/tmp',
    ),
);
