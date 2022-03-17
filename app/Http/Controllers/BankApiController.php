<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TransactionPayments;
use App\Helper\Helper;
use Illuminate\Support\Facades\DB;

class BankApiController extends Controller
{
    public function updateTransactionPayents($vendor_id, $vnuban, $tranx_ref, $payment_ref, $cust_email, $amount_payable)
    {
       $fee = 200;
        $merchant_amount = $amount_payable - $fee;
        $transactions = TransactionPayments::create([
            'vendor_id' => $vendor_id,
            'vnuban' => $vnuban,
            'payment_ref' => $payment_ref,
            'tranx_ref' => $tranx_ref,
            'cust_email' => $cust_email,
            'merchant_amount' => $merchant_amount,
            'fee' => $fee,
            'amount_payable' => $amount_payable,
            'amount_paid' => '0',
            'status' => 'Pending',
        ]);
        return $transactions;
    }

    public function generatePaymentInfo(Request $request)
    {
        $tranx_ref = $request->tranx_ref;
        $payment_ref = Helper::generateTranxNumber(6);
        if (($request->amount * 0.012) <= 2000) {
            $amount_payable = $request->amount * (1 + 0.012);
        }
        else {
        $amount_payable = $request->amount + 2000;
        }
        $vendor_id = 'RE4567HJ';
        $vnuban = '9104666181';
        $merchant_name = 'Landmark Vendor';
        $response = BankApiController::updateTransactionPayents($vendor_id, $vnuban, $tranx_ref, $payment_ref, $request->contact_email, $amount_payable);
        return json_encode(
            array(
                "payment_ref" => $payment_ref,
                "amount_payable" => '₦'.number_format($amount_payable,2),
                "account_no" => $vnuban,
                "account_name" => $merchant_name,
                "bank_name" => 'Sparkle Bank',
                "cust_email" => $request->contact_email
            ));
       
        
    }

    // Paystack / Get Wallet integrations

    public function fixedAccountsPaystack()
    {
        $fixedAccounts = array(
            'landmark_reward_wallet_id' => '6193c5f7d7a12306b02b48a3', 
            'landmark_sales_rent_wallet_id' => '6193c616d7a12306b02b48a4',
            'landmark_transaction_fee_account' => '61c1c700dc36008ee80295f0'
        );
        return $fixedAccounts;
    }

    // Create Wallet
    // public function paystackCreateWallet($email)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->post('https://api.getwallets.co/v1/wallets', [
    //         'customer_email' => $email,
    //     ]);
    //     return $response['data']['wallet_id'];
    // }

    // Make Payment
    // public function makePaymentPaystack($amount, $from_wallet_id, $to_wallet_id)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->post('https://api.getwallets.co/v1/wallets/transfers/wallet', [
    //         'amount' => $amount,
    //         'currency' => 'NGN',
    //         'from_wallet_id' => $from_wallet_id,
    //         "to_wallet_id" => $to_wallet_id
    //     ]);
    //     return $response;
    // }

    // Generate Bank Account details to fund wallet
    // public function generateAccountInfo($walletId, $phone, $first_name, $last_name)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->post('https://api.getwallets.co/v1/wallets/funds/banktransfer', [
    //         'wallet_id' => $walletId,
    //         'phone' => $phone,
    //         'first_name' => $first_name,
    //         'last_name' => $last_name,
    //     ]);
    //     return $response;
    // }

    // public function payOutPaystack($amount, $from_wallet_id, $bank_code, $account_number)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->post('https://api.getwallets.co/v1/wallets/transfers/wallet', [
    //         'amount' => $amount,
    //         'from_wallet_id' => $from_wallet_id,
    //         "bank_code" => $bank_code,
    //         "account_number" => $account_number
    //     ]);
    //     return $response;
    // }

    // public function walletBalance($wallet_id)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->get('https://api.getwallets.co/v1/wallets/'.$wallet_id);
    //     return $response['data']['balances'][0]['balance'] / 100;
    // }

    public function verifyAccountPaystack($account_no, $bank_code)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer sk_live_ebff764dd335e20b9bdf254fc9e5c77cdbc0e371'
        ])->get('https://api.paystack.co/bank/resolve', [
            "account_number" => $account_no,
            "bank_code" => $bank_code
        ]);
        return $response;
    }

    public function payOutSparkle($amount, $destinationBankCode, $destinationAccountNumber, $tranx_id)
    {
        // Basic authentication...
        $result = Http::withBasicAuth('deRAFJJbGNnJ7CUb', 'WA3wE5$Nt7gMJ2RQ')
        ->post('https://virtual.sparkleapp.in/collection/payout/v2/inter-bank-transfer', [
            'dic' => $destinationBankCode,
            'beneficiaryAccountNumber' => $destinationAccountNumber,
            'senderName' => 'Landmark Leisure Beach',
            'fromAccount' => '1000460457',
            'narration' => 'payout_landmark',
            'amount' => $amount,
            'transactionReference' => $tranx_id
        ]);

        if (isset($result['transactionReference'])) {
            $response = array(
                'responseCode' => "00",
                'response' => 'success'
            );
            return $response;
        }
        else {
            $response = array(
                'responseCode' => "11",
                'response' => 'failed'
            );
            return $response;
        }
    }

    public function get_bank_code()
    {
        // Read the JSON file 
        $json = file_get_contents(__DIR__.'/bank_list.json');
        
        // Decode the JSON file
        $banks_data = json_decode($json,true);
        $bank_details = $banks_data['data'];
        

        foreach ($bank_details as  $value) {
            if ($value['cbn_code'] == '044') {
                $bank_code = $value['bank_code'];
            }
        }
        return $bank_code;
    }

    // public function debitWallet($walletId, $currency, $amount)
    // {
    //     $response = Http::withHeaders([
    //         'Content-Type' => 'application/json',
    //         'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
    //     ])->post('https://api.getwallets.co/v1/wallets/debit/manual', [
    //         'wallet_id' => $walletId,
    //         'currency' => $currency,
    //         'amount' => intval($amount)
    //     ]);
    //     return $response;
    // }

    // ==================== SMAPPS API INTEGRATION STARTS HERE ===========================
    // Creating wallet
    public function create_wallet($email, $first_name, $last_name, $phone)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 2|ZgMPsZ0PJ2flMpfGwc3tclIFv1V549LBHOShpXHZ'
        ])->post('https://www.smappsgroup.com/pay/public/api/create_wallet', [
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
        ]);
        return $response;
    }


    // Get Wallet Details
    public function wallet_details($wallet_id)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 2|ZgMPsZ0PJ2flMpfGwc3tclIFv1V549LBHOShpXHZ'
        ])->get('https://www.smappsgroup.com/pay/public/api/wallet_details', [
            'wallet_id' => $wallet_id
        ]);
        return $response;
    }

    // Making Internal payments
    public function wallet_payment($amount, $from_wallet_id, $to_wallet_id)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 2|ZgMPsZ0PJ2flMpfGwc3tclIFv1V549LBHOShpXHZ'
        ])->post('https://www.smappsgroup.com/pay/public/api/wallet_payments', [
            'amount' => $amount,
            'from_wallet_id' => $from_wallet_id,
            'to_wallet_id' => $to_wallet_id,
        ]);
        return $response;
    }

    // Debit Wallet
    public function debit_wallet($wallet_id, $amount)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer 2|ZgMPsZ0PJ2flMpfGwc3tclIFv1V549LBHOShpXHZ'
        ])->post('https://www.smappsgroup.com/pay/public/api/debit_wallet', [
            'amount' => $amount,
            'wallet_id' => $wallet_id
        ]);
        return $response;
    }

    // ==================== SMAPPS API INTEGRATION ENDS HERE =============================
}
