<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\Cashier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PosTransactions;

class CashierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        if (!Auth::guard('cashier')->attempt($request->only('email', 'password'))) {
            return response([
                'message' => 'Invalid credentials!'
            ]);
        }
        $user = Auth::guard('cashier')->user();
        // if ($user->is_verified == 0) {
        //     return json_encode(
        //         array(
        //             "response" => "Not verified",
        //             "userToken" => $user->user_token
        //         ));
        // }
        $token = $user->createToken('token')->plainTextToken;
        $cookie = cookie('jwt', $token, 60 * 24); // 1 day
        $user_token = $user->user_token;

        return json_encode(
            array(
                "message" => "Successful login.",
                "responseCode" => "00",
                "apiToken" => $token,
                "userBlock" => $user
            ));

        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPaymentInfo(Request $request)
    {
        $tranxId = Helper::generateTranxNumber(10);
        $vendor_id = Auth::user()->vendor_id;
        $cashier_id = $request->cashierId;
        $amount = $request->amount;

        // log pos transaction
        $postransaction = PosTransactions::create([
            'transaction_id' => $tranxId,
            'cashier_id' => $cashier_id,
            'vendor_id' => $vendor_id,
            'amount' => $amount,
            'tranx_value' => $amount,
            'narration' => $request->narration,
            'status' => 'Pending'
        ]);

        // Get Store Name
        $store = DB::table('vendors')
            ->where('vendor_id', $vendor_id)
            ->get();
        $store_name = $store[0]->store_name;
        return json_encode(
            array(
                "cashierId" => $cashier_id,
                "vendorId" => $vendor_id,
                "storeName" => $store_name,
                "amount" => $amount,
                "vendorAccountNumber" => $store[0]->wallet_id,
                "tranxId" => $tranxId,
            ));
    }

    public function getOnlinePaymentInfo(Request $request)
    {
        $tranxId = $request->tranxId;
        $vendor_id = $request->vendor_id;
        $amount = $request->amount;

         // Get cashier information
         $cashier = DB::table('cashiers')
         ->where('vendor_id', $vendor_id)
         ->where('privilege', 'Owner')
         ->first();
        $cashier_id = $cashier->cashier_id;

        // log pos transaction
        $postransaction = PosTransactions::create([
            'transaction_id' => $tranxId,
            'cashier_id' => $cashier_id,
            'vendor_id' => $vendor_id,
            'amount' => $amount,
            'tranx_value' => $amount,
            'narration' => 'Online payment',
            'status' => 'Pending'
        ]);

        // Get Store Name
        $store = DB::table('vendors')
            ->where('vendor_id', $vendor_id)
            ->get();
        $store_name = $store[0]->store_name;
        return json_encode(
            array(
                "cashier_id" => $cashier_id,
                "vendor_id" => $vendor_id,
                "account_name" => $store_name,
                "amount_payable" => $amount,
                "account_no" => $store[0]->wallet_id,
                "payment_ref" => $tranxId,
            ));
    }
    
    public function cashierLogin(Request $request)
    {
        $pin = $request->pin;
        $cashierId = $request->cashierId;
        $pinHash = sha1($pin);
        $cashier_exist = DB::table('cashiers')
            ->where('pin', $pinHash)
            ->where('cashier_id', $cashierId)
            ->where('account_status', 'Active')
            ->exists();
        
        
        if ($cashier_exist) {
                $cashier_details = DB::table('cashiers')
                ->where('pin', $pinHash)
                ->where('cashier_id', $cashierId)
                ->where('account_status', 'Active')
                ->get();

                $response = array(
                    'response' => "00",
                    'name' => $cashier_details[0]->name,
                    'cashier_id' => $cashier_details[0]->cashier_id,
                    'vendor_id' => $cashier_details[0]->vendor_id,
                    'privilege' => $cashier_details[0]->privilege,
                    'phone' => $cashier_details[0]->phone,
                    'picture' => $cashier_details[0]->picture
                );
                echo json_encode($response);
            }
            else {
                $response = array(
                    'response' => "11",
                    'message' => 'Invalid Credentials!'
                );
                echo json_encode($response);
            }
    }

    // Get Cashiers
    public function getCashiers()
    {
        $vendor_id = Auth::user()->vendor_id;
        $cashier_exist = DB::table('cashiers')
            ->where('vendor_id', $vendor_id)
            ->exists();
        
        if ($cashier_exist) {
            $cashier_list = DB::table('cashiers')
            ->where('vendor_id', $vendor_id)
            ->get();
            foreach ($cashier_list as $cashier) {
                
                $response[] = array(
                    'name' => $cashier->name,
                    'cashier_id' => $cashier->cashier_id,
                    'vendor_id' => $cashier->vendor_id,
                    'privilege' => $cashier->privilege,
                    'phone' => $cashier->phone,
                    'picture' => $cashier->picture,
                    'created_at' => $cashier->created_at,
                    'account_status' => $cashier->account_status
                );
            }
            return $response;
        }
        else {
            return 'No data';
        }
    }

    public function changePin(Request $request)
    {
        $cashier_id = $request->cashierId;
        $pinHash = sha1($request->oldPin);
        $newPinHash = sha1($request->newPin);
        $getPin = DB::table('cashiers')
            ->where('cashier_id', $cashier_id)
            ->get();
        
        if ($pinHash == $getPin[0]->pin)
            {
                $changePin = DB::table('cashiers')
                    ->where('cashier_id', $cashier_id)
                    ->where('pin', $pinHash)
                    ->update([
                        'pin' => $newPinHash
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

    public function resetPin(Request $request)
    {
        $cashier_id = $request->cashierId;
        $newPinHash = sha1($request->newPin);
        
        $changePin = DB::table('cashiers')
                    ->where('cashier_id', $cashier_id)
                    ->update([
                        'pin' => $newPinHash
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

    public function cashierBalance(Request $request)
    {
        $cashier_id = $request->cashierId;
        $today = date("Y-m-d");
        $cashierBalance = DB::table('pos_transactions')
            ->where('cashier_id', $cashier_id)
            ->where('status', 'Paid')
            ->whereDate('created_at', $today)
            ->sum('tranx_value');
        
        return $cashierBalance;
    }
    
    public function logout() {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Logged out'
        ];
    }

    public function getGiftCardPaymentInfo(Request $request)
    {
        $tranxId = Helper::generateTranxNumber(10);
        $vendor_id = Auth::user()->vendor_id;
        $cashier_id = $request->cashierId;
        $amount = $request->amount;

        // log pos transaction
        $postransaction = PosTransactions::create([
            'transaction_id' => $tranxId,
            'cashier_id' => $cashier_id,
            'vendor_id' => $vendor_id,
            'amount' => $amount,
            'tranx_value' => $amount,
            'narration' => $request->narration,
            'status' => 'Pending'
        ]);
        return json_encode(
            array(
                "cashierId" => $cashier_id,
                "amount" => $amount,
                "tranxId" => $tranxId,
            ));
    }
}
