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
        $userId = Auth::id();
        Cart::instance($userId);
    
        // Step 1: Clear the old session cart (prevent duplicates)
        Cart::destroy();
    
        // Step 2: Load items from DB into session
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
    
        // Return combined response
        return response()->json([
            'session_items' => Cart::content(),
            'db_items' => $cartItems,
            'total' => Cart::total(),
            'count' => Cart::count(),
        ]);
    }
    

    // Add item to cart (session + DB)
    public function add(Request $request)
    {
        $userId = Auth::id();
        Cart::instance($userId);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Add to session cart
        $item = Cart::add([
            'id' => $product->id,
            'name' => $product->name_en,
            'qty' => $request->quantity,
            'price' => (float) $product->price,
            'options' => [
                'image' => is_array($product->images) ? $product->images[0] ?? null : $product->images,
            ],
        ]);

        // ✅ Force session write
        session()->save();

        // Save to DB
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
    }


    // Update quantity
    public function update(Request $request, $rowId)
    {
        $this->restoreCartFromDatabase(); // Restore first ✅
    
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
    }
    
    

    // Remove item
    public function remove($rowId)
    {
        $this->restoreCartFromDatabase(); // ✅ Restore session cart from DB first
    
        $cartItem = Cart::get($rowId);
    
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found in session'], 404);
        }
    
        Cart::remove($rowId);
    
        // Remove from DB
        CartItem::where('user_id', Auth::id())
            ->where('product_id', $cartItem->id)
            ->delete();
    
        return response()->json(['message' => 'Item removed from cart (session + DB)']);
    }
    

    // Clear cart
    public function clear()
    {
        
        Cart::instance(Auth::id());

        Cart::destroy();

        // Clear DB cart
        CartItem::where('user_id', Auth::id())->delete();

        return response()->json(['message' => 'Cart cleared (session + DB)']);
    }
    
    
    
    

    
    private function restoreCartFromDatabase()
    {
    $userId = Auth::id();
    Cart::instance($userId);
    Cart::destroy(); // Clear old session

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