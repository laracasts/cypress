<?php

use Illuminate\Support\Facades\Route;
use Laracasts\Cypress\Controllers\CypressController;

Route::post('/__cypress__/factory', [CypressController::class, 'factory'])->name('cypress.factory');
Route::post('/__cypress__/login', [CypressController::class, 'login'])->name('cypress.login');
Route::post('/__cypress__/logout', [CypressController::class, 'logout'])->name('cypress.logout');
Route::post('/__cypress__/artisan', [CypressController::class, 'artisan'])->name('cypress.artisan');
Route::post('/__cypress__/run-php', [CypressController::class, 'runPhp'])->name('cypress.run-php');
Route::get('/__cypress__/csrf_token', [CypressController::class, 'csrfToken'])->name('cypress.csrf-token');
Route::post('/__cypress__/routes', [CypressController::class, 'routes'])->name('cypress.routes');
Route::post('/__cypress__/current-user', [CypressController::class, 'currentUser'])->name('cypress.current-user');
