<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\SubscriptionController as ApiSubscriptionController;
use App\Http\Controllers\Webhook\SubscriptionController as WebhookSubscriptionController;
use App\Http\Controllers\Api\AccountController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register',[AuthController::class,'register']);
Route::post('login', [AuthController::class,'login']);
Route::post('refresh', [AuthController::class,'refresh']);
Route::post('logout', [AuthController::class,'logout']);

Route::post('account/register',[AccountController::class, 'register'])->middleware('auth:api');
Route::get('account/cards',[AccountController::class, 'cards'])->middleware('auth:api');
Route::post('subscription/register',[ApiSubscriptionController::class, 'register'])->middleware('auth:api');
Route::get('subscription/check',[ApiSubscriptionController::class, 'check'])->middleware('auth:api');
Route::post('subscription/cancel',[ApiSubscriptionController::class, 'cancel'])->middleware('auth:api');
Route::post('webhook/subscription/update',[WebhookSubscriptionController::class, 'update']);
