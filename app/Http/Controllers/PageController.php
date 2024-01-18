<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Show the home page.
     */
    public function front()
    {
        return view('pages.front');
    }

    /**
     * Show a page.
     */
    public function page()
    {
        return view('pages.default');
    }

    /**
     * Show search results.
     */
    public function search()
    {
        return view('pages.search');
    }

    /**
     * Show 404 not found page.
     */
    public function error404()
    {
        return view('errors.404');
    }

}
