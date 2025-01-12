<?php

namespace App\Http\Controllers;

use App\Events\SuccessfulPayment;
use App\Http\Requests\InitialPaymentRequest;
use App\Models\Payment as userpayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class PaymentController extends Controller
{

    public function initiatePayment(InitialPaymentRequest $request)
    {
        // مبلغ به ریال تبدیل می‌شود
        $amount = $request->amount * 10;

        // ایجاد فاکتور برای درخواست پرداخت
        $invoice = $this->createInvoice($amount);
        $transactionId = null;

        //صدا زدن درگاه پرداخت
        try {
            Payment::purchase(
                $invoice,
                function ($driver, $transId) use (&$transactionId, $request) {
                    $transactionId = $transId;
                    $this->storePayment($request, $transactionId); // ثبت اطلاعات در دیتابیس
                }
            );

            if (empty($transactionId)) {
                return redirect()->route('books.index')->with('error', 'مشکلی در ارتباط با درگاه پرداخت رخ داده است.');
            }

            return redirect('https://gateway.zibal.ir/start/' . $transactionId);

        } catch (\Exception $exception) {
            Log::error('Payment initiation error: ' . $exception->getMessage());
            return redirect()->route('books.index')->with('error', 'خطایی در ایجاد تراکنش رخ داده است.');
        }
    }



    public function verifyPayment(Request $request)
    {
        $transaction_id = $request->trackId;
        try {
            // شروع تراکنش
            DB::beginTransaction();

            // دریافت اطلاعات پرداخت
            $payment = userpayment::where('transaction_id', $transaction_id)->firstOrFail();
            // تایید پرداخت
            Payment::amount($payment->amount)->transactionId($transaction_id)->verify();
            // بروزرسانی وضعیت پرداخت
            $payment->update(['payment_status' => 'success']);

            // پایان تراکنش
            DB::commit();
            // اعلان پرداخت موفق
            event(new SuccessfulPayment($payment));

            return redirect()->route('books.index')->with('success', 'پرداخت شما با موفقیت انجام شد');

        } catch (InvalidPaymentException $exception) {
            // بازگرداندن تراکنش در صورت خطا
            DB::rollBack();
            return redirect()->route('books.index')->with('error', 'پرداخت شما ناموفق بود');
        } catch (\Exception $exception) {
            // بازگرداندن تراکنش در صورت خطا
            DB::rollBack();
            return redirect()->route('books.index')->with('error', 'خطایی در پردازش پرداخت شما رخ داده است');
        }
    }


    private function createInvoice($amount)
    {
        // ایجاد فاکتور برای درخواست پرداخت
        $invoice = new Invoice();
        $invoice->amount($amount);
        $invoice->via('zibal');

        return $invoice;
    }


    private function storePayment(Request $request, $transactionId)
    {
        userpayment::create([
            'user_id' => $request->user_id,
            'book_id' => $request->book_id,
            'amount' => $request->amount,
            'transaction_id' => $transactionId,
            'payment_status' => 'pending',
            'payment_method' => 'zibal',
            'payment_date' => now(),
        ]);
    }
}