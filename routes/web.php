<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/users/data', [App\Http\Controllers\HomeController::class, 'getUsers'])->name('users.data');
