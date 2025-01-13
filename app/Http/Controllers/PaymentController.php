<?php
namespace App\Http\Controllers;

use App\Http\Requests\InitialPaymentRequest;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Services\PaymentStatusHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $paymentService;
    private $paymentRepository;
    private $paymentStatusHandler;

    // سازنده برای تزریق وابستگی‌ها
    public function __construct(PaymentService $paymentService, PaymentRepository $paymentRepository, PaymentStatusHandler $paymentStatusHandler)
    {
        $this->paymentService = $paymentService;
        $this->paymentRepository = $paymentRepository;
        $this->paymentStatusHandler = $paymentStatusHandler;
    }

    /**
     * متد شروع فرآیند پرداخت
     * 
     * این متد با دریافت مبلغ از درخواست کاربر، به درگاه پرداخت ارسال می‌کند
     * و اگر پرداخت موفقیت‌آمیز بود، کاربر به درگاه هدایت می‌شود.
     * اگر پرداخت موفقیت‌آمیز نبود، خطا به کاربر نمایش داده می‌شود.
     * 
     * @param InitialPaymentRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiatePayment(InitialPaymentRequest $request)
    {
        // محاسبه مبلغ پرداختی و ارسال درخواست به درگاه
        $amount = ($request->amount) * 10;
        $response  = $this->paymentService->initiate($amount);
        
        // ثبت لاگ نتیجه پرداخت
        Log::info('Payment initiation result:', ['result' => $response]);
        
        // بررسی نتیجه و هدایت به درگاه پرداخت
        if ($response['success']) {
            // ذخیره اطلاعات پرداخت در پایگاه داده
            $this->paymentRepository->createPayment($amount, $response['trackId']);
            return redirect($this->paymentService->getZibalApiStart() . $response['trackId']);
        } else {
            // اگر پرداخت ناموفق بود، نمایش خطا
            Log::error('Payment initiation failed.');
            return response()->json(['error' => $response['message']], 500);
        }
    }

    /**
     * متد تایید یا رد وضعیت پرداخت
     * 
     * این متد برای تایید یا رد پرداخت بر اساس وضعیت دریافتی از درگاه استفاده می‌شود.
     * اگر پرداخت موفق بود، وضعیت موفقیت‌آمیز پردازش می‌شود.
     * اگر پرداخت ناموفق بود، وضعیت شکست پردازش می‌شود.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyPayment(Request $request)
    {
        // دریافت اطلاعات وضعیت پرداخت
        $trackId = $request->input('trackId');
        $success = $request->input('success');
        $status = $request->input('status');

        // اگر پرداخت موفقیت‌آمیز بود
        if ($success == '1') {
            $this->paymentService->verify($trackId);

            // هندل کردن پرداخت موفق
            return $this->paymentStatusHandler->SuccessfulPayment($status, $trackId);
        }

        // اگر پرداخت ناموفق بود
        return $this->paymentStatusHandler->FailedPayment($status);
    }
}
