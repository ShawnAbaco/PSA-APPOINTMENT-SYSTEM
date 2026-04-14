<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function landing()
    {
        return view('client.landing-page');
    }
    
    public function appointment()
    {
        return view('client.appointment');
    }
}