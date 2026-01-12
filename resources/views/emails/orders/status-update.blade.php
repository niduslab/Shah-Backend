<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
        .status-confirmed { background: #c6f6d5; color: #276749; }
        .status-processing { background: #bee3f8; color: #2b6cb0; }
        .status-shipped { background: #feebc8; color: #c05621; }
        .status-delivered { background: #c6f6d5; color: #276749; }
        .status-cancelled { background: #fed7d7; color: #c53030; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shah Sports</h1>
            <p>Order Status Update</p>
        </div>
        
        <div class="content">
            <p>Your order status has been updated!</p>
            
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            
            <p><strong>New Status:</strong> 
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
            
            @if($order->status === 'shipped' && $order->tracking_number)
            <p><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
            @endif
            
            @if($order->status === 'delivered')
            <p>Your order has been delivered. We hope you enjoy your purchase!</p>
            <p>If you're happy with your order, please consider leaving a review.</p>
            @endif
            
            @if($order->status === 'cancelled')
            <p>Your order has been cancelled. If you have any questions, please contact our support team.</p>
            @endif
        </div>
        
        <div class="footer">
            <p>Shah Sports - Your Sports Equipment Destination</p>
        </div>
    </div>
</body>
</html>
