<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        
        'http://localhost/LaravelCRUD/queryrest/qrystore',

        'http://localhost/LaravelCRUD/qryjobcards/store',

        'http://localhost/LaravelCRUD/qryorders/store',

        'http://localhost/LaravelCRUD/qryorders/{id}/show',

        'http://localhost/LaravelCRUD/qryorders/destroy',

        'http://localhost/LaravelCRUD/qryorderitems/destroy',

        'http://localhost/LaravelCRUD/qryorderitems/update',

        'http://localhost/LaravelCRUD/queryrest/qryUsers?custid=',
        
        'http://localhost/LaravelCRUD/qryorders/{id}/update',

        'http://localhost/LaravelCRUD/qryorderitems/store',

        'http://sailingpackaging.co.za/deliveries.create'

    ];


}
