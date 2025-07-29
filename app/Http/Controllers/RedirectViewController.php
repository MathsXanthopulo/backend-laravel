<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectViewController extends Controller
{
    /**
     * Display the redirects index page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('redirect.index');
    }
}
