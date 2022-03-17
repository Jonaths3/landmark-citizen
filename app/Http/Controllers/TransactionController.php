<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\CashAccount;
use App\Models\User;
use App\Models\PosTransactions;
use App\Models\TransactionPayments;
use App\Models\Reward;
use App\Helper\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankApiController;
use Illuminate\Support\Facades\Http;
use App\Events\NewPayment;

class TransactionController extends Controller
{
    public function fundWallet(Request $request)
    {
        $event = $request->event;
       if ($event == 'wallet_funding') {
            // Get citizen ID
        $user = DB::table('users')
        ->where('wallet_id', $request->wallet_id)
        ->first();    

        $transaction_id = Helper::generateTranxNumber(10);

        // Update transaction table
        $transaction = Transactions::create([
            'transaction_id' => $transaction_id,
            'from' => 'Online top-up',
            'to' => $user->citizen_id,
            'transaction_mode' => 'Funded Wallet',
            'narration' => $transaction_id.'_Purchase_of_token',
            'amount' => $request->amount
        ]);

       // Update cash_accounts table
       $cashAccount = CashAccount::create([
            'transaction_id' => $transaction_id,
            'account_id' => $user->citizen_id,
            'transaction_type' => 'Credit',
            'amount' => $request->amount
        ]);

        if ($transaction && $cashAccount) {
                http_response_code(200);
        }
       }

    }

    public function processQrCodePayment()
    {
        // Getting user Account Balance
        $userController = new UserController;
        $response = $userController->getUserBankInfo();
        $userBalance = $response['availableBalance'];

        if($json = json_decode(file_get_contents("php://input"), true)) {
            $data = $json;
        }
        $pin = $data['pin'];
        $transaction_id = $data['tranxId'];
        $senderAccount = Auth::user()->vnuban;
        $citizen_id = Auth::user()->citizen_id;
        $user_wallet_id = Auth::user()->wallet_id;
        $vendor_wallet_id = $data['vendorAccountNumber'];
        $receiver_citizen_id = $data['vendorId'];
        $cashierId = $data['cashierId'];
        $amount = $data['amount'];
        $senderName = Auth::user()->first_name.' '.Auth::user()->last_name;
        
        // Get vendors loyalty discount and sales rent percentages
        $vendor = DB::table('vendors')
            ->where('vendor_id', $receiver_citizen_id)
            ->first();
        
        $vendor_loyalty_discount_percent = $vendor->loyalty_discount / 100;
        $vendor_sales_rent_percent = $vendor->sales_rent / 100;

        // Get users citizen class and cashback percentage
        $user_type = Auth::user()->user_type;
        $loyalty = DB::table('loyalty_classes')
            ->where('id', $user_type)
            ->get();
        //$eligible_discount = $loyalty[0]->percentage_discount / 100;
        $eligible_cashback = $loyalty[0]->percentage_cashback / 100;
        $eligible_point = $loyalty[0]->percentage_points / 100;

        $maintenanceFee = $amount * (0.25 / 100);
        if ($maintenanceFee >= 2000) {
            $processingFee = 2000;
        }
        else {
            $processingFee = round($maintenanceFee,2);
        }
        
        $landmark_reward_bank_amount = round(($amount * $vendor_loyalty_discount_percent),2);
        $vendor_tranx_amount = round(($amount - $landmark_reward_bank_amount),2);
        $landmark_sales_rent_amount = round(($vendor_tranx_amount * $vendor_sales_rent_percent),2);

        $vendor_amount = round(($amount - $landmark_sales_rent_amount - $landmark_reward_bank_amount),2);
        $vendor_earned_amount = round($vendor_amount - $processingFee);


        $wallet_vendor_earned_amount = $vendor_earned_amount;
        $wallet_landmark_sales_rent_amount = $landmark_sales_rent_amount;
        $wallet_landmark_reward_bank_amount = $landmark_reward_bank_amount;
        $wallet_processing_fee_amount = $processingFee;

        $BankApiController = new BankApiController;
        $fixedAccount = $BankApiController->fixedAccountsPaystack();
        // Calculate users points
        $loyalty_point_earned = round($amount * $eligible_point);
        // Calculating Cashback
        // getting total spend by user
        $total_spend = DB::table('pos_transactions')
            ->where('customer_id', $citizen_id)
            ->sum('tranx_value');

        $cashback_earned = ($user_type == 1 && $total_spend < $loyalty[0]->min_point) ? 0 : $vendor_tranx_amount * $eligible_cashback;
        // Payment information for the landmark reward bank
        $landmark_reward_wallet_id = $fixedAccount['landmark_reward_wallet_id'];

         // Payment information for the landmark sales rent
        $landmark_sales_rent_wallet_id = $fixedAccount['landmark_sales_rent_wallet_id'];
        
        // Payment information for the landmark transactions account
        $landmark_transaction_fee_wallet_id = $fixedAccount['landmark_transaction_fee_account'];

        
        if (Hash::check($pin, Auth::user()->user_pin)) {


            if (Auth::user()->account_status == 'Locked') {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Account is Locked. Please unlock this account to make transactions."
                    ));
                    exit();
            }

            // Check if user has amount
            if ($userBalance < $amount) {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Insufficient Balance"
                    ));
                    exit();
            }

            // check if payment has been made for this transaction
            if (DB::table('pos_transactions')
            ->where('transaction_id', $transaction_id)
            ->where('status', 'Paid')
            ->exists()
            ) {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Payment has already been made for this transaction."
                    ));
                    exit();
            }

            // check if vendor has initiated payment for this transaction
            if (DB::table('pos_transactions')
            ->where('transaction_id', $transaction_id)
            ->doesntExist()
            ) {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "This transaction is not initiated by a vendor."
                    ));
                    exit();
            }
            $pay_vendor = $BankApiController->wallet_payment($wallet_vendor_earned_amount, $user_wallet_id, $vendor_wallet_id);
            $pay_landmark_reward_bank = $BankApiController->wallet_payment($wallet_landmark_reward_bank_amount, $user_wallet_id, $landmark_reward_wallet_id);
            $pay_sales_rent = $BankApiController->wallet_payment($wallet_landmark_sales_rent_amount, $user_wallet_id, $landmark_sales_rent_wallet_id);
            $pay_landmark_transaction_fee = $BankApiController->wallet_payment($wallet_processing_fee_amount, $user_wallet_id, $landmark_transaction_fee_wallet_id);

            //$pay_processing_fee = '';
            if($pay_vendor['status'] == 'success') {
            // update user transaction
                $transaction = Transactions::create([
                    'transaction_id' => $transaction_id,
                    'from' => $citizen_id,
                    'to' => $receiver_citizen_id,
                    'transaction_mode' => 'Payment',
                    'narration' => 'Merchant Payment',
                    'amount' => $amount
                ]);

                // update vendor pos transaction
                
                $postransaction = DB::table('pos_transactions')
                ->where('transaction_id', $transaction_id)
                ->update([
                    'amount' => $vendor_earned_amount,
                    'status' => 'Paid',
                    'customer_id' => $citizen_id
                ]);
                // Update cash_accounts table for user
            $cashAccount = CashAccount::create([
                    'transaction_id' => $transaction_id,
                    'account_id' => $citizen_id,
                    'transaction_type' => 'Debit',
                    'amount' => $amount
                ]);

                // Update user's reward table
                $reward = Reward::create([
                    'citizen_id' => $citizen_id,
                    'tranx_id' => $transaction_id,
                    'point_earned' => $loyalty_point_earned,
                    'amount_spent' => $amount,
                    'cashback_earned' => $cashback_earned
                ]);

                // Update the transactions payments table
                $transactions_payments = TransactionPayments::create([
                    'vendor_id' => $receiver_citizen_id,
                    'vnuban' => $vendor_wallet_id,
                    'tranx_ref' => $transaction_id,
                    'payment_ref' => $transaction_id, //Live server
                    //'payment_ref' => $pay_vendor['data']['payout_reference'], //test server
                    'cust_email' => Auth::user()->email,
                    'merchant_amount' => $vendor_earned_amount,
                    'fee' => $processingFee,
                    'amount_payable' => $amount,
                    'amount_paid' => $amount,
                    'status' => 'Paid',
                    'sales_rent' => $landmark_sales_rent_amount
                ]);
                // Get user total spend
                $user_total_spend = DB::table('pos_transactions')
                    ->where('customer_id', $citizen_id)
                    ->sum('tranx_value');

                // Get reward classes
                $rewards = DB::table('loyalty_classes')
                    ->get();
                if ($user_total_spend >= $rewards[1]->min_point && $user_total_spend < $rewards[2]->min_point) {
                    $user_loyalty_type = $rewards[1]->id;
                }
                elseif ($user_total_spend >= $rewards[2]->min_point) {
                    $user_loyalty_type = $rewards[2]->id;
                }
                else {
                    $user_loyalty_type = $rewards[0]->id;
                }

                // Updating user loyalty type
                $update_user = DB::table('users')
                ->where('citizen_id', $citizen_id)
                ->update([
                    'user_type' => $user_loyalty_type
                ]);

        
            
                if ($transaction && $cashAccount && $postransaction && $reward && $transactions_payments) {
                    
                    $response = array(
                            "responseCode" => "00",
                            "status" => "success",
                            "message" => "Payment successful",
                            "cashback_earned" => strval($reward->cashback_earned),
                            "tranx_id" => $reward->tranx_id,
                            "points_earned" => strval($reward->point_earned),
                            "amount_paid" => strval($reward->amount_spent),
                            "store_name" => $data['storeName'],
                            "payment_reference" => $transaction_id
                            //"payment_reference" => $pay_vendor['data']['payout_reference']
                        );
                        // Sending email to customer
                        MailController::sendPaymentNotification($senderName, $senderAccount, Auth::user()->email, $amount, $data['storeName'], $receiver_citizen_id, $transaction_id);
                        return json_encode($response);
                        
                }
                else {
                    return json_encode(
                        array(
                            "responseCode" => "11",
                            "message" => "Transaction Failed"
                        ));
                }
            }
            else {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Payment Failed",
                        "wallet_vendor_earned_amount" => $wallet_vendor_earned_amount,
                        "wallet_landmark_sales_rent_amount" => $wallet_landmark_sales_rent_amount,
                        "wallet_landmark_reward_bank_amount" => $wallet_landmark_reward_bank_amount,
                        "amount" => $amount,
                        "response" => $pay_vendor
                    ));
            }
        }
        else {
            return json_encode(
                array(
                    "responseCode" => "11",
                    "message" => "Invalid Authentication Pin"
                ));
        }
    }

    // Process online payment
     public function processOnlinePayment(Request $request)
     {
         if($json = json_decode(file_get_contents("php://input"), true)) {
             $data = $json;
         }
         $citizen_id = $data['citizenId'];
         $citizen = DB::table('users')
             ->where('citizen_id', $citizen_id)
             ->first();
        $wallet_id = $citizen->wallet_id;
         // Getting user Account Balance
         
         $BankApiController = new BankApiController;
         // Get Wallet Balance
         $wallet_details = $BankApiController->wallet_details($wallet_id);
         $userBalance = $wallet_details['balance'];
         
         $pin = $data['pin'];
         $transaction_id = $data['tranxId'];
         $senderAccount = $citizen->vnuban;
        
         $user_wallet_id = $citizen->wallet_id;
         $vendor_wallet_id = $data['vendorAccountNumber'];
         $receiver_citizen_id = $data['vendorId'];
         $cashierId = $data['cashierId'];
         $amount = $data['amount'];
         $senderName = $citizen->first_name.' '.$citizen->last_name;
         
         // Get vendors loyalty discount and sales rent percentages
         $vendor = DB::table('vendors')
             ->where('vendor_id', $receiver_citizen_id)
             ->first();
         
         $vendor_loyalty_discount_percent = $vendor->loyalty_discount / 100;
         $vendor_sales_rent_percent = $vendor->sales_rent / 100;
 
         // Get users citizen class and cashback percentage
         $user_type = $citizen->user_type;
         $loyalty = DB::table('loyalty_classes')
             ->where('id', $user_type)
             ->get();
         //$eligible_discount = $loyalty[0]->percentage_discount / 100;
         $eligible_cashback = $loyalty[0]->percentage_cashback / 100;
         $eligible_point = $loyalty[0]->percentage_points / 100;
 
         $maintenanceFee = $amount * (0.25 / 100);
        if ($maintenanceFee >= 2000) {
            $processingFee = 2000;
        }
        else {
            $processingFee = round($maintenanceFee,2);
        }
         $landmark_reward_bank_amount = round(($amount * $vendor_loyalty_discount_percent),2);
         $vendor_tranx_amount = round(($amount - $landmark_reward_bank_amount),2);
         $landmark_sales_rent_amount = round(($vendor_tranx_amount * $vendor_sales_rent_percent),2);


 
         $vendor_amount = round(($amount - $landmark_sales_rent_amount - $landmark_reward_bank_amount),2);
         $vendor_earned_amount = round($vendor_amount - $processingFee);
 
 
         $wallet_vendor_earned_amount = $vendor_earned_amount;
         $wallet_landmark_sales_rent_amount = $landmark_sales_rent_amount;
         $wallet_landmark_reward_bank_amount = $landmark_reward_bank_amount;
         $wallet_processing_fee_amount = $processingFee;

 
         $BankApiController = new BankApiController;
         $fixedAccount = $BankApiController->fixedAccountsPaystack();
         // Calculate users points
         $loyalty_point_earned = round($amount * $eligible_point);
         // Calculating Cashback
         // getting total spend by user
         $total_spend = DB::table('pos_transactions')
             ->where('customer_id', $citizen_id)
             ->sum('tranx_value');
 
         $cashback_earned = ($user_type == 1 && $total_spend < $loyalty[0]->min_point) ? 0 : $vendor_tranx_amount * $eligible_cashback;
         // Payment information for the landmark reward bank
         $landmark_reward_wallet_id = $fixedAccount['landmark_reward_wallet_id'];
 
          // Payment information for the landmark sales rent
         $landmark_sales_rent_wallet_id = $fixedAccount['landmark_sales_rent_wallet_id'];

           // Payment information for the landmark transactions account
         $landmark_transaction_fee_wallet_id = $fixedAccount['landmark_transaction_fee_account'];

         
         
         if (Hash::check($pin, $citizen->user_pin)) {
 
 
             if ($citizen->account_status == 'Locked') {
                 return json_encode(
                     array(
                         "responseCode" => "11",
                         "message" => "Account is Locked. Please unlock this account to make transactions."
                     ));
                     exit();
             }
 
             // Check if user has amount
             if ($userBalance < $amount) {
                 return json_encode(
                     array(
                         "responseCode" => "11",
                         "message" => "Insufficient Balance"
                     ));
                     exit();
             }
 
             // check if payment has been made for this transaction
             if (DB::table('pos_transactions')
             ->where('transaction_id', $transaction_id)
             ->where('status', 'Paid')
             ->exists()
             ) {
                 return json_encode(
                     array(
                         "responseCode" => "11",
                         "message" => "Payment has already been made for this transaction."
                     ));
                     exit();
             }
 
             // check if vendor has initiated payment for this transaction
             if (DB::table('pos_transactions')
             ->where('transaction_id', $transaction_id)
             ->doesntExist()
             ) {
                 return json_encode(
                     array(
                         "responseCode" => "11",
                         "message" => "This transaction is not initiated by a vendor."
                     ));
                     exit();
             }
             $pay_vendor = $BankApiController->wallet_payment($wallet_vendor_earned_amount, $user_wallet_id, $vendor_wallet_id);
             $pay_landmark_reward_bank = $BankApiController->wallet_payment($wallet_landmark_reward_bank_amount, $user_wallet_id, $landmark_reward_wallet_id);
             $pay_sales_rent = $BankApiController->wallet_payment($wallet_landmark_sales_rent_amount, $user_wallet_id, $landmark_sales_rent_wallet_id);
             $pay_landmark_transaction_fee = $BankApiController->wallet_payment($wallet_processing_fee_amount, $user_wallet_id, $landmark_transaction_fee_wallet_id);

 
             //$pay_processing_fee = '';
             if($pay_vendor['status'] == 'success') {
             // update user transaction
                 $transaction = Transactions::create([
                     'transaction_id' => $transaction_id,
                     'from' => $citizen_id,
                     'to' => $receiver_citizen_id,
                     'transaction_mode' => 'Payment',
                     'narration' => 'Merchant Payment',
                     'amount' => $amount
                 ]);
 
                 // update vendor pos transaction
                 
                 $postransaction = DB::table('pos_transactions')
                 ->where('transaction_id', $transaction_id)
                 ->update([
                     'amount' => $vendor_earned_amount,
                     'status' => 'Paid',
                     'customer_id' => $citizen_id
                 ]);
                 // Update cash_accounts table for user
             $cashAccount = CashAccount::create([
                     'transaction_id' => $transaction_id,
                     'account_id' => $citizen_id,
                     'transaction_type' => 'Debit',
                     'amount' => $amount
                 ]);
 
                 // Update user's reward table
                 $reward = Reward::create([
                     'citizen_id' => $citizen_id,
                     'tranx_id' => $transaction_id,
                     'point_earned' => $loyalty_point_earned,
                     'amount_spent' => $amount,
                     'cashback_earned' => $cashback_earned
                 ]);
 
                 // Update the transactions payments table
                 $transactions_payments = TransactionPayments::create([
                     'vendor_id' => $receiver_citizen_id,
                     'vnuban' => $vendor_wallet_id,
                     'tranx_ref' => $transaction_id,
                     'payment_ref' => $transaction_id, //Live server
                     //'payment_ref' => $pay_vendor['data']['payout_reference'], //test server
                     'cust_email' => $citizen->email,
                     'merchant_amount' => $vendor_earned_amount,
                     'fee' => $processingFee,
                     'amount_payable' => $amount,
                     'amount_paid' => $amount,
                     'status' => 'Paid',
                     'sales_rent' => $landmark_sales_rent_amount
                 ]);
                 // Get user total spend
                 $user_total_spend = DB::table('pos_transactions')
                     ->where('customer_id', $citizen_id)
                     ->sum('tranx_value');
 
                 // Get reward classes
                 $rewards = DB::table('loyalty_classes')
                     ->get();
                 if ($user_total_spend >= $rewards[1]->min_point && $user_total_spend < $rewards[2]->min_point) {
                     $user_loyalty_type = $rewards[1]->id;
                 }
                 elseif ($user_total_spend >= $rewards[2]->min_point) {
                     $user_loyalty_type = $rewards[2]->id;
                 }
                 else {
                     $user_loyalty_type = $rewards[0]->id;
                 }
 
                 // Updating user loyalty type
                 $update_user = DB::table('users')
                 ->where('citizen_id', $citizen_id)
                 ->update([
                     'user_type' => $user_loyalty_type
                 ]);
 
         
             
                 if ($transaction && $cashAccount && $postransaction && $reward && $transactions_payments) {
                     
                     $response = array(
                             "responseCode" => "00",
                             "status" => "success",
                             "message" => "Payment successful",
                             "cashback_earned" => strval($reward->cashback_earned),
                             "tranx_id" => $reward->tranx_id,
                             "points_earned" => strval($reward->point_earned),
                             "amount_paid" => strval($reward->amount_spent),
                             "store_name" => $data['storeName'],
                             "payment_reference" => $transaction_id
                             //"payment_reference" => $pay_vendor['data']['payout_reference']
                         );
                         // Sending email to customer
                         MailController::sendPaymentNotification($senderName, $senderAccount, $citizen->email, $amount, $data['storeName'], $receiver_citizen_id, $transaction_id);
                         return json_encode($response);
                         
                 }
                 else {
                     return json_encode(
                         array(
                             "responseCode" => "11",
                             "message" => "Transaction Failed"
                         ));
                 }
             }
             else {
                 return json_encode(
                     array(
                         "responseCode" => "11",
                         "message" => "Payment Failed",
                         "wallet_vendor_earned_amount" => $wallet_vendor_earned_amount,
                         "wallet_landmark_sales_rent_amount" => $wallet_landmark_sales_rent_amount,
                         "wallet_landmark_reward_bank_amount" => $wallet_landmark_reward_bank_amount,
                         "amount" => $amount,
                         "response" => $pay_vendor
                     ));
             }
         }
         else {
             return json_encode(
                 array(
                     "responseCode" => "11",
                     "message" => "Invalid Authentication Pin"
                 ));
         }
     }

    public function sendMoney(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $transaction_id = Helper::generateTranxNumber(10);
        $pin = ($request->pin == '0000') ? '0' : $request->pin;
        $userController = new UserController;
        $response = $userController->getUserBankInfo();
        $userBalance = $response['availableBalance'];
        $senderName = Auth::user()->first_name.' '.Auth::user()->last_name;
        $senderAccount = Auth::user()->vnuban;
        
        if ($userBalance < $request->amount) {
            return json_encode(
                array(
                    "responceCode" => "11",
                    "message" => "Insufficient fund for this transaction"
                ));
                exit();
        }
        // Checking if account is locked.
        $getUser = DB::table('users')
            ->where('citizen_id', $citizen_id)
            ->get();
        if ($getUser[0]->account_status == 'Locked') {
            return json_encode(
                array(
                    "responseCode" => "11",
                    "message" => "Account is Locked"
                ));
                exit();
        }

        // Checking if receiver exists.
        if (DB::table('users')->where('citizen_id', $request->recipientId)->doesntExist()) {
            return json_encode(
                array(
                    "responseCode" => "11",
                    "message" => "Recipient account is not found."
                ));
                exit();
        }

        if (Hash::check($pin, $getUser[0]->user_pin)) {
            $sender_wallet_id = Auth::user()->wallet_id;
            // Getting receiver vnuban
            $getReceiver = DB::table('users')
            ->where('citizen_id', $request->recipientId)
            ->first();
            $receiverName = $getReceiver->first_name.' '.$getReceiver->last_name;
            $receiver_wallet_id = $getReceiver->wallet_id;
            $receiver_citizen_id = $request->recipientId;
            $sent_amount = $request->amount;
            $amount = $request->amount;
            $tranx_id = $transaction_id;
            $BankApiController = new BankApiController;
            $paymentResponse = $BankApiController->wallet_payment($sent_amount, $sender_wallet_id, $receiver_wallet_id);
            if($paymentResponse['status'] == 'success') {
            $transaction = Transactions::create([
                'transaction_id' => $transaction_id,
                'from' => $citizen_id,
                'to' => $request->recipientId,
                'transaction_mode' => 'Transfer',
                'narration' => $request->description,
                'amount' => $request->amount
            ]);
    
            // Update cash_accounts table for sender
           $cashAccount = CashAccount::create([
                'transaction_id' => $transaction_id,
                'account_id' => $citizen_id,
                'transaction_type' => 'Debit',
                'amount' => $request->amount
            ]);
    
            // Update cash_accounts table for receiver
           $cashAccount_1 = CashAccount::create([
                'transaction_id' => $transaction_id,
                'account_id' => $request->recipientId,
                'transaction_type' => 'Credit',
                'amount' => $request->amount
            ]);
    
            if ($transaction && $cashAccount && $cashAccount_1) {
                // Sending email to the receiver
                MailController::sendPaymentNotification($senderName, $senderAccount, $getReceiver->email, $request->amount, $receiverName, $request->recipientId, $transaction_id);
                // Sending email to the sender
                MailController::sendPaymentNotification($senderName, $senderAccount, Auth::user()->email, $request->amount, $receiverName, $request->recipientId, $transaction_id);
                return json_encode(
                    array(
                        "responseCode" => "00",
                        "message" => "Transfer successful",
                        "recipientName" => $getReceiver->first_name.' '.$getReceiver->last_name,
                        "amount" => $request->amount,
                        "tranx_id" => $transaction_id
                        //"transferResponse" => $paymentResponse
                    ));
            }
            else {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Transfer failed"
                    ));
                }
            }
            else {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Transfer failed"
                    ));
            }
         }
         else {
            return json_encode(
                array(
                    "message" => "Invalid Authentication Pin"
                ));
        }
    }

    public function getInflows(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
            $total_records = DB::table('transactions')
                ->where('to', $citizen_id)
                ->count();
        
                  // Get current page number
                if (isset($request->page) && $request->page!="") {
                    $page_no = $request->page;
                    } 
                else {
                        $page_no = 1;
                    }
        
                // Set total products per page
                $total_records_per_page = 10;
                // Calculate OFFSET Value and SET other Variables
                $offset = ($page_no-1) * $total_records_per_page;
                $previous_page = $page_no - 1;
                $next_page = $page_no + 1;
                $adjacents = "2";
        
                $total_no_of_pages = ceil($total_records / $total_records_per_page);
                $second_last = $total_no_of_pages - 1; // total pages minus 1
        
                $query = DB::table('transactions')
                    ->where('to', $citizen_id)
                    ->orderBy('id', 'desc')
                    ->offset($offset)
                    ->limit( $total_records_per_page)
                    ->get();

        if (DB::table('transactions')
        ->where('to', $citizen_id)
        ->exists()) {
            foreach ($query as $value) {
                $cashAccount = DB::table('cash_accounts')
                ->where('transaction_id', $value->transaction_id)
                ->where('account_id', $citizen_id)
                ->get();
                $transaction_type = $cashAccount[0]->transaction_type;

                if ($value->transaction_mode == 'Funded Wallet') {
                    $transaction_title = 'Funded Wallet';
                    $senderName = 'Funded Wallet';
                }
                elseif ($value->transaction_mode == 'Redeemed Cashback') {
                    $transaction_title = 'Redeemed Cashback';
                    $senderName = 'Redeemed Cashback';
                }
                elseif ($value->transaction_mode == 'Transfer') {
                    $sender = DB::table('users')
                    ->where('citizen_id', $value->from)
                    ->get();
                    $senderName = $sender[0]->first_name.' '.$sender[0]->last_name;
                    $transaction_title = 'Transfer from '.$senderName;
                }
                $from = $senderName;
                $to = 'Me';
                $narration = isset($value->narration) ? $value->narration : '';
                $response[] = array(
                    'from' => $from,
                    'to' => $to,
                    'transactionId' => $value->transaction_id,
                    'transactionTitle' => $transaction_title,
                    'date' => $value->created_at,
                    'amount' => $value->amount,
                    'transaction_type' => $transaction_type,
                    'narration' => $narration
                );
            }
            if(!empty($response)){
                return json_encode(
                    array(
                        "page_no" => $page_no,
                        "total_records" => $total_records,
                        "total_records_per_page" => $total_records_per_page,
                        "total_no_of_pages" => $total_no_of_pages,
                        "data" => $response
                    ));
                return $response;
            }
        }
        else {
            return json_encode(
                array(
                    "response" => "No Transactions Available",
                ));
        }
    }
    

    public function getOutflows(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $total_records = DB::table('transactions')
            ->where('from', $citizen_id)
            ->count();
    
              // Get current page number
            if (isset($request->page) && $request->page!="") {
                $page_no = $request->page;
                } 
            else {
                    $page_no = 1;
                }
    
            // Set total products per page
            $total_records_per_page = 10;
            // Calculate OFFSET Value and SET other Variables
            $offset = ($page_no-1) * $total_records_per_page;
            $previous_page = $page_no - 1;
            $next_page = $page_no + 1;
            $adjacents = "2";
    
            $total_no_of_pages = ceil($total_records / $total_records_per_page);
            $second_last = $total_no_of_pages - 1; // total pages minus 1
    
            $query = DB::table('transactions')
                ->where('from', $citizen_id)
                ->orderBy('id', 'desc')
                ->offset($offset)
                ->limit( $total_records_per_page)
                ->get();


        if (DB::table('transactions')
        ->where('from', $citizen_id)
        ->exists()) {
            foreach ($query as $value) {
                $cashAccount = DB::table('cash_accounts')
                ->where('transaction_id', $value->transaction_id)
                ->where('account_id', $citizen_id)
                ->get();
                $transaction_type = $cashAccount[0]->transaction_type;

                $receiver = DB::table('users')
                    ->where('citizen_id', $value->to)
                    ->get();
                    if (DB::table('users')
                    ->where('citizen_id', $value->to)
                    ->exists()) {
                        $receiverName = $receiver[0]->first_name.' '.$receiver[0]->last_name;
                    }
                else {
                    $receiver = DB::table('vendors')
                    ->where('vendor_id', $value->to)
                    ->get();
                    $receiverName = $receiver[0]->store_name;
                }
                    if ($value->transaction_mode == 'Transfer') {
                        $transaction_title = 'Transfer to '.$receiverName;
                    }
                    elseif ($value->transaction_mode == 'Payment') {
                        $transaction_title = 'Payment to '.$receiverName;
                    }
                    $from = 'Me';
                    $to = $receiverName;
                    $narration = isset($value->narration) ? $value->narration : '';
                    $response[] = array(
                        'from' => $from,
                        'to' => $to,
                        'transactionId' => $value->transaction_id,
                        'transactionTitle' => $transaction_title,
                        'date' => $value->created_at,
                        'amount' => $value->amount,
                        'transaction_type' => $transaction_type,
                        'narration' => $narration
                    );
            }
            if(!empty($response)){
                return json_encode(
                    array(
                        "page_no" => $page_no,
                        "total_records" => $total_records,
                        "total_records_per_page" => $total_records_per_page,
                        "total_no_of_pages" => $total_no_of_pages,
                        "data" => $response
                    ));
                return $response;
            }
        }
        else {
            return json_encode(
                array(
                    "response" => "No Transactions Available",
                ));
        }
            
    }

    public function getAllTransactions(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $total_records = DB::table('transactions')
            ->where('from', $citizen_id)
            ->orWhere('to', $citizen_id)
            ->count();

          // Get current page number
        if (isset($request->page) && $request->page!="") {
            $page_no = $request->page;
            } 
        else {
                $page_no = 1;
            }

        // Set total products per page
        $total_records_per_page = 5;
        // Calculate OFFSET Value and SET other Variables
        $offset = ($page_no-1) * $total_records_per_page;
        $previous_page = $page_no - 1;
        $next_page = $page_no + 1;
        $adjacents = "2";

        $total_no_of_pages = ceil($total_records / $total_records_per_page);
        $second_last = $total_no_of_pages - 1; // total pages minus 1

        $query = DB::table('transactions')
            ->where('from', $citizen_id)
            ->orWhere('to', $citizen_id)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit( $total_records_per_page)
            ->get();

        foreach ($query as $value) {
            if (DB::table('cash_accounts')
            ->where('transaction_id', $value->transaction_id)
            ->where('account_id', $citizen_id)
            ->exists()) {
                $cashAccount = DB::table('cash_accounts')
                ->where('transaction_id', $value->transaction_id)
                ->where('account_id', $citizen_id)
                ->get();
                $transaction_type = $cashAccount[0]->transaction_type;
                if ($value->to == $citizen_id) {
                    if ($value->transaction_mode == 'Funded Wallet') {
                        $transaction_title = 'Funded Wallet';
                        $senderName = 'Funded Wallet';
                    }
                    elseif ($value->transaction_mode == 'Redeemed Cashback') {
                        $transaction_title = 'Redeemed Cashback';
                        $senderName = 'Redeemed Cashback';
                    }
                    elseif ($value->transaction_mode == 'Transfer') {
                        $sender = DB::table('users')
                        ->where('citizen_id', $value->from)
                        ->get();
                        $senderName = $sender[0]->first_name.' '.$sender[0]->last_name;
                        $transaction_title = 'Transfer from '.$senderName;
                    }
                    $from = $senderName;
                    $to = 'Me';
                }
                elseif ($value->from == $citizen_id) {
                    $receiver = DB::table('users')
                    ->where('citizen_id', $value->to)
                    ->get();
                    if (DB::table('users')
                    ->where('citizen_id', $value->to)
                    ->exists()) {
                        $receiverName = $receiver[0]->first_name.' '.$receiver[0]->last_name;
                    }
                else {
                    $receiver = DB::table('vendors')
                    ->where('vendor_id', $value->to)
                    ->get();
                    $receiverName = $receiver[0]->store_name;
                }
                    if ($value->transaction_mode == 'Transfer') {
                        $transaction_title = 'Transfer to '.$receiverName;
                    }
                    elseif ($value->transaction_mode == 'Payment') {
                        $transaction_title = 'Payment to '.$receiverName;
                    }
                    $from = 'Me';
                    $to = $receiverName;
                }
                $narration = isset($value->narration) ? $value->narration : '';
                $response[] = array(
                    'from' => $from,
                    'to' => $to,
                    'transactionId' => $value->transaction_id,
                    'transactionTitle' => $transaction_title,
                    'date' => $value->created_at,
                    'amount' => $value->amount,
                    'transaction_type' => $transaction_type,
                    'narration' => $narration
                );
            }
        }
        if(!empty($response)){
            return json_encode(
                array(
                    "page_no" => $page_no,
                    "total_records" => $total_records,
                    "total_records_per_page" => $total_records_per_page,
                    "total_no_of_pages" => $total_no_of_pages,
                    "data" => $response
                ));
           
        }
        else {
            return json_encode(
                array(
                    "response" => "No Transactions Available",
                ));
        }
    }

    public function getRecipientInfo(Request $request)
    {
        $recipient = DB::table('users')
                ->where('citizen_id', $request->recipientId)
                ->get();
        
        if(isset($recipient[0])) {
            $response[] = array(
                'responseCode' => '00',
                'recipientName' => $recipient[0]->first_name.' '.$recipient[0]->last_name,
                'recipientId' => $recipient[0]->citizen_id
            );
            return json_encode($response);
        }
        else {
            $response[] = array(
                'responseCode' => '11',
                'message' => 'Recipient number not found'
            );
            return json_encode($response);
        }
    }

    public function getRecentRecipients()
    {
        $citizen_id = Auth::user()->citizen_id;
        if (DB::table('transactions')->where('from', $citizen_id)->where('transaction_mode', "Transfer")->exists()) {
            $recipientList = DB::table('transactions')
                ->where('from', $citizen_id)
                ->where('transaction_mode', "Transfer")
                ->distinct()
                ->get();

                foreach ($recipientList as $value) {

                    $recipient = DB::table('users')
                    ->where('citizen_id', $value->to)
                    ->get();
                    $response[] = array(
                        'recipientName' => $recipient[0]->first_name.' '.$recipient[0]->last_name,
                        'recipientId' => $recipient[0]->citizen_id
                    );
                }
                //$recipient_array = array_unique($response, SORT_REGULAR );
                return  array_values(array_unique($response, SORT_REGULAR));
        }
        
    }

    public function showTransactionDetails(Request $request)
    {
        $citizen_id = Auth::user()->citizen_id;
        $transactions = DB::table('transactions')
            ->where('transaction_id', $request->transactionId)
            ->get();
        if (DB::table('cash_accounts')
            ->where('transaction_id', $request->transactionId)
            ->exists()) {
                $cashAccount = DB::table('cash_accounts')
                ->where('transaction_id', $request->transactionId)
                ->get();
                $transaction_type = $cashAccount[0]->transaction_type;
                if ($transactions[0]->to == $citizen_id) {
                    if ($transactions[0]->transaction_mode == 'Funded Wallet') {
                        $transaction_title = 'Funded Wallet';
                        $senderName = 'Funded Wallet';
                    }
                    elseif ($transactions[0]->transaction_mode == 'Redeemed Cashback') {
                        $transaction_title = 'Redeemed Cashback';
                        $senderName = 'Redeemed Cashback';
                    }
                    elseif ($transactions[0]->transaction_mode == 'Transfer') {
                        $sender = DB::table('users')
                        ->where('citizen_id', $transactions[0]->from)
                        ->get();
                        $senderName = $sender[0]->first_name.' '.$sender[0]->last_name;
                        $transaction_title = 'Transfer from '.$senderName;
                    }
                    $from = $senderName;
                    $to = 'Me';
                }
                elseif ($transactions[0]->from == $citizen_id) {
                    $receiver = DB::table('users')
                    ->where('citizen_id', $transactions[0]->to)
                    ->get();
                    if (DB::table('users')
                    ->where('citizen_id', $transactions[0]->to)
                    ->exists()) {
                        $receiverName = $receiver[0]->first_name.' '.$receiver[0]->last_name;
                    }
                else {
                    $receiver = DB::table('vendors')
                    ->where('vendor_id', $transactions[0]->to)
                    ->get();
                    $receiverName = $receiver[0]->store_name;
                }
                    if ($transactions[0]->transaction_mode == 'Transfer') {
                        $transaction_title = 'Transfer to '.$receiverName;
                    }
                    elseif ($transactions[0]->transaction_mode == 'Payment') {
                        $transaction_title = 'Payment to '.$receiverName;
                    }
                    $from = 'Me';
                    $to = $receiverName;
                }
                $response = array(
                    'from' => $from,
                    'to' => $to,
                    'transactionId' => $request->transactionId,
                    'transactionTitle' => $transaction_title,
                    'date' => $transactions[0]->created_at,
                    'amount' => $transactions[0]->amount,
                    'transaction_type' => $transaction_type,
                    'narration' => $transactions[0]->narration,
                );

                return $response;
            }
    } 

    public function fundWalletNotes()
    {
        $query = DB::table('fund_wallet_notes')->get();
        return $query;
    }

    public function verifyPayment(Request $request)
    {
        $payments = DB::table('transaction_payments')
            ->where('tranx_ref', $request->ref)
            ->first();
       
            return json_encode(
                array(
                    "responseCode" => "00",
                    "data" => $payments
                ));
       
    }

}
