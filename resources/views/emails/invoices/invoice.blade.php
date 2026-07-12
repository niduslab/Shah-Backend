<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .invoice-details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shah Sports</h1>
            <p>Invoice</p>
        </div>
        
        <div class="content">
            <p>Please find your invoice attached to this email.</p>
            
            <div class="invoice-details">
                <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Order Number:</strong> {{ $invoice->order->order_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('F j, Y') }}</p>
                <p><strong>Total Amount:</strong> BDT {{ number_format($invoice->total_amount, 2) }}</p>
            </div>
            
            <p>Thank you for shopping with Shah Sports!</p>
        </div>
        
        <div class="footer">
            <p>Shah Sports - Your Sports Equipment Destination</p>
        </div>
    </div>
</body>
</html>
