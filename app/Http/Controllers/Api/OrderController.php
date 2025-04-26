<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Http\Resources\OrderResource;
use Stripe\Stripe;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'payment_method' => 'required|in:stripe,paypal',
                'city' => 'required|string',
                'address' => 'required|string',
                'building_number' => 'required|string'
            ]);

            Cart::instance(Auth::id());

            if (Cart::content()->isEmpty()) {
                $storedItems = CartItem::with('product')->where('user_id', Auth::id())->get();
                foreach ($storedItems as $item) {
                    if ($item->product) {
                        Cart::add([
                            'id' => $item->product_id,
                            'name' => $item->product->name_en,
                            'qty' => $item->quantity,
                            'price' => (float) $item->product->price,
                            'options' => [
                                'image' => is_array($item->product->images)
                                    ? $item->product->images[0] ?? null
                                    : $item->product->images,
                            ]
                        ]);
                    }
                }
            }

            $cart = Cart::content();

            if ($cart->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], 400);
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => strtoupper(Str::random(10)),
                'payment_method' => $request->payment_method,
                'payment_status' => 'not_paid',
                'status' => 'pending',
                'city' => $request->city,
                'address' => $request->address,
                'building_number' => $request->building_number
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->id,
                    'quantity' => $item->qty,
                    'price' => $item->price
                ]);
            }

            Cart::destroy();
            CartItem::where('user_id', Auth::id())->delete();

            return response()->json([
                'message' => 'Order placed successfully.',
                'order' => new OrderResource($order->load('items.product'))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to place order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            return OrderResource::collection(
                Order::with('items.product')->where('user_id', Auth::id())->get()
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve orders.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with('items.product')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return new OrderResource($order);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve order details.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,shipped,delivered'
            ]);

            $order = Order::findOrFail($id);
            $order->status = $request->status;
            $order->save();

            return response()->json([
                'message' => 'Order status updated successfully.',
                'status' => $order->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update order status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pay(Request $request, $id)
    {
        try {
            $order = Order::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            if ($order->payment_status === 'paid') {
                return response()->json(['message' => 'Order is already paid.']);
            }

            if ($order->payment_method === 'stripe') {
                Stripe::setApiKey(env('STRIPE_SECRET'));
                // Fake payment success (simulate for assessment)
                $order->payment_status = 'paid';
                $order->save();

                return response()->json(['message' => 'Stripe payment simulated successfully.']);
            } elseif ($order->payment_method === 'paypal') {
                $order->payment_status = 'paid';
                $order->save();

                return response()->json(['message' => 'PayPal payment simulated successfully.']);
            }

            return response()->json(['message' => 'Invalid payment method'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
