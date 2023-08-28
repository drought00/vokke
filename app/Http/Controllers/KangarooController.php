<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KangarooController extends Controller
{
    public function index()
    {
        return view('index');
    }
}
