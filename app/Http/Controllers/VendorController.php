<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Cashier;
use App\Models\GiftCardSpend;
use App\Models\CashOutPin;
use App\Models\TransactionPayments;
use App\Models\CashAccount;
use App\Models\VendorPayouts;
use App\Http\Controllers\BankApiController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use DataTables;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   

    public function login(Request $request)
    {
        if (!Auth::guard('vendor')->attempt($request->only('contact_email', 'password'))) {
            return response([
                'message' => 'Invalid credentials!'
            ]);
        }
        $user = Auth::guard('vendor')->user();
        // if ($user->password_active == 0) {
        //     return json_encode(
        //         array(
        //             "response" => "11",
        //             "Message" => 'Password change is required to continue'
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
    public function store(Request $request)
    {
        $vendor_id = Helper::generateNumber(10);
        $cashier_id = Helper::generateNumber(10);
        $pin = Helper::generateNumber(4);
        //$password = Helper::generateTranxNumber(6);
        $password = Helper::generateTranxNumber(6);
        $passwordHash = Hash::make($password);
        $BankApiController = new BankApiController;
        $storeName = explode(" ", $request->store_name);
        $store_first_name = $storeName[0];
        $store_last_name = !isset($storeName[1]) ? 'Landmark' : $storeName[1];
        if (DB::table('vendors')
            ->where('contact_email', $request->input('contact_email'))
            ->exists()) {
                return 'Email address already exists';
                exit();
            }
        
        if (DB::table('vendors')
            ->where('contact_email', $request->input('contact_email'))
            ->doesntExist()) {
        $accountInfo = $BankApiController->create_wallet($request->contact_email, $store_first_name, $store_last_name, $request->contact_phone);
        $wallet_id = $accountInfo['wallet_id'];
        $vnuban = $accountInfo['account_number'];
                // Creating vendors
                $vendorQuery = Vendor::create([
                    'vendor_id' => $vendor_id,
                    'store_name' => $request->store_name,
                    'contact_name' => $request->contact_name,
                    'contact_email' => $request->contact_email,
                    'contact_phone' => $request->contact_phone,
                    'password' => $passwordHash,
                    'vnuban' => $vnuban,
                    'sales_rent' => $request->sales_rent,
                    'loyalty_discount' => $request->loyalty_discount,
                    'account_no' => $request->account_no,
                    'account_name' => $request->account_name,
                    'bank_name' => $request->bank_name,
                    'bank_code' => $request->bank_code,
                    'password_active' => 0,
                    'wallet_id' => $wallet_id
                ]);

                // Creating Cashier
                $cashierQuery = Cashier::create([
                    'cashier_id' => $cashier_id,
                    'name' => $request->contact_name,
                    'phone' => $request->contact_phone,
                    'vendor_id' => $vendor_id,
                    'privilege' => 'Owner',
                    'pin' => sha1($pin),
                    'picture' => 'NULL',
                    'account_status' => 'Active'
                ]);

                if($vendorQuery && $cashierQuery) {
                    MailController::sendVendorRegisterEmail($request->contact_name, $request->contact_email, $pin, $password, $request->store_name);
                    return 'success';
                }
                else {
                    return 'failed';
                }

            }
            else {
                $response = array(
                    'response' => "11",
                    'message' => "Contact Person Already Exists!"
                );
                echo json_encode($response);
            }
       
    }

    public function getVendorPosTransactions(Request $request)
    {
        $vendor_id = Auth::user()->vendor_id;
        $total_records = DB::table('pos_transactions')
            ->where('vendor_id', $vendor_id)
            ->where('status', 'Paid')
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

        $vendorTranx = DB::table('pos_transactions')
            ->where('vendor_id', $vendor_id)
            ->where('status', 'Paid')
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit( $total_records_per_page)
            ->get();

        if(DB::table('pos_transactions')
        ->where('vendor_id', $vendor_id)
        ->where('status', 'Paid')
        ->exists()) {
                return json_encode(
                    array(
                        "page_no" => $page_no,
                        "total_records" => $total_records,
                        "total_records_per_page" => $total_records_per_page,
                        "total_no_of_pages" => $total_no_of_pages,
                        "data" => $vendorTranx
                    ));
            }
            else {
                return json_encode(
                    array(
                        "response" => "No Transactions Available",
                    ));
            } 
    }

    public function getCashierPosTransactions(Request $request)
    {
        $cashier_id = $request->cashierId;
        $total_records = DB::table('pos_transactions')
            ->where('cashier_id', $cashier_id)
            ->where('status', 'Paid')
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

        $cashierTranx = DB::table('pos_transactions')
            ->where('cashier_id', $cashier_id)
            ->where('status', 'Paid')
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit( $total_records_per_page)
            ->get();
        
        if(DB::table('pos_transactions')
        ->where('cashier_id', $cashier_id)
        ->where('status', 'Paid')
        ->exists()) {
            return json_encode(
                array(
                    "page_no" => $page_no,
                    "total_records" => $total_records,
                    "total_records_per_page" => $total_records_per_page,
                    "total_no_of_pages" => $total_no_of_pages,
                    "data" => $cashierTranx
                ));
        }
        else {
            return json_encode(
                array(
                    "response" => "No Transactions Available",
                ));
        }
    }

    public function vendorList()
    {
       // Getting the list of vendors 
       $query = DB::table('vendors')
            ->orderBy('id', 'DESC')
            ->get();
            $vendors = [];
        foreach ($query as $vendor) {
            $noOfTranx = DB::table('transaction_payments')
            ->where('vendor_id', $vendor->vendor_id)
            ->where('status', 'Paid')
            ->count();

            $tranxValue = DB::table('transaction_payments')
            ->where('vendor_id', $vendor->vendor_id)
            ->where('status', 'Paid')
            ->sum('merchant_amount');
               $vendors[] = array(
                   'store_name' => $vendor->store_name,
                   'contact_name' => $vendor->contact_name,
                   'created_at' => $vendor->created_at,
                   'no_of_tranx' => $noOfTranx,
                   'tranx_value' => $tranxValue,
                   'id' => $vendor->id
               );
               
          }

          return Datatables::of($vendors)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '<a href="javascript:void(0);" class="action-icon edit-vendor" data-bs-toggle="modal" data-bs-target="#edit-vendor" data-id="'.$row['id'].'"><i class="mdi mdi-eye"></i></a><a href="javascript:void(0);" class="action-icon delete-vendor" data-bs-toggle="modal" data-bs-target="#delete-warning-modal" data-id="'.$row['id'].'"> <i class="mdi mdi-delete"></i></a>';
              return $actionBtn;
          })
          ->rawColumns(['action'])
          ->make(true);
    }

    public function displayVendors()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
        ])->get('https://api.getwallets.co/v1/banks');
        $bank_list = $response['data'];
        return view('admin.vendor', compact('bank_list'));
    }
   

  
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // Getting the list of vendors 
       $vendorDetails = DB::table('vendors')
       ->where('id', $request->id)
       ->get();
        return $vendorDetails;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $affected = DB::table('vendors')
              ->where('id', $request->id)
              ->update([
                  'store_name' => $request->store_name,
                  'contact_name' => $request->contact_name,
                  'contact_email' => $request->contact_email,
                  'contact_phone' => $request->contact_phone,
                  'sales_rent' => $request->sales_rent,
                  'loyalty_discount' => $request->loyalty_discount,
                  'account_no' => $request->account_no,
                  'account_name' => $request->account_name,
                  'bank_name' => $request->bank_name,
                  'bank_code' => $request->bank_code
                  ]);
        if($affected) {
            return 'success';
        }
        else {
            return 'No update is done. Please ensure that you are not using an existing email address or password and ensure that all fields are correctly filled.';
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyVendor(Request $request)
    {
        // Check if vendor has transactions
        $vendor = DB::table('vendors')
            ->where('id', $request->id)
            ->first();
        $vendor_id = $vendor->vendor_id;
        if(DB::table('pos_transactions')
        ->where('vendor_id',  $vendor_id)
        ->exists()){
            return 'Vendor already has pos transactions and cannot be deleted at this time. You can only disable this vendor.';
            exit();
        }
        if(DB::table('transactions')
        ->where('to',  $vendor_id)
        ->exists()){
            return 'Vendor already has transactions and cannot be deleted at this time. You can only disable this vendor.';
            exit();
        }
        // Delete from vendor's table
        $deleteVendor = DB::table('vendors')
            ->where('id', $request->id)
            ->delete();
        // Delete all vendor cashiers
        $deleteCashiers = DB::table('cashiers')
            ->where('vendor_id', $vendor_id)
            ->delete();
        if($deleteVendor && $deleteCashiers) {
                return 'success';
            }
        else {
                return 'failed';
            }
    }

    public function disableVendor(Request $request)
    {
        $affected = DB::table('vendors')
              ->where('id', $request->id)
              ->update([
                  'is_active' => '0'
                  ]);
        if($affected) {
            return 'success';
        }
        else {
            return 'failed';
        }
    }

    public function enableVendor(Request $request)
    {
        $affected = DB::table('vendors')
              ->where('id', $request->id)
              ->update([
                  'is_active' => '1'
                  ]);
        if($affected) {
            return 'success';
        }
        else {
            return 'failed';
        }
    }
// Create Cashiers
public function createCashier(Request $request)
    {
         // Creating Cashier 
         $vendor_id = Auth::user()->vendor_id;
         $cashier_id = Helper::generateNumber(10);
            // Uploading cashier image
            $img_url = 'NULL';
            if($request->file()) {
                $request->validate([
                    'picture' => 'required|mimes:jpeg,jpg|max:1024'
                    ]);

                $img_url = 'https://www.landmarkafrica.com/ldc/storage/app/'.$request->file('picture')->store('public/cashier_pictures');
            }
         $cashierQuery = Cashier::create([
                'cashier_id' => $cashier_id,
                'name' => $request->name,
                'phone' => $request->phone,
                'vendor_id' => $vendor_id,
                'privilege' => $request->privilege,
                'pin' => sha1($request->pin),
                'picture' => $img_url,
                'account_status' => 'Active'
            ]);
            if($cashierQuery) {
                $response = array(
                    'response' => "00",
                    'cashierBlock' => $cashierQuery
                );
                echo json_encode($response);
            }
            else {
                $response = array(
                    'response' => "11",
                    'message' => "User could not be registered; please confirm your input!"
                );
                echo json_encode($response);
            }
    }

    public function editCashier(Request $request)
    {
         // Editing Cashier 
        //  get cashier picture
        $Query = DB::table('cashiers')
         ->where('cashier_id', $request->cashierId)
         ->get();

         $img_url = $Query[0]->picture;
         $vendor_id = Auth::user()->vendor_id;
            // Uploading cashier image
            if($request->file()) {
                $request->validate([
                    'picture' => 'required|mimes:jpeg,jpg|max:1024'
                    ]);

                $img_url = 'https://www.landmarkafrica.com/ldc/storage/app/'.$request->file('picture')->store('public/cashier_pictures');
            }
         $cashierQuery = DB::table('cashiers')
         ->where('cashier_id', $request->cashierId)
         ->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'privilege' => $request->privilege,
                'picture' => $img_url,
                'updated_at' => Carbon::now()->toDateTimeString()
                
            ]);

            if($cashierQuery) {
                $response = array(
                    'response' => "00",
                    'message' => "User update successful"
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

    public function disableCashier(Request $request)
    {
        $affected = DB::table('cashiers')
              ->where('cashier_id', $request->cashierId)
              ->update([
                  'account_status' => 'disabled'
                  ]);
        if($affected) {
            $response = array(
                'response' => "00",
                'message' => "Cashier disabled successfully."
            );
            echo json_encode($response);
        }
        else {
            $response = array(
                'response' => "11",
                'message' => "Unable to disable cashier. Please contact support."
            );
            echo json_encode($response);
        }
    }

    public function enableCashier(Request $request)
    {
        $affected = DB::table('cashiers')
              ->where('cashier_id', $request->cashierId)
              ->update([
                  'account_status' => 'Active'
                  ]);
        if($affected) {
            $response = array(
                'response' => "00",
                'message' => "Cashier enabled successfully."
            );
            echo json_encode($response);
        }
        else {
            $response = array(
                'response' => "11",
                'message' => "Unable to enable cashier. Please contact support."
            );
            echo json_encode($response);
        }
    }

    public function vendorAccountInfo()
    {
        $vendor_id = Auth::user()->vendor_id;
        // Get user Inflows
        $vendor = DB::table('vendors')
            ->where('vendor_id', $vendor_id)
            ->get();
        $wallet_id = $vendor->wallet_id;
        $storeName = explode(" ", $vendor->store_name);
        $store_first_name = $storeName[0];
        $store_last_name = $storeName[1];
        $BankApiController = new BankApiController;
        $wallet_details = $BankApiController->wallet_details($wallet_id);
        $response = array(
            'accountName' =>$vendor_id,
            'accountNumber' => $wallet_details['virtual_account_number'],
            'bankName' => $wallet_details['virtual_bank_name'],
            'availableBalance' => $wallet_details['balance']
        );
        return $response;
    }

    public function vendorBalance()
    {
        $wallet_id = Auth::user()->wallet_id;
        $BankApiController = new BankApiController;
        $wallet_details = $BankApiController->wallet_details($wallet_id);
        $availableBalance = $wallet_details['balance'];
        return $availableBalance;
    }

    public function payOut(Request $request)
    {
        $vendor_id = Auth::user()->vendor_id;
        $pin = $request->pin;
        $pinHash = sha1($pin);
        if (DB::table('cash_out_pins')
            ->where('vendor_id', $vendor_id)
            ->where('pin', $pinHash)
            ->doesntExist()
        ) {
            $response = array(
                'responseCode' => "11",
                'message' => 'Invalid Payout Pin'
            );
            echo json_encode($response);
            exit();
        }
        $pinTime = DB::table('cash_out_pins')
            ->where('vendor_id', $vendor_id)
            ->where('pin', $pinHash)
            ->get();
        // Checking if pin has elapsed 5mins.
        $pinDate = strtotime($pinTime[0]->created_at);
        $now = strtotime(Carbon::now()->toDateTimeString());

        $totalMinsDiff = round(abs($pinDate-$now) / 60);
        if ($totalMinsDiff > 5) {
            $response = array(
                'responseCode' => "11",
                'message' => 'Pin has expired.'
            );
            echo json_encode($response);
            exit();
        }
        // Get user Inflows
        $vnuban = DB::table('vendors')
            ->where('vendor_id', $vendor_id)
            ->get();
        $vendor_wallet_id = $vnuban[0]->wallet_id;
        $BankApiController = new BankApiController;
        $wallet_details = $BankApiController->wallet_details($vendor_wallet_id);
        $availableBalance = $wallet_details['balance'];
        $destinationAccountNumber = $vnuban[0]->account_no;
        $destinationAccountName = $vnuban[0]->account_name;
        $destinationBankName = $vnuban[0]->bank_name;
        $cbn_code = $vnuban[0]->bank_code;
        $amount = $availableBalance;
        $tranx_id = Helper::generateTranxNumber(10);
        //$paymentResponse = $BankApiController->payOutPaystack($availableBalance, $vendor_wallet_id, $destinationBankCode, $destinationAccountNumber);
        $destinationBankCode = $BankApiController->get_bank_code($cbn_code);
        $payVendor = $BankApiController->payOutSparkle($amount, $destinationBankCode, $destinationAccountNumber, $tranx_id);
        if($payVendor['response'] == 'success') {
            $debit_amount = $amount;
            // Debit Vendors Wallet with the amount manually
            $debit_vendor = $BankApiController->debit_wallet($vendor_wallet_id, $debit_amount);
            // Update vendor payout table
            $vendorPayout = VendorPayouts::create([
                'vendor_id' => $vendor_id,
                'amount' => $amount
            ]);

            $response = array(
                'responseCode' => "00",
                'message' => 'Payout was successful'
            );
            echo json_encode($response);
        }
        else {
            $response = array(
                'responseCode' => "11",
                'message' => 'Payout could not be done. Please ensure there is an amount in your wallet.'
            );
            echo json_encode($response);
        }
    }

    public function verifyAccount(Request $request)
    {
        $BankApiController = new BankApiController;
        $response = $BankApiController->verifyAccountPaystack($request->account_no, $request->verify_bank);
        return $response;
    }

    public function changePassword(Request $request)
    {
        $vendor_id = Auth::user()->vendor_id;
        $oldPassword = $request->oldPassword;
        $newPasswordHash = Hash::make($request->newPassword);
        $getPassword = DB::table('vendors')
            ->where('vendor_id', $vendor_id)
            ->get();
        if (Hash::check($oldPassword, $getPassword[0]->password)) {
            $changePassword = DB::table('vendors')
                ->where('vendor_id', $vendor_id)
                ->update([
                    'password' => $newPasswordHash
                    ]);
            
            if ($changePassword) {
                return [
                    'responseCode' => '00',
                    'message' => 'successful'
                ];
            }
            else {
                return [
                    'responseCode' => '11',
                    'message' => 'failed'
                ];
            }
        }
        else {
            return [
                'responseCode' => '11',
                'message' => 'Invalid Old Password'
            ];
        }

    }

    public function resetPassword(Request $request)
    {
        $password = Helper::generateTranxNumber(6);
        $passwordHash = Hash::make($password);
        $email = $request->email;
        if ( DB::table('vendors')->where('contact_email', $email)->exists()) {
            $changePassword = DB::table('vendors')
                        ->where('contact_email', $email)
                        ->update([
                            'password' => $passwordHash
                            ]);
            $query = DB::table('vendors')
            ->where('contact_email', $email)
            ->get();
            $name = $query[0]->contact_name;
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
                'message' => 'Email does not exist!'
            ];
        }
    }

    public function fetchBank(Request $request)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
        ])->get('https://api.getwallets.co/v1/banks');
        $bank_list = $response['data'];
    return view('admin.vendor', compact('bank_list'));
    }

    public function fetchUserBank(Request $request)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer sk_live_618941acc1402c0d6ed63527618941acc1402c0d6ed63528'
        ])->get('https://api.getwallets.co/v1/banks');
        $bank_list = $response['data'];
    return $bank_list;
    }

    public function verifyPayment(Request $request)
    {
        $query = DB::table('pos_transactions')
            ->where('transaction_id', $request->tranxId)
            ->first();

            if ($query->status == 'Paid') {
                return [
                    'responseCode' => '00',
                    'message' => 'Payment Successful'
                ];
            }
            else {
                return [
                    'responseCode' => '11',
                    'message' => 'Payment Failed'
                ];
            } 
    }
    

    public function generateCashoutOtp()
    {
        $vendor_id = Auth::user()->vendor_id;
        $pin = Helper::generateNumber(4);
        $pinHash = sha1($pin);
        $email = Auth::user()->contact_email;
        //$email = 'jonathan.o@landmarkafrica.com';
        // Update Cash out pin table
        $query = CashOutPin::create([
            'vendor_id' => $vendor_id,
            'pin' => $pinHash
        ]);
        MailController::sendCashOutPinEmail(Auth::user()->contact_name, $email, $pin);
     
            return [
                'responseCode' => '00',
                'message' => 'Pin Sent'
            ];
    }


// All codes in this function are no longer valid and contain a lot of errors and invalid values
    public function processGiftCardPayment(Request $request)
    {
        // Check if gift card is valid.
        if (DB::table('gift_cards')
            ->where('card_no', $request->cardNo)
            ->doesntExist()
            ) {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Invalid Gift card."
                    ));
                    exit();
            }
        //Get card amount funded
        $gift_card = DB::table('gift_cards')
            ->where('card_no', $request->cardNo)
            ->first();
        $card_pin = $gift_card->card_pin;
        $amount_funded = $gift_card->funded_amount;
        // Get total amount spent
        $total_spend = DB::table('gift_card_spends')
        ->where('card_no', $request->cardNo)
        ->sum('amount_spent');
        $userBalance = $amount_funded - $total_spend;
        $landmark_gift_card_pool_account_no = '';
       
        $pinHash = sha1($request->pin);
        $transaction_id = $request->tranxId;
        $senderAccount = $landmark_gift_card_pool_account_no;
        $citizen_id = $request->cardNo;
        $receiverAccount = Auth::user()->vnuban;
        $receiver_citizen_id = Auth::user()->vendor_id;;
        $cashierId = $request->cashierId;
        $amount = $request->amount;
        $senderName = $request->cardNo;
       
        
        // Get vendors loyalty discount and sales rent percentages
        $vendor_loyalty_discount_percent = Auth::user()->loyalty_discount / 100;
        $vendor_sales_rent_percent = Auth::user()->sales_rent / 100;

        $processingFee = $amount * (0 / 100);
        $landmark_sales_rent_amount = $amount * $vendor_sales_rent_percent;
        $vendor_tranx_amount = $amount - $landmark_sales_rent_amount - $processingFee;
        $vendor_earned_amount = $vendor_tranx_amount - ($vendor_tranx_amount * $vendor_loyalty_discount_percent);
        $landmark_reward_bank_amount = $amount - $vendor_earned_amount - $landmark_sales_rent_amount;
        
        $BankApiController = new BankApiController;
        $fixedAccount = $BankApiController->fixedAccountsPaystack();
        
        // Payment information for the landmark reward bank
        $landmark_reward_bank_acct_name = $fixedAccount['landmark_reward_bank_acct_name'];
        $landmark_reward_bank_acct_no = $fixedAccount['landmark_reward_bank_acct_no'];

         // Payment information for the landmark sales rent
         $landmark_sales_rent_acct_name = $fixedAccount['landmark_sales_rent_acct_name'];
         $landmark_sales_rent_acct_no = $fixedAccount['landmark_sales_rent_acct_no'];

        
        if ($pinHash == $card_pin) {

            // Check if gift card is activated
            if ($gift_card->status == '0') {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Gift card is not activated."
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
            $pay_vendor = $BankApiController->wallet_payment($senderAccount, $receiverAccount, $receiver_citizen_id, $vendor_earned_amount, $transaction_id);
            $pay_sales_rent = $BankApiController->wallet_payment($senderAccount, $landmark_sales_rent_acct_no, $landmark_sales_rent_acct_name, $landmark_sales_rent_amount, $transaction_id);
            $pay_landmark_reward_bank = $BankApiController->wallet_payment($senderAccount, $landmark_reward_bank_acct_no, $landmark_reward_bank_acct_name, $landmark_reward_bank_amount, $transaction_id);
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
                    'status' => 'Paid'
                ]);
                // Update cash_accounts table for user
            $cashAccount = CashAccount::create([
                    'transaction_id' => $transaction_id,
                    'account_id' => $citizen_id,
                    'transaction_type' => 'Debit',
                    'amount' => $amount
                ]);

            // Update Gift card spends
            $giftCardSpend = GiftCardSpend::create([
                'transaction_id' => $transaction_id,
                'card_no' => $citizen_id,
                'amount_spent' => $amount
            ]);


                // Update the transactions payments table
                $transactions_payments = TransactionPayments::create([
                    'vendor_id' => $receiver_citizen_id,
                    'vnuban' => $receiverAccount,
                    'tranx_ref' => $transaction_id,
                    'payment_ref' => $pay_vendor['data']['unique_reference'], //Live server
                    //'payment_ref' => $pay_vendor['data']['payout_reference'], //test server
                    'cust_email' => Auth::user()->email,
                    'merchant_amount' => $vendor_earned_amount,
                    'fee' => $processingFee,
                    'amount_payable' => $amount,
                    'amount_paid' => $amount,
                    'status' => 'Paid',
                    'sales_rent' => $landmark_sales_rent_amount
                ]);
        
            
                if ($giftCardSpend && $transaction && $cashAccount && $postransaction && $transactions_payments) {
                    
                    $response = array(
                            "responseCode" => "00",
                            "status" => "success",
                            "message" => "Payment successful",
                            "tranx_id" => $transaction_id,
                            "amount_paid" => strval($amount),
                            "payment_reference" => $pay_vendor['data']['unique_reference']
                            //"payment_reference" => $pay_vendor['data']['payout_reference']
                        );
                        // Sending email to customer
                        //MailController::sendPaymentNotification($senderName, $senderAccount, Auth::user()->email, $amount, $data['storeName'], $receiverAccount, $transaction_id);
                        return json_encode($response);
                        
                }
                else {
                    return json_encode(
                        array(
                            "responseCode" => "11",
                            "message" => "Payment Failed"
                        ));
                }
            }
            else {
                // return json_encode(
                //     array(
                //         "responseCode" => "11",
                //         "message" => "Payment Failed"
                //     ));

                $response = array(
                    "responseCode" => "00",
                    "status" => "success",
                    "message" => "Payment successful",
                    "tranx_id" => $transaction_id,
                    "amount_paid" => strval($amount),
                    "payment_reference" => '233456'
                );

                return json_encode($response);
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

    public function getGiftCardBalance(Request $request)
    {
        // Check if gift card is valid.
        if (DB::table('gift_cards')
            ->where('card_no', $request->cardNo)
            ->doesntExist()
            ) {
                return json_encode(
                    array(
                        "responseCode" => "11",
                        "message" => "Invalid Gift card."
                    ));
                    exit();
            }

         //Get card amount funded
         $gift_card = DB::table('gift_cards')
                ->where('card_no', $request->cardNo)
                ->first();
            $amount_funded = $gift_card->funded_amount;
            // Get total amount spent
            $total_spend = DB::table('gift_card_spends')
            ->where('card_no', $request->cardNo)
            ->sum('amount_spent');
            $userBalance = $amount_funded - $total_spend;
            return json_encode(
                array(
                    "responseCode" => "00",
                    "availableBalance" =>  $userBalance
                ));
    }

}
