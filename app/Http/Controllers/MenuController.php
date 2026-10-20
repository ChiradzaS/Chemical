<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Show the main menu (resources/views/view.blade.php).
     * Only reachable when logged in — the route sits inside the 'auth' middleware group.
     */
    public function index()
    {
        $user = Auth::user();

        return response()
            ->view('view', ['user' => $user])
            // Stop the browser caching the menu, so pressing Back after logout
            // doesn't show it again from cache
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}