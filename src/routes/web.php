<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

use App\GoogleDrive;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth.global'], function () {
    Route::get('/', function () {
        return view('index', [
            'files' => (new GoogleDrive())->list(),
        ]);
    });
});

Route::get('/anmelden', function (Request $request) {
    if ($request->cookie('authenticated', false)) {
        return redirect()->to('/');
    }

    return view('login');
});

Route::get('/abmelden', function (Request $request) {
    $cookie = cookie('authenticated', false, 60 * 24);

    return response()
        ->redirectTo('/')
        ->withCookies([$cookie]);
});

Route::post('/anmelden', function (Request $request) {
    $password = $request->get('password', null);

    if ($password === null) {
        return redirect()
            ->back()
            ->withErrors([
                'password' => 'Bitte gebe ein Passwort ein!',
            ]);
    }

    if (!Hash::check($password, config('app.password'))) {
        return redirect()
            ->back()
            ->withErrors([
                'password' => 'Das Passwort ist falsch! Wende dich an Anna und Daniel falls du es vergessen hast.',
            ]);
    }

    $cookie = cookie('authenticated', true, 60 * 24);

    return response()
        ->redirectTo('/')
        ->withCookies([$cookie]);
})->middleware('throttle:20,1');
