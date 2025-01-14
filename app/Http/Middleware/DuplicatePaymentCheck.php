<?php

namespace App\Http\Middleware;

use App\Models\Payment;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DuplicatePaymentCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // شناسه کاربر و محصول از درخواست
        $user_id = Auth::id();
        $book_id = $request->input('book_id');
        $existingSuccessfulPayment = Payment::where('user_id', $user_id)
            ->where('book_id', $book_id)
            ->where('payment_status', 'success')
            ->exists();
        if ($existingSuccessfulPayment) {
            // بازگرداندن خطا در صورت وجود پرداخت موفق قبلی
            return back()->with('error', 'You have already purchased this book.');
        }

        return $next($request);
    }
}
