<?php
namespace App\Services;

use App\Http\Requests\InitialPaymentRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    // متغیرهای مربوط به URLهای درگاه زیبال
    private $zibalApiRequest;
    private $zibalApiStart;
    private $zibalMerchant;
    private $callbackUrl;
    private $zibal_API_Verify;

    public function __construct()
    {
        // مقداردهی اولیه URLهای درگاه زیبال از تنظیمات محیطی
        $this->zibalApiRequest = env('ZIBAL_API_REQUEST', 'https://gateway.zibal.ir/v1/request');
        $this->zibalApiStart = env('ZIBAL_API_START', 'https://gateway.zibal.ir/start/');
        $this->zibalMerchant = env('ZIBAL_MERCHANT', 'zibal');
        $this->callbackUrl = env('ZIBAL_CALLBACK_URL', 'https://h00wen41.ir/payment/verify');
        $this->zibal_API_Verify = env('ZIBAL_VERIFY_URL', 'https://gateway.zibal.ir/v1/verify');
    }

    /**
     * متد برای آغاز فرآیند پرداخت و درخواست از درگاه زیبال
     * 
     * @param int $amount مبلغ پرداختی
     * @return array نتیجه درخواست شامل trackId یا پیام خطا
     */
    public function initiate($amount)
    {
        // ارسال درخواست به درگاه زیبال برای گرفتن trackId
        $response = Http::post($this->zibalApiRequest, [
            'merchant' => $this->zibalMerchant,
            'amount' => $amount,
            'callbackUrl' => $this->callbackUrl,
        ]);

        // ثبت لاگ پاسخ دریافتی
        Log::info('Zibal API Response', ['response' => $response->json()]);

        // بررسی موفقیت‌آمیز بودن پاسخ و وجود trackId
        if ($response->successful() && isset($response['trackId'])) {
            return [
                'success' => true,
                'trackId' => $response['trackId'],
            ];
        }

        // در صورت عدم موفقیت، بازگشت خطا
        return [
            'success' => false,
            'message' => $response->json()['message'] ?? 'Unknown error',
        ];
    }

    /**
     * متد برای تایید پرداخت با استفاده از trackId
     * 
     * @param string $trackId شناسه پیگیری پرداخت
     * @return mixed پاسخ تایید پرداخت از درگاه زیبال
     */
    public function verify($trackId)
    {
        // ارسال درخواست به درگاه زیبال برای تایید پرداخت
        $response = Http::post($this->zibal_API_Verify, [
            'merchant' => $this->zibalMerchant,
            'trackId' => $trackId
        ]);

        // ثبت لاگ پاسخ دریافتی از API
        Log::info('API Verify Response', ['response' => $response->json()]);

        return $response;
    }

    /**
     * متد برای دریافت URL درخواست پرداخت زیبال
     * 
     * @return string URL درخواست پرداخت زیبال
     */
    public function getZibalApiRequest()
    {
        return $this->zibalApiRequest;
    }

    /**
     * متد برای دریافت URL شروع پرداخت زیبال
     * 
     * @return string URL شروع پرداخت زیبال
     */
    public function getZibalApiStart()
    {
        return $this->zibalApiStart;
    }

    /**
     * متد برای دریافت شناسه مرچنت زیبال
     * 
     * @return string شناسه مرچنت زیبال
     */
    public function getZibalMerchant()
    {
        return $this->zibalMerchant;
    }

    /**
     * متد برای دریافت URL کال‌بک زیبال
     * 
     * @return string URL کال‌بک زیبال
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }
}
