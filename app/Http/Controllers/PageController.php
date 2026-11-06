<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    /**
     * Show the public landing page.
     */
    public function landing()
    {
        return view('landing');
    }

    /**
     * Show the public download page.
     */
    public function download()
    {
        return view('download');
    }
}
