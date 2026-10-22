<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home', ['materials' => Material::with(['category', 'subject', 'user'])->where('status', 'available')->whereHas('user', fn ($q) => $q->where('is_active', true))->latest()->take(6)->get(), 'categories' => Category::all()]))->name('home');
Route::view('/about', 'about');
Route::view('/contact', 'contact');
Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/my-listings', [MaterialController::class, 'mine'])->name('materials.mine');
    Route::get('/materials/create', [MaterialController::class, 'create'])->name('materials.create');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    Route::patch('/materials/{material}/sold', [MaterialController::class, 'sold'])->name('materials.sold');
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/materials/{material}/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:10,1')->name('enquiries.store');
    Route::get('/enquiries', [EnquiryController::class, 'index'])->name('enquiries.sent');
    Route::get('/enquiries/received', [EnquiryController::class, 'index'])->name('enquiries.received');
    Route::patch('/enquiries/{enquiry}', [EnquiryController::class, 'update'])->name('enquiries.update');
    Route::post('/materials/{material}/reports', [ReportController::class, 'store'])->middleware('throttle:10,1')->name('reports.store');
    Route::prefix('admin')->middleware('can:admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::patch('/users/{user}', [AdminController::class, 'toggle']);
        Route::get('/materials', [AdminController::class, 'materials']);
        Route::get('/reports', [AdminController::class, 'reports']);
        Route::patch('/reports/{report}', [AdminController::class, 'report']);
        Route::get('/options/{kind}', [AdminController::class, 'taxonomy']);
        Route::post('/options/{kind}', [AdminController::class, 'save']);
        Route::put('/options/{kind}/{id}', [AdminController::class, 'save']);
        Route::delete('/options/{kind}/{id}', [AdminController::class, 'remove']);
    });
});
Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
Route::get('/materials/{material}',[MaterialController::class, 'show'])->name('materials.show');
