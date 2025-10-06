<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NovelController extends Controller
{
    public function create()
    {
        return view('novel.create');
    }
}
