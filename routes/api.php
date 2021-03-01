<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;


const BASE = '/api/v1/user/';
const GUEST = '/api/v1/guest/';
const ADMIN = '/api/v1/admin/';

// Route::get(GUEST . 'facebook/redirect', function () {
//     return Socialite::driver('facebook')->redirect();
// });

// Route::get(GUEST . 'facebook/callback', function () {
//     $user = Socialite::driver('facebook')->user();

//     // $user->token
// });

Route::group(['middleware' => 'auth.jwt'], function () {
});
