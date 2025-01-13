<?php
namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;

class PaymentStatusHandler
{
    /**
     * متد برای هندل کردن وضعیت پرداخت موفق
     * 
     * @param int $status وضعیت پرداخت (مقدار عددی)
     * @param string $trackId شناسه پیگیری پرداخت
     * @return \Illuminate\Http\RedirectResponse
     */
    public function SuccessfulPayment($status, $trackId)
    {
        // پیدا کردن پرداخت با استفاده از trackId
        $payment = Payment::where('trackId', $trackId)->first();
        
        // اگر پرداخت پیدا شود
        if ($payment) {
            // تبدیل مقدار عددی وضعیت به enum
            $statusEnum = PaymentStatus::from($status);

            // ذخیره متن وضعیت پرداخت به جای مقدار عددی
            $payment->payment_status = $statusEnum->getText();
            $payment->save();
        }

        // هندل کردن وضعیت پرداخت با استفاده از متد handlePaymentStatus
        return $this->handlePaymentStatus($status);
    }

    /**
     * متد برای هندل کردن وضعیت پرداخت ناموفق
     * 
     * @param int $status وضعیت پرداخت (مقدار عددی)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function FailedPayment($status)
    {
        // هندل کردن وضعیت پرداخت ناموفق با استفاده از متد handlePaymentStatus
        return $this->handlePaymentStatus($status);
    }

    /**
     * متد برای هندل کردن وضعیت‌های مختلف پرداخت
     * 
     * @param int $status وضعیت پرداخت (مقدار عددی)
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handlePaymentStatus($status)
    {
        // بررسی وضعیت پرداخت با استفاده از enum
        switch (PaymentStatus::from($status)) {
            case PaymentStatus::SUCCESS_CONFIRMED:
                // اگر وضعیت پرداخت تایید شده باشد
                return redirect('/books')->with('success', 'Payment successful and confirmed.');
            case PaymentStatus::SUCCESS_UNCONFIRMED:
                // اگر وضعیت پرداخت موفق اما تایید نشده باشد
                return redirect('/books')->withErrors(['payment' => 'Payment successful but not yet confirmed.']);
            case PaymentStatus::CANCELED_BY_USER:
                // اگر پرداخت توسط کاربر لغو شده باشد
                return redirect('/books')->withErrors(['payment' => 'Payment canceled by the user.']);
            case PaymentStatus::PENDING:
                // اگر وضعیت پرداخت در حال انتظار باشد
                return redirect('/books')->withErrors(['payment' => 'Payment is pending.']);
            case PaymentStatus::INTERNAL_ERROR:
                // اگر خطای داخلی در سیستم درگاه پرداخت رخ داده باشد
                return redirect('/books')->withErrors(['payment' => 'An internal error occurred.']);
            default:
                // در صورتی که وضعیت پرداخت ناشناخته باشد
                return redirect('/books')->withErrors(['payment' => 'Unknown payment status.']);
        }
    }
}
