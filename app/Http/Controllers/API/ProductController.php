<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');

        if ($id) {
            $product = Product::find($id);
            if ($product) {
                return ResponseFormatter::success($product, 'Success get product data');
            } else {
                return ResponseFormatter::error(null, 'Failed get product data', 404);
            }
        }

        $product = Product::query();
        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($types) {
            $product->where('types', 'like', '%' . $types . '%');
        }
        if ($price_from) {
            $product->where('$price_from', '>=', '%' . $price_from . '%');
        }
        if ($price_to) {
            $product->where('$price_to', '<=', '%' . $price_to . '%');
        }
        if ($rate_from) {
            $product->where('$rate_from', '>=', '%' . $rate_from . '%');
        }
        if ($rate_to) {
            $product->where('$rate_to', '<=', '%' . $rate_to . '%');
        }

        return ResponseFormatter::success($product->paginate($limit), 'Success get list product');
    }
}
