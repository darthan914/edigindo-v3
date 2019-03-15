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
    protected $except = [
        '*/action*',
        '*/get*',
        
        '*/datatables*',
        '*/ajax*',
        '*/history*',

        '*/invoice/noAdmin',
        '*/invoice/checkFinance',
        '*/invoice/noteInvoice',
        '*/invoice/checkMaster',
        '*/checkHRD',

        '*/pr/changePurchasing',
        '*/pr/changeStatus',
        '*/pr/checkAudit',
        '*/pr/checkFinance',
        '*/pr/noteAudit',
        '*/pr/getSpkItem',

        '*/config/update',
        '*/home/notification',
        '/api/*',
        '/broadcasting/auth',

    ];
}
