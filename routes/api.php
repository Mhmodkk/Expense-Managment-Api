<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\IncomeController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register')->name('api.auth.register');
    Route::post('/login', 'login')->name('api.auth.login');
    Route::post('/reset/otp', 'resetOtp')->name('api.auth.reset.otp');
    Route::post('/reset/password', 'resetPassword')->name('api.auth.reset.password');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/otp', 'otp')->name('api.auth.otp');
        Route::post('/verify', 'verify')->name('api.auth.verify');
        Route::post('/logout', 'logout')->name('api.auth.logout');
        Route::post('/set-initial-balance', 'setInitialBalance')->name('api.user.setInitialBalance');
        Route::get('/me', 'me')->name('api.auth.me');
    });
});


Route::controller(IncomeController::class)->group(function () {

Route::middleware('auth:sanctum')->group(function () {
    //Route::resource('incomes', IncomeController::class);

    Route::get('/incomes','index')->name('api.incomes.index');
    Route::post('/incomes','store')->name('api.incomes.store');
    Route::put('/incomes/{id}','update')->name('api.incomes.update');
    Route::delete('/incomes/{id}','destroy')->name('api.incomes.delete');
    Route::get('/incomes/summary', 'summary')->name('api.incomes.summary');
    Route::get('/incomes/filter','filterByPeriod')->name('api.incomes.filter');
});
});


Route::controller(ExpenseController::class)->group(function () {

Route::middleware('auth:sanctum')->group(function () {
    //Route::resource('expenses', ExpenseController::class);

    Route::get('/expenses','index')->name('api.expenses.index');
    Route::post('/expenses','store')->name('api.expenses.store');
    Route::put('/expenses/{id}','update')->name('api.expenses.update');
    Route::delete('/expenses/{id}','destroy')->name('api.expenses.delete');
    Route::get('/expenses/summary', 'summary')->name('api.expenses.summary');
    Route::get('/expenses/filter','filterByPeriod')->name('api.expenses.filter');
});
});


Route::controller(CategoryController::class)->group(function () {

Route::middleware('auth:sanctum')->group(function () {
    //Route::resource('expenses', CategoryController::class);

    Route::get('/categories','index')->name('api.categories.index');
    Route::post('/categories','store')->name('api.categories.store');
    Route::put('/categories/{id}','update')->name('api.categories.update');
    Route::delete('/categories/{id}','destroy')->name('api.categories.delete');

});
});


Route::controller(GoalController::class)->group(function () {

Route::middleware('auth:sanctum')->group(function () {
    //Route::resource('expenses', GoalController::class);

    Route::get('/goals','index')->name('api.goals.index');
    Route::post('/goals','store')->name('api.goals.store');
    Route::put('/goals/{id}','update')->name('api.goals.update');
    Route::delete('/goals/{id}','destroy')->name('api.goals.delete');

});
});


Route::middleware('auth:sanctum')->controller(CurrencyController::class)->group(function () {

    Route::get('/currency/check-rate', 'checkRate')->name('api.currency.checkRate');
    Route::post('/currency/change', 'changeCurrency')->name('api.currency.changeCurrency');

});


Route::middleware('auth:sanctum')->controller(DashboardController::class)->group(function () {

    Route::get('/dashboard', 'summary')->name('api.dashboard.summary');
    Route::get('/dashboard/expensePercent', 'expensePercent')->name('api.dashboard.expensePercent');
    Route::get('/dashboard/incomePercent', 'incomePercent')->name('api.dashboard.incomePercent');
    Route::post('/dashboard/monthly-limit', 'setMonthlyLimit')->name('api.dashboard.setMonthlyLimit');
    Route::get('/dashboard/checkMonthlyLimit', 'checkMonthlyLimit')->name('api.dashboard.checkMonthlyLimit');

});

