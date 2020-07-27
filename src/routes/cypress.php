<?php

use Illuminate\Support\Facades\Route;
use Laracasts\Cypress\Controllers\CypressController;

Route::post('/__cypress__/factory', [CypressController::class, 'factory'])->name('cypress.factory');
Route::post('/__cypress__/login', [CypressController::class, 'login'])->name('cypress.login');
Route::post('/__cypress__/logout', [CypressController::class, 'logout'])->name('cypress.logout');
Route::post('/__cypress__/artisan', [CypressController::class, 'artisan'])->name('cypress.artisan');
