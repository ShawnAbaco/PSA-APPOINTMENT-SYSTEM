<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('client.landing-page');
});

Route::get('/appointment', function () {
    return view('client.appointment');
});
