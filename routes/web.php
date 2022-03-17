<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AdminController;
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


Route::get('/vendors', function () {
    return view('admin.vendor');
});
Route::get('/loyalty', function () {
    return view('admin.loyalty');
});

// Route::get('/faqs', function () {
//     return view('admin.faq');
// });

Route::get('/modal-test', function () {
    return view('admin.modal-test');
});

Route::get('/transaction', function () {
    return view('admin.transaction');
});

Route::get('/gift-cards', function () {
    return view('admin.gift-card');
});

Route::get('/user/verify', [UserController::class, 'verifyUser']);
Route::get('/vendors', [VendorController::class, 'displayVendors']);
Route::post('/add-vendor', [VendorController::class, 'store'])->name('vendor.add');
Route::post('/view-vendor', [VendorController::class, 'show'])->name('view-vendor.add');
Route::post('/edit-vendor', [VendorController::class, 'update'])->name('vendor.update');
Route::post('/remove-vendor', [VendorController::class, 'destroyVendor'])->name('vendor.remove');
Route::post('/disable-vendor', [VendorController::class, 'disableVendor'])->name('vendor.disable');
Route::post('/enable-vendor', [VendorController::class, 'enableVendor'])->name('vendor.enable');
Route::post('/verify-vendor-account.add', [VendorController::class, 'verifyAccount'])->name('verify-vendor-account.add');
Route::get('/ajax_files/transaction_summary', [AdminController::class, 'transactionSummary']);
Route::get('/ajax_files/reward_summary', [AdminController::class, 'rewardSummary']);
Route::get('/home', [AdminController::class, 'index']);
Route::get('/', [AdminController::class, 'index']);
Route::get('/users/list', [AdminController::class, 'transactionTable']);
Route::get('/users/list_reward', [AdminController::class, 'rewardTable']);
Route::get('/vendor/list', [VendorController::class, 'vendorList']);
Route::get('/giftcard/list', [AdminController::class, 'displayGiftCards']);
Route::get('/loyalty', [AdminController::class, 'loyaltySettings']);
Route::post('/updateLoyaltySettings', [AdminController::class, 'updateLoyaltySettings'])->name('loyalty-class.update');
Route::post('/addFaq', [AdminController::class, 'addFaq'])->name('faq.add');
Route::get('/faqs', [AdminController::class, 'getFaq']);
Route::post('/showFaq', [AdminController::class, 'showFaq'])->name('faq.show');
Route::post('/updateFaq', [AdminController::class, 'updateFaq'])->name('faq.update');
Route::post('/deleteFaq', [AdminController::class, 'deleteFaq'])->name('faq.delete');
Route::get('/events-promo', [AdminController::class, 'getNotification']);
Route::post('/addEvents', [AdminController::class, 'addEvents'])->name('event.add');
Route::post('/showEvent', [AdminController::class, 'showEvent'])->name('event.show');
Route::post('/deleteEvent', [AdminController::class, 'deleteEvent'])->name('event.delete');
Route::post('/generateGiftCard', [AdminController::class, 'generateGiftCards'])->name('generate.cards');


// Auth::routes();

Route::get('/custom-home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
