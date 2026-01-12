<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .order-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .item:last-child { border-bottom: none; }
        .total { font-weight: bold; font-size: 18px; margin-top: 15px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shah Sports</h1>
            <p>Order Confirmation</p>
        </div>
        
        <div class="content">
            <p>Thank you for your order!</p>
            <p>Your order <strong>{{ $order->order_number }}</strong> has been received and is being processed.</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                @foreach($order->items as $item)
                <div class="item">
                    <strong>{{ $item->product_name }}</strong><br>
                    Qty: {{ $item->quantity }} × ৳{{ number_format($item->unit_price, 2) }}
                    <span style="float: right;">৳{{ number_format($item->total_price, 2) }}</span>
                </div>
                @endforeach
                
                <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #eee;">
                    <p>Subtotal: ৳{{ number_format($order->subtotal, 2) }}</p>
                    <p>Shipping: ৳{{ number_format($order->shipping_cost, 2) }}</p>
                    @if($order->discount_amount > 0)
                    <p>Discount: -৳{{ number_format($order->discount_amount, 2) }}</p>
                    @endif
                    <p class="total">Total: ৳{{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>
            
            @if($order->shippingAddress)
            <div class="order-details">
                <h3>Shipping Address</h3>
                <p>{{ $order->shippingAddress->address_line_1 }}<br>
                {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->postal_code }}</p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Shah Sports - Your Sports Equipment Destination</p>
            <p>If you have any questions, please contact us.</p>
        </div>
    </div>
</body>
</html>
