<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;

/**
 * Application routes.
 */
Route::any('/', [PageController::class, 'front']);
Route::any('page', [PageController::class, 'page']);
Route::any('search', [PageController::class, 'search']);

Route::any('single', [PostController::class, 'single']);
Route::any('archive', [PostController::class, 'collection']);
Route::any('category', [PostController::class, 'collection']);
Route::any('tag', [PostController::class, 'collection']);
Route::any('blog', [PostController::class, 'collection']);

Route::fallback([PageController::class, 'error404']);

