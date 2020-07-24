<?php

use Illuminate\Support\Facades\Route;
use Laracasts\Cypress\Controllers\CypressController;

Route::get('/__cypress__/factory', [CypressController::class, 'factory'])->name('cypress.factory');
Route::get('/__cypress__/login', [CypressController::class, 'login'])->name('cypress.login');
Route::get('/__cypress__/logout', [CypressController::class, 'logout'])->name('cypress.logout');
Route::get('/__cypress__/artisan', [CypressController::class, 'artisan'])->name('cypress.artisan');

