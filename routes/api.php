<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\BankApiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SparkleWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/register_test', [UserController::class, 'registerTest']);
Route::post('/user/login', [AuthController::class, 'login']);
Route::post('/user/resendVerifyEmail', [UserController::class, 'resendVerificationEmail']);
Route::get('/faq', [UserController::class, 'getFaq']);
Route::post('/vendor/new', [VendorController::class, 'store']);
Route::post('/cashier/login', [CashierController::class, 'login']);
Route::post('/user/createWallet', [UserController::class, 'createWallet']);
Route::post('/user/makePayment', [UserController::class, 'makePayment']);
Route::post('/transaction/fund_wallet', [TransactionController::class, 'fundWallet']);
Route::post('/checkout/pay', [BankApiController::class, 'generatePaymentInfo']);
Route::post('/updateTransactionsPayment', [BankApiController::class, 'updateTransactionPayents']);
Route::post('/verifyPayment', [BankApiController::class, 'verifyPayment']);
Route::post('user/verifyPayment', [TransactionController::class, 'verifyPayment']);
Route::post('/vendor/login', [VendorController::class, 'login']);
Route::post('/vendor/resetPassword', [VendorController::class, 'resetPassword']);
Route::post('/user/resetPassword', [UserController::class, 'resetPassword']);
Route::get('/user/fetchBank', [VendorController::class, 'fetchBank']);
Route::get('/user/transactionTable', [AdminController::class, 'transactionTable']);
Route::post('/user/storeRewardTips', [AdminController::class, 'storeRewardTips']);
Route::get('/getFixedAccounts', [TransactionController::class, 'getFixedAccounts']);
Route::get('/privacyPolicy', [AdminController::class, 'privacyPolicy']);
Route::post('/verifyUserBankAccount', [VendorController::class, 'verifyAccount']);
Route::get('/fetchUserBank', [VendorController::class, 'fetchUserBank']);
Route::post('/user/verifyUser', [UserController::class, 'verifyCheckoutUser']);
Route::get('/cashier/getOnlinePaymentInfo', [CashierController::class, 'getOnlinePaymentInfo']);
Route::post('/user/processOnlinePayment', [TransactionController::class, 'processOnlinePayment']);

// Test paystack payments
Route::post('/user/createWallet', [BankApiController::class, 'paystackCreateWallet']);
Route::post('/user/generateAccountInfo', [BankApiController::class, 'generateAccountInfo']);
Route::get('/user/walletDetails', [BankApiController::class, 'walletBalanceTest']);
Route::post('/user/debitWallet', [BankApiController::class, 'debitWallet']);
// Sparkle name enquiry
Route::post('/bank/payOut', [BankApiController::class, 'sparklePayout']);

Route::middleware('auth:sanctum')->group(function () {
    // Cashier apis
    Route::post('/cashier/new', [VendorController::class, 'createCashier']);
    Route::post('/cashier/pin_login', [CashierController::class, 'cashierLogin']);
    Route::get('/vendor/getCashiers', [CashierController::class, 'getCashiers']);
    Route::post('/cashier/logout', [CashierController::class, 'logout']);
    Route::get('/cashier/getPaymentInfo', [CashierController::class, 'getPaymentInfo']);
    Route::get('/cashier/getTransactionHistory', [VendorController::class, 'getCashierPosTransactions']);
    Route::get('/vendor-admin/getTransactionHistory', [VendorController::class, 'getVendorPosTransactions']);
    Route::post('/vendor/create_cashier', [VendorController::class, 'createCashier']);
    Route::post('/vendor/edit_cashier', [VendorController::class, 'editCashier']);
    Route::post('/vendor/disable_cashier', [VendorController::class, 'disableCashier']);
    Route::post('/vendor/enable_cashier', [VendorController::class, 'enableCashier']);
    Route::get('/vendor/account_info', [VendorController::class, 'vendorAccountInfo']);
    Route::post('/vendor/processPayOut', [VendorController::class, 'payOut']);
    Route::post('/vendor/changePassword', [VendorController::class, 'changePassword']);
    Route::post('/vendor/verifyPayment', [VendorController::class, 'verifyPayment']);
    Route::post('/vendor/generateCashoutPin', [VendorController::class, 'generateCashoutOtp']);
    Route::post('/cashier/changePin', [CashierController::class, 'changePin']);
    Route::post('/cashier/resetPin', [CashierController::class, 'resetPin']);
    Route::get('/cashier/dayBalance', [CashierController::class, 'cashierBalance']);
    Route::get('/vendor/vendorBalance', [VendorController::class, 'vendorBalance']);
    Route::post('/cashier/processGiftCardPayment', [VendorController::class, 'processGiftCardPayment']);
    Route::get('/cashier/getGiftCardBalance', [VendorController::class, 'getGiftCardBalance']);
    Route::get('/cashier/getGiftCardPaymentInfo', [CashierController::class, 'getGiftCardPaymentInfo']);

    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::post('/transaction/sendMoney', [TransactionController::class, 'sendMoney']);
    Route::get('/transaction/getInflows', [TransactionController::class, 'getInflows']);
    Route::get('/transaction/getOutflows', [TransactionController::class, 'getOutflows']);
    Route::get('/transaction/getAllTransactions', [TransactionController::class, 'getAllTransactions']);
    Route::post('/transaction/processPayment', [TransactionController::class, 'processQrCodePayment']);
    Route::post('/user/changePin', [UserController::class, 'changePin']);
    Route::get('/user/getAccountBalance', [UserController::class, 'getUserBankInfo']);
    Route::get('/user/showLoyaltyPoint', [UserController::class, 'accruedGemPoints']);
    Route::post('/user/lockAccount', [UserController::class, 'lockAccount']);
    Route::post('/user/unLockAccount', [UserController::class, 'unLockAccount']);
    Route::post('/user/changePassword', [UserController::class, 'changePassword']);
    Route::post('/user/resetPin', [UserController::class, 'resetPin']);
    Route::get('/user/getBankInfo', [UserController::class, 'getUserBankInfo']);
    Route::get('/user/getUserPointEarned', [UserController::class, 'userEarnings']);
    Route::get('/user/loyaltyGroup', [UserController::class, 'loyaltyGroup']);
    Route::post('/user/redeemGemPoints', [UserController::class, 'redeemGemPoints']);
    Route::get('/user/getRecentRecipients', [TransactionController::class, 'getRecentRecipients']);
    Route::get('/transaction/getRecipientInfo', [TransactionController::class, 'getRecipientInfo']);
    Route::get('/transaction/getTransactionDetails', [TransactionController::class, 'showTransactionDetails']);
    Route::get('/transaction/paginate', [TransactionController::class, 'paginate']);
    Route::get('/notification', [UserController::class, 'getNotification']);
    Route::post('/addNotification', [UserController::class, 'storeNotification']);
    Route::get('/notificationDetails', [UserController::class, 'notificationDetails']);
    Route::get('/user/getRewardTips', [AdminController::class, 'getRewardTips']);
    Route::post('/user/updateUser', [UserController::class, 'updateUser']);
    Route::get('/user/fundWalletNotes', [TransactionController::class, 'fundWalletNotes']);
    Route::get('/bank_code/show', [BankApiController::class, 'get_bank_code']);
});




Route::post('sparkle-webhook',[SparkleWebhookController::class,'sparkleWebhook']);
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


