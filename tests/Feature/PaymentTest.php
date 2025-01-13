<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
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
        Http::fake(['https://gateway.zibal.ir/v1/request' => Http::response([
                "trackId" => 'testTrackId1234',
                'result' => 100,
                'message' => 'success'
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
            'amount' => 10000 * 10,
            'trackId' => 'testTrackId1234', // باید trackId فیک که در Http::fake تعریف شده باشد ذخیره شود
            'payment_status' => 'pending',
            'payment_method' => 'zibal',
        ]);

        // اطمینان از ریدایرکت شدن به درگاه پرداخت با trackId فیک
        $response->assertRedirect('https://gateway.zibal.ir/start/testTrackId1234');
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
        ->with('testTrackId1234') // مقدار درست
            ->andReturnSelf();
    
        Payment::shouldReceive('verify')
            ->andReturnTrue();
    
        // ایجاد رکورد اولیه در دیتابیس
        $payment = \App\Models\Payment::create([
            'user_id' => $user->id,
            'amount' => 10000,
            'trackId' => 'testTrackId1234',
            'payment_status' => 'pending',
            'payment_method' => 'zibal',
            'payment_date' => now(),
        ]);
    
        // بررسی دیتابیس قبل از درخواست
        $this->assertDatabaseHas('payments', [
            'trackId' => 'testTrackId1234',
            'payment_status' => 'pending',
        ]);

        // ارسال درخواست به متد verifyPayment
        $response = $this->get(route('payment.verify', [
            'trackId' => $payment->trackId,
            'success' => '1', // حتماً مقدار موفقیت ارسال شود
            'status' => PaymentStatus::SUCCESS_CONFIRMED->value, // ارسال وضعیت صحیح
        ]));
        
        // بررسی وضعیت پرداخت در دیتابیس
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_status' => 'success',
        ]);
    
        // بررسی اینکه کاربر به صفحه درست هدایت شده است
        $response->assertRedirect(route('books.index'));
 
    }
    

    public function test_verify_payment_updates_status_to_failed()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    // Mock کردن کتابخانه Shetabit Multipay
    Payment::shouldReceive('amount')
        ->with(10000)
        ->andReturnSelf();

    Payment::shouldReceive('transactionId')
        ->with('testTrackId1234')
        ->andReturnSelf();

    Payment::shouldReceive('verify')
        ->andThrow(new \Exception('Payment verification failed'));

    // ایجاد رکورد اولیه در دیتابیس
    $payment = \App\Models\Payment::create([
        'user_id' => $user->id,
        'amount' => 10000,
        'trackId' => 'testTrackId1234',
        'payment_status' => 'pending',
        'payment_method' => 'zibal',
        'payment_date' => now(),
    ]);

    // ارسال درخواست به متد verifyPayment
    $response = $this->get(route('payment.verify', [
        'trackId' => $payment->trackId,
        'success' => '0', // پرداخت ناموفق
        'status' => PaymentStatus::CANCELED_BY_USER->value,
    ]));

    // بررسی وضعیت پرداخت در دیتابیس
    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'payment_status' => 'failed',
    ]);

    // بررسی اینکه کاربر به صفحه مناسب هدایت شده است
    $response->assertRedirect(route('books.index'));
    $response->assertSessionHasErrors(['payment' => 'Payment canceled by the user.']);
}

}
