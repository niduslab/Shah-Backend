<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-details {
            font-size: 11px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table thead {
            background-color: #f5f5f5;
        }
        .items-table th {
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #333;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .items-table th.text-right,
        .items-table td.text-right {
            text-align: right;
        }
        .items-table th.text-center,
        .items-table td.text-center {
            text-align: center;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        .totals-table td {
            padding: 8px 10px;
        }
        .totals-table .label {
            text-align: right;
            font-weight: bold;
        }
        .totals-table .value {
            text-align: right;
            width: 120px;
        }
        .totals-table .grand-total {
            border-top: 2px solid #333;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        .notes {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #333;
        }
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-details">
                    {{ $company['address'] }}<br>
                    @if(!empty($company['phone']))
                        Phone: {{ $company['phone'] }}<br>
                    @endif
                    Email: {{ $company['email'] }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-details">
                    <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}<br>
                    <strong>Order #:</strong> {{ $order->order_number }}
                </div>
            </div>
        </div>

        <!-- Bill To Section -->
        <div class="section">
            <div class="section-title">Bill To</div>
            <table class="info-table">
                <tr>
                    <td class="label">Customer:</td>
                    <td>{{ $order->customer_name ?? ($order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'Guest') }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $order->customer_email ?? $order->user?->email }}</td>
                </tr>
                <tr>
                    <td class="label">Phone:</td>
                    <td>{{ $order->customer_phone ?? $order->user?->phone }}</td>
                </tr>
                @if($order->shippingAddress)
                <tr>
                    <td class="label">Shipping Address:</td>
                    <td>
                        {{ $order->shippingAddress->address_line1 }}<br>
                        @if($order->shippingAddress->address_line2)
                            {{ $order->shippingAddress->address_line2 }}<br>
                        @endif
                        {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}<br>
                        {{ $order->shippingAddress->country }}
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Order Items -->
        <div class="section">
            <div class="section-title">Order Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Product</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 20%;">Unit Price</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->productVariation)
                                <br>
                                <small style="color: #666;">
                                    @foreach($item->productVariation->variationValues as $variationValue)
                                        @if($variationValue->variationOption && $variationValue->variationOption->variation)
                                            {{ $variationValue->variationOption->variation->name }}: {{ $variationValue->variationOption->value }}
                                            @if(!$loop->last), @endif
                                        @endif
                                    @endforeach
                                </small>
                            @endif
                            @if($item->sku)
                                <br><small style="color: #666;">SKU: {{ $item->sku }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">৳{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <td class="label">Discount:</td>
                <td class="value">-৳{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Shipping:</td>
                <td class="value">৳{{ number_format($invoice->shipping_cost, 2) }}</td>
            </tr>
            @if($invoice->tax_amount > 0)
            <tr>
                <td class="label">Tax:</td>
                <td class="value">৳{{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label">Total:</td>
                <td class="value">৳{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </table>

        <!-- Payment Information -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <table class="info-table">
                <tr>
                    <td class="label">Payment Method:</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                </tr>
                <tr>
                    <td class="label">Payment Status:</td>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                </tr>
                <tr>
                    <td class="label">Order Status:</td>
                    <td>{{ ucfirst($order->status) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes:</div>
            {{ $invoice->notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
