<?php

declare(strict_types=1);

return [
    /**
     * Two-factor authentication guest configuration.
     */
    'guest' => [
        /**
         * Two-factor authentication guest guard.
         */
        'guard' => 'web',

        /**
         * Two-factor authentication guest middleware guard.
         */
        'middleware' => 'guest',
    ],

    /**
     * Two-factor authentication auth configuration.
     */
    'auth' => [
        /**
         * Two-factor authentication window.
         */
        //        'window' => 0,
        /**
         * Old OTP is forbidden when set to true regardless of the window.
         */
        'forbid_old_otp' => false,

        /**
         * Two-factor authentication guard.
         */
        'guard' => 'web',

        /**
         * Two-factor authentication middleware.
         */
        'middleware' => 'auth',

        /**
         * Two-factor authentication throttle.
         */
        'throttle' => [
            /**
             * Number of attempts before requests is throttled.
             */
            'attempts' => 6,

            /**
             * How many minutes to wait before another attempts can be made.
             */
            'decay' => 1, // in minutes
        ],
    ],
];
