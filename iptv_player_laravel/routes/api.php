<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlaylistController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/upload-playlist', [PlaylistController::class, 'uploadPlaylist']);
Route::get('/playlists', [PlaylistController::class, 'getPlaylists']);
Route::get('/fetch-playlist', [PlaylistController::class, 'fetchPlaylist']);
Route::get('/dashboard-content', [PlaylistController::class, 'getDashboardContent']);

// Route::post('/parse-m3u', [PlaylistController::class, 'parseM3UContent']);
// Route::get('/m3u-content/{file}', [PlaylistController::class, 'parseM3UContent']);

Route::get('/m3u-content/{id}', [PlaylistController::class, 'getM3UContentByPlaylist']);
Route::get('/playlist/{id}/content/{type}', [PlaylistController::class, 'getM3UContentByType']);

