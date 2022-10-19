<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('get_code', [VerificationController::class, 'getCode']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('verify_code', [VerificationController::class, 'verifyCode']);
    Route::post('update_user', [VerificationController::class, 'updateUser']);
    Route::get('user', [VerificationController::class, 'getUser']);
    Route::post('add_contact', [ContactController::class, 'addContact']);

    // approved friends
    Route::get('contacts', [ContactController::class, 'getContacts']);
    // get friend requests
    Route::get('friend_requests', [ContactController::class, 'getPendingContacts']);
    // Accept particular request
    Route::post('accept_contact_request', [ContactController::class, 'acceptPendingContact']);

    // send image
    Route::post('send_image', [ImageController::class, 'sendImage']);
    // get images
    Route::get('get_images', [ImageController::class, 'getImages']);

    Route::post('delete_account', [UserController::class, 'deleteAccount']);

    Route::post('send_feedback', [UserController::class, 'sendFeedback']);

    Route::post('update_phone', [UserController::class, 'updatePhone']);

});

Route::post('sign_in', [VerificationController::class, 'signIn']);
Route::post('verify_sign_in', [VerificationController::class, 'verifySignIn']);


