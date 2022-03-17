<?php

namespace App\Http\Controllers;

use App\Models\VendorPayouts;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BankApiController;
use Illuminate\Support\Facades\DB;

class SparkleWebhookController extends Controller
{
    public function sparkleWebhook(Request $request){
        $validator = Validator::make($request->all(),['referenceId' => 'required','status' => 'required','transactionFee'=>'required','transactionDate'=>'required']);

        if ($validator->fails()) {
           $msg = [
                'message' =>  $validator->errors()
           ];
           response()->json($msg, 403);
        }
        $ref = $request['referenceId'];
        try {
           DB::beginTransaction();
           $lastTran =  VendorPayouts::where('ref',$ref)->first();
           if($lastTran){
               if($request['status']=='Successful'){
                   $lastTran->status = 1;
                   $lastTran->time_out = Carbon::now();
                   $lastTran->transaction_fee=$request['transactionFee'];
                   $lastTran->transaction_date=$request['transactionDate'];
                   $lastTran->save();
                   $debit = $this->debitUponSucçcessfullPayout($lastTran->wallet_id,$lastTran->amount);
               }
               $msg=["message"=>'Transaction status updated'];

           }
           DB::commit();
           return response()->json($msg, 200);
        } catch (\Exception $th) {
            DB::rollBack();
            $msg=["message"=>'Transaction reference not found'];
           return  response()->json($msg, 400);
        }
    }
    public function debitUponSucçcessfullPayout($vendor_wallet_id,$debit_amount){

        $BankApiController = new BankApiController;
        $debit_vendor = $BankApiController->debitWallet($vendor_wallet_id, 'NGN', $debit_amount);
        // dd($debit_vendor);
        return $debit_vendor;

    }
}
