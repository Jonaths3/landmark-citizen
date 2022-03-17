<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PosTransactions;
use App\Models\Reward;
use App\Models\Transactions;
use App\Models\Notification;
use App\Models\CashAccount;
use App\Models\RedeemedAmount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\BankApiController;

class UserController extends Controller
{
    public function register(Request $request)
    {
        
        $citizen_id = Helper::generateNumber(10);
        $user_token = Helper::generateRandomCode();
        $pin = Helper::generateNumber(4);
        $pinHash = Hash::make($pin);
        $passwordHash = Hash::make($request->password);
        $verification_code = sha1(time());
        if (DB::table('users')
            ->where('email', $request->input('email'))
            ->doesntExist()) {
        $BankApiController = new BankApiController;
        $accountInfo = $BankApiController->create_wallet($request->email, $request->firstName, $request->lastName, $request->phone);
        $vnuban = $accountInfo['account_number'];
        $wallet_id = $accountInfo['wallet_id'];

        
        $query = User::create([
            'first_name' => $request->input('firstName'),
            'last_name' => $request->input('lastName'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'citizen_id' => $citizen_id,
            'vnuban' => $vnuban,
            'account_status' => 'Active',
            'account_balance' => '0',
            'user_token' => $user_token,
            'password' => $passwordHash,
            'user_pin' => $pinHash,
            'verification_code' => $verification_code,
            'user_type' => "1",
            "wallet_id" => $wallet_id
        ]);

        if ($query) {
            $name = $query->first_name.' '.$query->last_name;
            MailController::sendSignupEmail($query->first_name, $query->email, $query->verification_code, $pin);
            $response = array(
                "responseCode" => "00",
                "userToken" => $user_token
            );
            echo json_encode($response);
        }
        else {
            $response = array(
                "responseCode" => "11",
                "message" => 'Registration Failed'
            );
            echo json_encode($response);
        }

       
    }
        else {
            $response = array(
                'response' => "User Already Exists!"
            );
            echo json_encode($response);
        }
    }

    public function verifyUser(Request $request){
        if ($request->filled('id')) {
            $verification_code = $request->id;
            $user = User::where(['verification_code' => $verification_code])->first();
            if($user != null){
                $user->is_verified = 1;
                $user->save();
                return '<html><body><h2>Congratulations! Your account has been verified.</h2></body></html>';
            }

            return 'Invalid Account!';
        }
        else{
            return 'Invalid Url';
        }
    }


    public function resendVerificationEmail(Request $request)
    {
        $pin = Helper::generateNumber(4);
        $pinHash = Hash::make($pin);

        $user = DB::table('users')
            ->where('user_token', $request->userToken)
            ->first();
        $changePin = DB::table('users')
            ->where('user_token', $request->userToken)
            ->update([
                'user_pin' => $pinHash
                ]);
            
        MailController::sendSignupEmail($user->first_name, $user->email, $user->verification_code, $pin);
            $response = array(
                'responseCode' => "00",
                'message' => "Email verification sent"
            );
            echo json_encode($response);
    }

    public function resetPin(Request $request)
    {
        $pin = Helper::generateNumber(4);
        $user = Auth::user();
        $pinHash = Hash::make($pin);
        $password = $request->password;
        if (Hash::check($password, Auth::user()->password)) {
            $changePin = DB::table('users')
                        ->where('citizen_id', $user->citizen_id)
                        ->update([
                            'user_pin' => $pinHash
                            ]);
            MailController::sendPinEmail($user->first_name, $user->email, $pin);
            $response = array(
                'responseCode' => "00",
                'message' => "PIN has been reset successfully"
            );
            echo json_encode($response);
        }
        else {
            return [
                'message' => 'Invalid Password!'
            ];
        }
    }

    public function resetPassword(Request $request)
    {
        $password = Helper::generateTranxNumber(6);
        $passwordHash = Hash::make($password);
        $email = $request->email;
        if ( DB::table('users')->where('email', $email)->exists()) {
            $changePassword = DB::table('users')
                        ->where('email', $email)
                        ->update([
                            'password' => $passwordHash
                            ]);
            $query = DB::table('users')
            ->where('email', $email)
            ->get();
            $name = $query[0]->first_name.' '.$query[0]->last_name;
            MailController::sendPasswordEmail($name, $request->email, $password);
            $response = array(
                'responseCode' => "00",
                'message' => "Password has been reset successfully"
            );
            echo json_encode($response);
        }
        else {
            return [
                'responseCode' => '11',
                'message' => 'Email does not exist.'
            ];
        }
    }

    
    public function changePin(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $pin = $request->oldPin;
        $newPinHash = Hash::make($request->newPin);
        $getPin = DB::table('users')
            ->where('citizen_id', $citizen_id)
            ->get();
        
        if (Hash::check($pin, $getPin[0]->user_pin))
            {
                $newPinHash = Hash::make($request->newPin);
                $changePin = DB::table('users')
                    ->where('citizen_id', $citizen_id)
                    ->update([
                        'user_pin' => $newPinHash
                        ]);
                if ($changePin) {
                    return [
                        'message' => 'successful'
                    ];
                }
                else {
                    return [
                        'message' => 'failed'
                    ];
                }
            }
            else {
                return [
                    'message' => 'Invalid Old Pin'
                ];
            }
    }


    
// Paystack bank info
    public function getUserBankInfo()
    {
        $citizen_id = Auth::user()->citizen_id;
        // Get user Inflows
        $user = DB::table('users')
            ->where('citizen_id', $citizen_id)
            ->first();
        $wallet_id = $user->wallet_id;
        $phone = $user->phone;
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $BankApiController = new BankApiController;
        // Get Wallet Balance
        $wallet_details = $BankApiController->wallet_details($wallet_id);
        $response = array(
            'accountName' =>strval($citizen_id),
            'accountNumber' => $wallet_details['virtual_account_number'],
            'bankName' => $wallet_details['virtual_bank_name'],
            'availableBalance' => strval($wallet_details['balance'])
        );
        return $response;
    }

    public function userEarnings()
    {
        $citizen_id = Auth::user()->citizen_id;
        // total amount of money rewarded
        $reward = DB::table('rewards')
            ->where('citizen_id', $citizen_id)
            ->sum('cashback_earned');

        // total amount of money redeemed
        $redeemed = DB::table('redeemed_amounts')
            ->where('citizen_id', $citizen_id)
            ->sum('redeemed_amount');
        // Calculating money earned
        $earnings = $reward - $redeemed;
        $response = array(
            "responseCode" => "00",
            "amountEarned" => strval($earnings)
        );
        return json_encode($response);
    }

    //Paystack implementation
    public function redeemGemPoints(Request $request)
    {
        $amountEarned = UserController::userEarnings();
        $amountDecode = json_decode($amountEarned, TRUE);
        $amount = $amountDecode['amountEarned'];
        $transaction_id = Helper::generateTranxNumber(10);
        $citizen_id = Auth::user()->citizen_id;
        $pin = $request->pin;
        if (Hash::check($pin, Auth::user()->user_pin)) {
            if ($amount == 0) {
                $response = array(
                    'response' => "11",
                    'message' => "You have not earned any cashback for redemption"
                );
                return json_encode($response);
                exit();
            }
            // Sending money from Landmark Reward bank to user wallet
            $BankApiController = new BankApiController;
            $fixedAccount = $BankApiController->fixedAccountsPaystack();
            $landmark_reward_wallet_id = $fixedAccount['landmark_reward_wallet_id'];
            $to_wallet_id = Auth::user()->wallet_id;
            $amount_sent = round($amount,2);
            $fund_user_wallet = $BankApiController->wallet_payment($amount_sent, $landmark_reward_wallet_id, $to_wallet_id);
            if ($fund_user_wallet['status'] != 'success') {
                $response = array(
                    'response' => "11",
                    'message' => "We could not redeem your point please try again later."
                );
                return json_encode($response);
                exit();
            }
            // Update transaction table
            $transaction = Transactions::create([
                'transaction_id' => $transaction_id,
                'from' => 'Redeemed Amount',
                'to' => $citizen_id,
                'transaction_mode' => 'Redeemed Cashback',
                'narration' => $transaction_id.'_redeemed_cashback',
                'amount' => $amount
            ]);

            
        // Update cash_accounts table
        $cashAccount = CashAccount::create([
                'transaction_id' => $transaction_id,
                'account_id' => $citizen_id,
                'transaction_type' => 'Credit',
                'amount' => $amount
            ]);

            // Update redeemed amounts table
            $redeem = RedeemedAmount::create([
                'tranx_id' => $transaction_id,
                'citizen_id' => $citizen_id,
                'redeemed_amount' => $amount
            ]);

            $response = array(
                'response' => "00",
                'message' => "Your wallet has been funded with the redeemed amount"
            );
            return json_encode($response);
        }
        else {
            $response = array(
                'response' => "11",
                'message' => "Invalid Pin"
            );
            return json_encode($response);
        }
    }

    public function accruedGemPoints()
    {
        $citizen_id = Auth::user()->citizen_id;
        // total amount of points awarded
        $reward = DB::table('rewards')
            ->where('citizen_id', $citizen_id)
            ->sum('point_earned');

            $response = array(
                'response' => "00",
                'loyaltyPoint' => $reward
            );
            return json_encode($response);
    }

    public function lockAccount(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $pin = $request->pin;
        if (Hash::check($pin, Auth::user()->user_pin)) {
            $lockAccount = DB::table('users')
                ->where('citizen_id', $citizen_id)
                ->update([
                    'account_status' => 'Locked'
                    ]);
            
            if ($lockAccount) {
                return [
                    'response' => 'success',
                    'message' => 'Account is locked'
                ];
            }
            else {
                return [
                    'message' => 'failed'
                ];
            }
        }
        else{
            return [
                'message' => 'Invalid Pin!'
            ];
        }
        
    }

    public function unLockAccount(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $pin = $request->pin;
        if (Hash::check($pin, Auth::user()->user_pin)) {
            $unlockAccount = DB::table('users')
                ->where('citizen_id', $citizen_id)
                ->update([
                    'account_status' => 'Active'
                    ]);
            
            if ($unlockAccount) {
                return [
                    'response' => 'success',
                    'message' => 'Account is unlocked'
                ];
            }
            else {
                return [
                    'message' => 'failed'
                ];
            }
        }
        else{
            return [
                'message' => 'Invalid Pin!'
            ];
        }
    }

    public function changePassword(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $oldPassword = $request->oldPassword;
        $newPasswordHash = Hash::make($request->newPassword);
        $getPassword = DB::table('users')
            ->where('citizen_id', $citizen_id)
            ->get();
        if (Hash::check($oldPassword, $getPassword[0]->password)) {
            $changePassword = DB::table('users')
                ->where('citizen_id', $citizen_id)
                ->update([
                    'password' => $newPasswordHash
                    ]);
            
            if ($changePassword) {
                return [
                    'message' => 'successful'
                ];
            }
            else {
                return [
                    'message' => 'failed'
                ];
            }
        }
        else {
            return [
                'message' => 'Invalid Old Password'
            ];
        }

    }

    public function getFaq()
    {
        $faq = DB::table('faqs')->get();
        return json_encode($faq);
    }

    public function loyaltyGroup()
    {
        $query = DB::table('loyalty_classes')
            ->get();
        foreach ($query as  $value) {
            $response[] = array(
                'loyalty_class' => $value->loyalty_class,
                'base_point' => $value->min_point
            );
        }
        return $response;
    }

    public function getNotification()
    {
        $notification = DB::table('notifications')->get();
        return json_encode($notification);
    }

    public function storeNotification(Request $request)
    {
        // Uploading image
        $img_url = 'NULL';
        if($request->file()) {
            $request->validate([
                'image' => 'required|mimes:jpeg,jpg|max:1024'
                ]);

            $img_url = 'https://www.landmarkafrica.com/ldc/storage/app/'.$request->file('image')->store('public/notification_images');
        }
        $query = Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'image' => $img_url,
            'vendor_name' => $request->vendor_name,
        ]);

        return $query;
    }

    public function notificationDetails(Request $request)
    {
        $notificationDetails = DB::table('notifications')
            ->where('id', $request->id)
            ->get();
        return json_encode($notificationDetails);
    }

    public function updateUser(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        //  get user picture
        $Query = DB::table('users')
         ->where('citizen_id', $citizen_id)
         ->get();

         $img_url = $Query[0]->picture;
            // Uploading cashier image
            if($request->file()) {
                $request->validate([
                    'picture' => 'required|mimes:png,jpeg,jpg|max:1024'
                    ]);

                $img_url = 'https://www.landmarkafrica.com/ldc/storage/app/'.$request->file('picture')->store('public/user_pictures');
            }
         $userQuery = DB::table('users')
         ->where('citizen_id', $citizen_id)
         ->update([
                'first_name' => $request->fname,
                'last_name' => $request->lname,
                'phone' => $request->phone,
                'updated_at' => Carbon::now()->toDateTimeString()
                
            ]);

            if($userQuery) {
                $response = array(
                    'response' => "00",
                    'message' => "User update successful",
                    'img' => $img_url,
                    'fname' => $request->fname,
                    'lname' => $request->lname,
                    'phone' => $request->phone
                );
                echo json_encode($response);
            }
            else {
                $response = array(
                    'response' => "11",
                    'message' => "User update failed"
                );
                echo json_encode($response);
            }
    }

    public function verifyCheckoutUser(Request $request)
    {
        if (DB::table('users')
        ->where('citizen_id', $request->citizen_id)
        ->orWhere('phone', $request->citizen_id)
        ->exists()) {
            $query = DB::table('users')
                    ->where('citizen_id', $request->citizen_id)
                    ->orWhere('phone', $request->citizen_id)
                    ->get();
            $response = array(
                        'response' => "success",
                        'citizen_id' => $query[0]->citizen_id,
                        'citizen_name' => $query[0]->first_name.' '.$query[0]->last_name
                    );
            return $response;
        }
        else {
            $response = array(
                        'response' => "failed"
                    );
            return $response;
        }
    }
}
