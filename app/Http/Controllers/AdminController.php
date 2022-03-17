<?php

namespace App\Http\Controllers;
use App\Models\Transactions;
use App\Models\RewardTips;
use App\Models\Faq;
use App\Models\Notification;
use App\Models\GiftCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use App\Helper\Helper;
class AdminController extends Controller
{
    public function index()
    {
        $vendors = DB::table('vendors')
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.home', compact('vendors'));
    }

    public function transactionSummary(Request $request)
    {
        $vendor_id = $request->vendorId;
        $date_range = $request->dateRange;
        $dateExplode = explode('-', $date_range);
        $dateFrom = date("Y-m-d", strtotime($dateExplode[0]));
        $dateTo = date("Y-m-d", strtotime($dateExplode[1]));

        if ($vendor_id == 'all') {
            // Calculating the number of transactions
            $noOfTranx = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->count();

            // Calculating the total transaction value
            $totalTranxValue = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->sum('amount_payable');

            // Calculating the total vendor earnings
            $totalVendorEarnings = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->sum('merchant_amount'); 

            // Calculating the total sales rent
            $totalSalesRent = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->sum('sales_rent'); 

             // Calculating the total vendor payout
             $totalVendorPayout = DB::table('vendor_payouts')
             ->whereDate('created_at', '>=',  $dateFrom)
             ->whereDate('created_at', '<=', $dateTo)
             ->sum('amount'); 
        }
        else {
            // Calculating the number of transactions
            $noOfTranx = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->where('vendor_id', $vendor_id)
            ->count();

            // Calculating the total transaction value
            $totalTranxValue = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->where('vendor_id', $vendor_id)
            ->sum('amount_payable');

            // Calculating the total vendor earnings
            $totalVendorEarnings = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->where('vendor_id', $vendor_id)
            ->sum('merchant_amount');

            // Calculating the total sales rent
            $totalSalesRent = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->where('vendor_id', $vendor_id)
            ->sum('sales_rent'); 

             // Calculating the total vendor payout
             $totalVendorPayout = DB::table('vendor_payouts')
             ->whereDate('created_at', '>=',  $dateFrom)
             ->whereDate('created_at', '<=', $dateTo)
             ->where('vendor_id', $vendor_id)
             ->sum('amount'); 
        }
        $response = array(
            'noOfTranx' => $noOfTranx,
            "totalTranxValue" => $totalTranxValue,
            "totalVendorEarnings" => $totalVendorEarnings,
            "totalSalesRent" => $totalSalesRent,
            'totalVendorPayout' => $totalVendorPayout
        );
        return view('admin.ajax_files.transaction_summary', compact('response'));
    }

    public function transactionTable(Request $request)
    {
        $vendor_id = $request->vendorId;
        $date_range = $request->dateRange;
        $dateExplode = explode('-', $date_range);
        $dateFrom = date("Y-m-d", strtotime($dateExplode[0]));
        $dateTo = date("Y-m-d", strtotime($dateExplode[1]));
        
        if ($vendor_id == 'all') {

            $users = DB::table('transaction_payments')
            ->whereDate('transaction_payments.created_at', '>=',  $dateFrom)
            ->whereDate('transaction_payments.created_at', '<=', $dateTo)
            ->where('transaction_payments.status', 'Paid')
            ->join('transactions', 'transaction_payments.tranx_ref', '=', 'transactions.transaction_id')
            ->join('users', 'transactions.from', '=', 'users.citizen_id')
            ->join('vendors', 'transaction_payments.vendor_id', '=', 'vendors.vendor_id')
            ->select('transaction_payments.tranx_ref', 'vendors.store_name', 'users.first_name', 'users.email', 'users.phone', 'transaction_payments.amount_payable', 'transactions.created_at', 'transaction_payments.status')
            ->get();
        }
        else {
            // Fetching user data
            $users = DB::table('transaction_payments')
            ->whereDate('transaction_payments.created_at', '>=',  $dateFrom)
            ->whereDate('transaction_payments.created_at', '<=', $dateTo)
            ->where('transaction_payments.status', 'Paid')
            ->where('transaction_payments.vendor_id', $vendor_id)
            ->join('transactions', 'transaction_payments.tranx_ref', '=', 'transactions.transaction_id')
            ->join('users', 'transactions.from', '=', 'users.citizen_id')
            ->join('vendors', 'transaction_payments.vendor_id', '=', 'vendors.vendor_id')
            ->select('transaction_payments.tranx_ref', 'vendors.store_name', 'users.first_name', 'users.email', 'users.phone', 'transaction_payments.amount_payable', 'transaction_payments.created_at')
            ->get();
        }
          $response = $users; 
        
          
    return DataTables::of($response)->make(true);
    
    }

    public function rewardSummary(Request $request)
    {
        $date_range = $request->dateRange;
        $dateExplode = explode('-', $date_range);
        $dateFrom = date("Y-m-d", strtotime($dateExplode[0]));
        $dateTo = date("Y-m-d", strtotime($dateExplode[1]));

            // Calculating the number of transactions
            $noOfTranx = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->count();

            // Calculating the total cashback earned
            $totalCashbackEarned = DB::table('rewards')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->sum('cashback_earned');

            // Calculating the total cashback redeemed
            $totalCashbackRedeemed = DB::table('redeemed_amounts')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->sum('redeemed_amount'); 

            // Calculating the total transaction value
            $totalTranxValue = DB::table('transaction_payments')
            ->whereDate('created_at', '>=',  $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->where('status', 'Paid')
            ->sum('amount_payable');
            $BankApiController = new BankApiController;
            $fixedAccount = $BankApiController->fixedAccountsPaystack();
             // Reward Bank Balance
             $vnubanNumber = $fixedAccount['landmark_reward_wallet_id'];
             
             $rewardBank = $BankApiController->walletBalance($vnubanNumber);
             //$rewardBank = $response['data']['available_balance'];
        
        $response = array(
            'noOfTranx' => $noOfTranx,
            "totalTranxValue" => $totalTranxValue,
            "totalCashbackEarned" => $totalCashbackEarned,
            "totalCashbackRedeemed" => $totalCashbackRedeemed,
            'rewardBank' => $rewardBank
        );
        return view('admin.ajax_files.reward_summary', compact('response'));
    }

    public function rewardTable(Request $request)
    {
        $date_range = $request->dateRange;
        $dateExplode = explode('-', $date_range);
        $dateFrom = date("Y-m-d", strtotime($dateExplode[0]));
        $dateTo = date("Y-m-d", strtotime($dateExplode[1]));
       
            $users = DB::table('transaction_payments')
            ->whereDate('transaction_payments.created_at', '>=',  $dateFrom)
            ->whereDate('transaction_payments.created_at', '<=', $dateTo)
            ->where('transaction_payments.status', 'Paid')
            ->join('transactions', 'transaction_payments.tranx_ref', '=', 'transactions.transaction_id')
            ->join('users', 'transactions.from', '=', 'users.citizen_id')
            ->join('vendors', 'transaction_payments.vendor_id', '=', 'vendors.vendor_id')
            ->join('rewards', 'transaction_payments.tranx_ref', '=', 'rewards.tranx_id')
            ->select('transaction_payments.tranx_ref', 'vendors.store_name', 'users.first_name', 'transaction_payments.amount_payable', 'transactions.created_at', 'rewards.point_earned', 'rewards.cashback_earned')
            ->get();
        
        
          $response = $users; 
        
          
    return DataTables::of($response)->make(true);
    
    }

    public function storeRewardTips(Request $request)
    {
        $query = RewardTips::create([
                    'tips' => $request->input('tips')
                ]);
        return 'success';
    }

    public function getRewardTips()
    {
       $query = DB::table('reward_tips')->get();
       return json_encode($query);
    }

    public function loyaltySettings()
    {
       $query = DB::table('loyalty_classes')->get();
       return view('admin.loyalty', compact('query'));
    }

    public function updateLoyaltySettings(Request $request)
    {
       $id = $request->id;
       $loyalty_class = $request->loyalty_class;
       $cashback = $request->cashback;
       $point = $request->point;
       $base_value = $request->base_value;
    //    Deleting records in the database
    DB::table('loyalty_classes')->delete();
    // Inserting new records
       for ($i=0; $i < count($loyalty_class); $i++) { 
           DB::table('loyalty_classes')->insert([
            'id' => $id[$i],
            'loyalty_class' => $loyalty_class[$i],
            'percentage_cashback' => $cashback[$i],
            'percentage_points' => $point[$i],
            'percentage_discount' => '',
            'min_point' => $base_value[$i]
        ]);
       }
       return 'success';
    }

    public function privacyPolicy()
    {
        $query = DB::table('privacy_policies')->get();
        return json_encode($query);
    }

    public function addFaq(Request $request)
    {
        $query = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        return 'success';
    }

    public function getFaq()
    {
        $faq_list = DB::table('faqs')->get();
        return view('admin.faq', compact('faq_list'));
    }

    public function showFaq(Request $request)
    {
        // Getting the list of vendors 
       $faqDetails = DB::table('faqs')
       ->where('id', $request->id)
       ->get();
        return $faqDetails;
    }

    public function updateFaq(Request $request)
    {
        // Getting the list of vendors 
       $faqDetails = DB::table('faqs')
       ->where('id', $request->id)
       ->update([
           'question' => $request->question,
           'answer' => $request->answer
       ]);
        return 'success';
    }

    public function deleteFaq(Request $request)
    {
        // Getting the list of vendors 
       $faqDetails = DB::table('faqs')
       ->where('id', $request->id)
       ->delete();
        return 'success';
    }

    public function getNotification()
    {
        $notification_list = DB::table('notifications')->get();
        $vendors = DB::table('vendors')
            ->orderBy('id', 'DESC')
            ->get();
        return view('admin.notification', compact('notification_list', 'vendors'));
    }

    public function addEvents(Request $request)
    {
        $query = Notification::create([
            'title' => $request->title,
            'message' => $request->event_details,
            'vendor_name' => $request->vendor,
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ]);

        return 'success';
    }

    public function showEvent(Request $request)
    {
        // Getting event details 
       $eventRow = DB::table('notifications')
       ->where('id', $request->id)
       ->get();
       foreach ($eventRow as $value) {
        //    get vendor name
            $vendor = DB::table('vendors')
            ->where('vendor_id',$value->vendor_name)
            ->first();
            $eventDetails[] = array(
                'title' => $value->title, 
                'message' => $value->message, 
                'vendor_name' => $vendor->store_name
            );
       }
        return $eventDetails;
    }

    public function deleteEvent(Request $request)
    {
       $deleteEvent = DB::table('notifications')
       ->where('id', $request->id)
       ->delete();
        return 'success';
    }

    public function generateGiftCards(Request $request)
    {
        $amount = $request->amount;
        $quantity = $request->quantity;
        $user = 'Jonathan';
        for ($i=0; $i < $quantity; $i++) { 
            $cardNo = Helper::generateNumber(10);
            $query = GiftCard::create([
                'card_no' => $cardNo,
                'status' => 'Inactive',
                'funded_amount' => $amount,
                'created_by' => $user,
            ]);
        }
        return 'success';
    }

    public function displayGiftCards(Request $request)
    {
        $query = DB::table('gift_cards')->get();
        $result = json_decode($query, true);
        return Datatables::of($result)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '<a href="javascript:void(0);" class="action-icon edit-vendor" data-bs-toggle="modal" data-bs-target="#edit-vendor" data-id="'.$row['id'].'"><i class="mdi mdi-eye"></i></a><a href="javascript:void(0);" class="action-icon delete-vendor" data-bs-toggle="modal" data-bs-target="#delete-warning-modal" data-id="'.$row['id'].'"> <i class="mdi mdi-delete"></i></a>';
              return $actionBtn;
          })
          ->rawColumns(['action'])
          ->make(true);
    }

}
