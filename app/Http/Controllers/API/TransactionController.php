<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
}
