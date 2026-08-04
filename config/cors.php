
<?php

return [

    /*
     * You can enable CORS for specific paths.
     *
     * The paths should be relative to the base URL (e.g. `api/*`).
     *
     * If you want to enable CORS for all paths, use `*`.
     *
     * The wildcard `*` is also supported for patterns like `api/*`.
     */
    'paths' => ['LaravelCRUD/*', 'api/*', 'sanctum/csrf-cookie'], // Add your specific paths here, or use '*' for all

    /*
     * The API's origins.
     *
     * Can be a single origin, an array of origins, or `*` to allow all origins.
     *
     * Example: ['http://localhost', 'http://localhost:3000']
     *
     * Use `*` to allow any origin (less secure, but good for development).
     */
    'allowed_origins' => ['http://localhost:3000'], // Allow your frontend's origin
    // Or for development, to allow all origins:
    // 'allowed_origins' => ['*'],

    /*
     * The API's allowed methods.
     *
     * Can be a single method, an array of methods, or `*` to allow all methods.
     */
    'allowed_methods' => ['*'],

    /*
     * The API's allowed headers.
     *
     * Can be a single header, an array of headers, or `*` to allow all headers.
     */
    'allowed_headers' => ['*'],

    /*
     * The API's exposed headers.
     *
     * Can be a single header, an array of headers, or `false` to not expose any headers.
     */
    'exposed_headers' => [],

    /*
     * The API's maximum age for the CORS preflight request.
     *
     * Can be a positive integer, or `0` to disable the preflight request.
     */
    'max_age' => 0,

    /*
     * Whether or not the API sends cookies.
     *
     * Set to `true` to allow the API to send cookies, or `false` to not allow cookies.
     */
    'supports_credentials' => false,
];
