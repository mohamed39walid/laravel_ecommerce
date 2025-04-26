<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\CartItem;

class CartController extends Controller
{
    // View all cart items
    public function index()
    {
        try {
            $userId = Auth::id();
            Cart::instance($userId);

            Cart::destroy(); // Clear session cart

            $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                if ($product) {
                    Cart::add([
                        'id' => $product->id,
                        'name' => $product->name_en,
                        'qty' => $cartItem->quantity,
                        'price' => (float) $product->price,
                        'options' => [
                            'image' => is_array($product->images)
                                ? ($product->images[0] ?? null)
                                : $product->images,
                        ],
                    ]);
                }
            }

            return response()->json([
                'session_items' => Cart::content(),
                'db_items' => $cartItems,
                'total' => Cart::total(),
                'count' => Cart::count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Add item to cart (session + DB)
    public function add(Request $request)
    {
        try {
            $userId = Auth::id();
            Cart::instance($userId);

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $product = Product::findOrFail($request->product_id);

            $item = Cart::add([
                'id' => $product->id,
                'name' => $product->name_en,
                'qty' => $request->quantity,
                'price' => (float) $product->price,
                'options' => [
                    'image' => is_array($product->images) ? $product->images[0] ?? null : $product->images,
                ],
            ]);

            session()->save(); // Force write session ✅

            CartItem::updateOrCreate([
                'user_id' => $userId,
                'product_id' => $product->id,
            ], [
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'message' => 'Item added to cart (session + DB)',
                'item' => $item,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add item to cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Update quantity
    public function update(Request $request, $rowId)
    {
        try {
            $this->restoreCartFromDatabase();

            $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem = Cart::get($rowId);

            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found in session'], 404);
            }

            Cart::update($rowId, $request->quantity);

            CartItem::where('user_id', Auth::id())
                ->where('product_id', $cartItem->id)
                ->update(['quantity' => $request->quantity]);

            return response()->json(['message' => 'Cart item updated']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart item.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Remove item
    public function remove($rowId)
    {
        try {
            $this->restoreCartFromDatabase();

            $cartItem = Cart::get($rowId);

            if (!$cartItem) {
                return response()->json(['message' => 'Cart item not found in session'], 404);
            }

            Cart::remove($rowId);

            CartItem::where('user_id', Auth::id())
                ->where('product_id', $cartItem->id)
                ->delete();

            return response()->json(['message' => 'Item removed from cart (session + DB)']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove cart item.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Clear cart
    public function clear()
    {
        try {
            Cart::instance(Auth::id());
            Cart::destroy();
            CartItem::where('user_id', Auth::id())->delete();

            return response()->json(['message' => 'Cart cleared (session + DB)']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to clear cart.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Helper to restore cart from DB
    private function restoreCartFromDatabase()
    {
        $userId = Auth::id();
        Cart::instance($userId);
        Cart::destroy();

        $cartItems = CartItem::with('product')->where('user_id', $userId)->get();

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            if ($product) {
                Cart::add([
                    'id' => $product->id,
                    'name' => $product->name_en,
                    'qty' => $cartItem->quantity,
                    'price' => (float) $product->price,
                    'options' => [
                        'image' => is_array($product->images)
                            ? ($product->images[0] ?? null)
                            : $product->images,
                    ],
                ]);
            }
        }
    }
}
