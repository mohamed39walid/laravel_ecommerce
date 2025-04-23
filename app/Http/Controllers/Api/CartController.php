<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:products,id',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);
        Cart::add([
            'id' => $validated['id'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'options' => []
        ]);
        return response()->json(['message' => "item added to cart!"]);
    }

    public function index()
    {
        return response()->json([
            'items' => Cart::content(),
            'total' => Cart::total(),
            'count' => Cart::count(),
        ]);
    }


    public function update($rowId, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        Cart::update($rowId, $validated['quantity']);
        return response()->json(['message' => "Cart updated"]);
    }

    public function clear()
    {
        Cart::destroy();
        return response()->json(["message" => "Cart Cleared!"]);
    }
}
