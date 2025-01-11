<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Shetabit\Multipay\Invoice;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'user_id' => 'required|numeric',
            'book_id' => 'required|numeric',
        ]);

        // مبلغ پرداختی به ریال (زیبال به ریال نیاز دارد)
        $amount = $request->amount * 10;

        $invoice = new Invoice();
        $invoice->amount($amount);
        $invoice->via('zibal');
        $invoice->detail([
            'transaction_id' => $request->transaction_id,
            'amount' => $amount,
        ]);

        try {
            $response = Http::post('https://api.zibal.ir/v1/request', [
                'merchant' => 'zibal',
                'callbackUrl' => 'https://h00wen41.ir/dashboard',
                'amount' => $amount,
            ]);

            Payment::create([
                'user_id' => $request->user_id,
                'transaction_id' => $response['trackId'],
                'payment_status' => 'pending',
                'payment_method' => 'zibal',
                'payment_date' => now(),
                'amount' => $amount,
            ]);
            return $this->verifyPayment($response);
        } catch (\Exception $e) {
            return back()->with('error', 'خطایی رخ داده است.');
        }
    }

    public function verifyPayment($response)
    {
dd( $response->trackId);
        return redirect()->away('https://gateway.zibal.ir/start/' . $response->trackId);
        $response = Http::post('https://api.zibal.ir/v1/verify', [
            'merchant' => 'zibal',
            'trackId' => $response->trackId,
        ]);

       
    
}
}
