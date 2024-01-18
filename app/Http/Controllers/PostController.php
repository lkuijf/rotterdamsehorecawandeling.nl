<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Show a single post.
     */
    public function single()
    {
        return view('blog.single');
    }

    /**
     * Show a post collection.
     */
    public function collection()
    {
        return view('blog.archive');
    }

}
