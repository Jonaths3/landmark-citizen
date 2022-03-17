<?php

namespace App\Http\Controllers;
use App\Mail\SignupEmail;
use App\Mail\ResetPinEmail;
use App\Mail\ResetCashOutPinEmail;
use App\Mail\ResetPasswordEmail;
use App\Mail\VendorRegisterEmail;
use App\Mail\PaymentConfirmationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public static function sendSignupEmail($name, $email, $verification_code, $pin){
        $data = [
            'name' => $name,
            'verification_code' => $verification_code,
            'pin' => $pin
        ];
        Mail::to($email)->send(new SignupEmail($data));
    }

    public static function sendPinEmail($name, $email, $pin){
        $data = [
            'name' => $name,
            'pin' => $pin
        ];
        Mail::to($email)->send(new ResetPinEmail($data));
    }

    public static function sendPasswordEmail($name, $email, $password){
        $data = [
            'name' => $name,
            'password' => $password
        ];
        Mail::to($email)->send(new ResetPasswordEmail($data));
    }

    public static function sendCashOutPinEmail($name, $email, $pin){
        $data = [
            'name' => $name,
            'pin' => $pin
        ];
        Mail::to($email)->send(new ResetCashOutPinEmail($data));
    }

    public static function sendVendorRegisterEmail($name, $email, $pin, $password, $store_name){
        $data = [
            'name' => $name,
            'pin' => $pin,
            'password' => $password,
            'store_name' => $store_name,
            'email' => $email
        ];
        Mail::to($email)->send(new VendorRegisterEmail($data));
    }

    public static function sendPaymentNotification($senderName, $senderAccount, $email, $amount, $receiverName, $receiverAccount, $tranx_id)
    {
        $data = [
            'sender_name' => $senderName,
            'sender_acount' => $senderAccount,
            'amount' => $amount,
            'receiver_name' => $receiverName,
            'receiver_account' => $receiverAccount,
            'tranx_id' => $tranx_id
        ];
        Mail::to($email)->send(new PaymentConfirmationEmail($data));
    }
}
