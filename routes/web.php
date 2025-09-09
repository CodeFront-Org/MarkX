<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RfqProcessorController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\UserRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\CompanyFileController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth', 'prefix' => ''], function () {
    Route::get('/', [HomeController::class, 'home']);
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('rtl', function () {
        return view('rtl');
    })->name('rtl');

    // User management routes
    Route::middleware('role:rfq_approver')->group(function () {
        Route::get('user-management', [UserController::class, 'index'])->name('user-management');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::get('tables', function () {
        return view('tables');
    })->name('tables');

    Route::get('virtual-reality', function () {
        return view('virtual-reality');
    })->name('virtual-reality');

    Route::get('static-sign-in', function () {
        return view('static-sign-in');
    })->name('sign-in');

    Route::get('static-sign-up', function () {
        return view('static-sign-up');
    })->name('sign-up');

    Route::get('/logout', [SessionsController::class, 'destroy']);
    Route::get('/user-profile', [InfoUserController::class, 'create']);
    Route::post('/user-profile', [InfoUserController::class, 'store']);

    // Quote routes
    Route::get('/quotes/fetch-customers', [QuoteController::class, 'fetchCustomers'])->name('quotes.fetch-customers');
    Route::get('/quotes/fetch-products', [QuoteController::class, 'fetchProductItems'])->name('quotes.fetch-products');
    Route::resource('quotes', QuoteController::class);
    //Route::post('quotes/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit');
    Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');
    Route::post('quotes/{quote}/submit-to-finance', [QuoteController::class, 'submitToFinance'])->name('quotes.submit-to-finance');
    Route::get('quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    
    // Quote file management routes
    Route::post('quotes/{quote}/attach-file', [QuoteController::class, 'attachFile'])->name('quotes.attach-file');
    Route::get('quotes/{quote}/files/{file}/download', [QuoteController::class, 'downloadFile'])->name('quotes.download-file');
    Route::get('quotes/{quote}/files/{file}/view', [QuoteController::class, 'viewFile'])->name('quotes.view-file');
    Route::delete('quotes/{quote}/files/{file}', [QuoteController::class, 'deleteFile'])->name('quotes.delete-file');
    
    // Quote item management routes
    Route::post('quotes/items/{item}/toggle-approval', [QuoteController::class, 'toggleItemApproval'])->name('quotes.toggle-item-approval');
    Route::post('quotes/{quote}/return-for-editing', [QuoteController::class, 'returnForEditing'])->name('quotes.return-for-editing');

    // Reports route
    Route::get('reports', [ReportsController::class, 'index'])->middleware('role:rfq_approver')->name('reports.index');
    Route::get('reports/user/{user}', [ReportsController::class, 'userReport'])->middleware('role:rfq_approver')->name('reports.user');

    // Company Files routes
    Route::get('company-files', [CompanyFileController::class, 'index'])->name('company-files.index');
    Route::post('company-files', [CompanyFileController::class, 'store'])->middleware(['auth', 'role:rfq_approver'])->name('company-files.store');
    Route::get('company-files/{fileName}/download', [CompanyFileController::class, 'download'])->name('company-files.download');
    Route::delete('company-files/{fileName}', [CompanyFileController::class, 'destroy'])->middleware(['auth', 'role:rfq_approver'])->name('company-files.destroy');

    // Manager Only Routes
    Route::middleware('role:rfq_approver')->group(function () {
        // Export routes
        Route::get('exports/data', [ExportController::class, 'exportData'])->name('exports.data');
        
        // RFQ Processor management routes
        Route::get('rfq-processors/create', [RfqProcessorController::class, 'create'])->name('rfq-processors.create');
        Route::post('rfq-processors', [RfqProcessorController::class, 'store'])->name('rfq-processors.store');

        // User registration routes (for rfq_processor and rfq_approver)
        Route::get('users/{role}/create', [UserRegistrationController::class, 'create'])
            ->where('role', 'rfq_processor|rfq_approver|lpo_admin')
            ->name('users.create');
        Route::post('users/{role}', [UserRegistrationController::class, 'store'])
            ->where('role', 'rfq_processor|rfq_approver|lpo_admin')
            ->name('users.store');
    });

    // Product Items routes
    Route::get('product-items/{itemName}/details', [ProductItemController::class, 'show'])->name('product-items.details')->where('itemName', '.*');
    Route::resource('product-items', ProductItemController::class)->except(['show']);
    
    // Supplier routes
    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{supplier}/products/add', [SupplierController::class, 'addProduct'])->name('suppliers.products.add');
    Route::post('suppliers/{supplier}/products', [SupplierController::class, 'attachProduct'])->name('suppliers.products.attach');
    Route::get('suppliers/{supplier}/products/{product}/edit', [SupplierController::class, 'editProduct'])->name('suppliers.products.edit');
    Route::put('suppliers/{supplier}/products/{product}', [SupplierController::class, 'updateProduct'])->name('suppliers.products.update');
    Route::delete('suppliers/{supplier}/products/{product}', [SupplierController::class, 'detachProduct'])->name('suppliers.products.detach');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    Route::post('/session', [SessionsController::class, 'store']);
    Route::get('/login/forgot-password', [ResetController::class, 'create']);
    Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
    Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});