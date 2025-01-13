<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Shetabit\Payment\Facade\Payment;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_initiate_payment_redirects_to_payment_gateway()
    {
        // شبیه‌سازی درخواست به درگاه زیبال
        Http::fake([
            'https://gateway.zibal.ir/v1/request' => Http::response([
                "trackId" => '123456789',
                'result' => 100,
                'message' => 'succes'
            ], 200),
        ]);

        // ساخت یک کاربر برای تست
        $user = User::factory()->create();
        $this->actingAs($user);

        // ارسال درخواست پرداخت به روت 'payment.purchase'
        $response = $this->post(route('payment.purchase'), [
            'book_id' => 1,
            'amount' => 10000,
            'user_id' => $user->id,
        ]);

        // بررسی ذخیره اطلاعات پرداخت در دیتابیس
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'amount' => 10000,
            'trackId' => '123456789', // باید trackId فیک که در Http::fake تعریف شده باشد ذخیره شود
            'payment_status' => 'pending',
            'payment_method' => 'zibal',
        ]);

        // اطمینان از ریدایرکت شدن به درگاه پرداخت با trackId فیک
        $response->assertRedirect('https://gateway.zibal.ir/start/123456789');
    }

    public function test_verify_payment_updates_status_to_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
    
        // Mock کردن کتابخانه Shetabit Multipay
        Payment::shouldReceive('amount')
            ->with(10000) // مقدار ریال معادل
            ->andReturnSelf();
    
        Payment::shouldReceive('transactionId')
            ->with('123456789') // مقدار درست
            ->andReturnSelf();
    
        Payment::shouldReceive('verify')
            ->andReturnTrue();
    
        // ایجاد رکورد اولیه در دیتابیس
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'amount' => 1000 * 10, // تبدیل به ریال
            'trackId' => '123456789',
            'payment_status' => 'pending',
            'payment_method' => 'zibal',
            'payment_date' => now(),
        ]);
    
        // بررسی دیتابیس قبل از درخواست
        $this->assertDatabaseHas('payments', [
            'trackId' => '123456789',
            'payment_status' => 'pending',
        ]);
    
        // ارسال درخواست به متد verifyPayment
        $response = $this->get(route('payment.verify', ['trackId' => $payment->trackId]));
    
        // بررسی وضعیت پرداخت در دیتابیس
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_status' => 'success',
        ]);
    
        // بررسی اینکه کاربر به صفحه درست هدایت شده است
        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', 'پرداخت شما با موفقیت انجام شد');
    }
    
}
