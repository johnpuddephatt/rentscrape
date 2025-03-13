<?php

use App\Http\Controllers\FetchController;
use App\Livewire\Home;
use App\Livewire\Post\Show as PostShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/article/{post:slug}', PostShow::class)->name('post.show');
Route::get('/zoopla/{outcode}/{listing_type}', [FetchController::class, 'zoopla'])->name('zoopla');
