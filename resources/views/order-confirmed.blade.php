<h1>Thank you for your order!</h1>
<p>Order #{{ $order->order_number }} has been placed successfully.</p>
<ul>
    @foreach($order->items as $item)
        <li>{{ $item->product->name_en }} - Qty: {{ $item->quantity }}</li>
    @endforeach
</ul>
<p>Total: ${{ $order->items->sum(fn($item) => $item->price * $item->quantity) }}</p>
