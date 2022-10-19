<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Laravel\Sanctum\PersonalAccessToken;

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

Route::get('/', function () {
    return view('welcome');
});


Route::get('artisan/migrate', function (){
    try {
        echo '<br>init migrate...';
        $output = new BufferedOutput;
        Artisan::call('migrate', [], $output);
        dump($output);
        echo 'done migrate';
    } catch (Exception $e) {
        return $e->getMessage();
    }
});