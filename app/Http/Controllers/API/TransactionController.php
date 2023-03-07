<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $product_id = $request->input('product_id');
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['product', 'user'])->find($id);
            if ($transaction) {
                return ResponseFormatter::success($transaction, 'Success get transaction data');
            } else {
                return ResponseFormatter::error(null, 'Failed get transaction data', 404);
            }
        }

        $transaction = Transaction::with(['product', 'user'])->where('user_id', Auth::user()->id);
        if ($product_id) {
            $transaction->where('product_id', $product_id);
        }
        if ($status) {
            $transaction->where('status', $status);
        }
        return ResponseFormatter::success($transaction->paginate($limit), 'Success get list transaction');
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());

        return ResponseFormatter::success($transaction, 'Transaction data updated');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:product,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required',
            'total' => 'required',
            'status' => 'required',
        ]);

        $transaction = Transaction::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'status' => $request->status,
            'payment_url' => '',
        ]);

        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$clientKey = config('services.midtrans.clientKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $transaction = Transaction::with(['product', 'user'])->find($transaction->id);

        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => (int) $transaction->total,
            ],
            'customer_details' => [
                'first_name' => $transaction->user->name,
                'email' => $transaction->user->email,
            ],
            'enabled_payments' => [
                'gopay', 'bank_transfer'
            ],
            'vtweb' => []
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            return ResponseFormatter::success($transaction, 'Transaction success');
        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 'Transaction failed');
        }
    }
}
