<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = ['attendance/webservice','attendance/attendance_log','beaconmanager/webservice','events/webservice','visitors/visitor_log','attendance/attendance_test_log',
        //
    ];
}
