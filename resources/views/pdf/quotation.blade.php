<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation #{{ $quotation_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        .container { padding: 20px; max-width: 800px; margin: 0 auto; }
        .header { display: table; width: 100%; margin-bottom: 30px; border-bottom: 2px solid #1a56db; padding-bottom: 20px; }
        .header-left { display: table-cell; width: 50%; vertical-align: top; }
        .header-right { display: table-cell; width: 50%; text-align: right; vertical-align: top; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a56db; margin-bottom: 5px; }
        .company-details { font-size: 11px; color: #666; }
        .quotation-title { font-size: 28px; font-weight: bold; color: #1a56db; margin-bottom: 10px; }
        .quotation-details { font-size: 11px; }
        .badge { display: inline-block; background-color: #e8f0fe; color: #1a56db; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; margin-bottom: 8px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #1a56db; border-bottom: 1px solid #c3d4f8; padding-bottom: 5px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px 0; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 140px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table thead { background-color: #e8f0fe; }
        .items-table th { padding: 10px; text-align: left; font-weight: bold; border-bottom: 2px solid #1a56db; color: #1a56db; }
        .items-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .items-table th.text-right, .items-table td.text-right { text-align: right; }
        .items-table th.text-center, .items-table td.text-center { text-align: center; }
        .items-table tr:nth-child(even) { background-color: #f7f9ff; }
        .totals-table { width: 300px; margin-left: auto; margin-top: 20px; }
        .totals-table td { padding: 8px 10px; }
        .totals-table .label { text-align: right; font-weight: bold; }
        .totals-table .value { text-align: right; width: 120px; }
        .totals-table .grand-total td { border-top: 2px solid #1a56db; font-size: 14px; font-weight: bold; color: #1a56db; }
        .validity { margin-top: 20px; padding: 12px 15px; background-color: #fff8e1; border-left: 3px solid #f59e0b; font-size: 11px; }
        .notes { margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-left: 3px solid #1a56db; }
        .notes-title { font-weight: bold; margin-bottom: 5px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #c3d4f8; text-align: center; font-size: 11px; color: #666; }
        .footer strong { color: #1a56db; }
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
                    @if(!empty($company['phone']))Phone: {{ $company['phone'] }}<br>@endif
                    Email: {{ $company['email'] }}
                </div>
            </div>
            <div class="header-right">
                <div class="badge">QUOTATION</div>
                <div class="quotation-title">QUOTATION</div>
                <div class="quotation-details">
                    <strong>Quotation #:</strong> {{ $quotation_number }}<br>
                    <strong>Date:</strong> {{ $date }}<br>
                    <strong>Valid Until:</strong> {{ $valid_until }}
                </div>
            </div>
        </div>

        <!-- Prepared For -->
        <div class="section">
            <div class="section-title">Prepared For</div>
            <table class="info-table">
                <tr>
                    <td class="label">Customer:</td>
                    <td>{{ $customer['name'] }}</td>
                </tr>
                @if(!empty($customer['email']))
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $customer['email'] }}</td>
                </tr>
                @endif
                @if(!empty($customer['phone']))
                <tr>
                    <td class="label">Phone:</td>
                    <td>{{ $customer['phone'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Items -->
        <div class="section">
            <div class="section-title">Quoted Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 45%;">Product</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-right" style="width: 20%;">Unit Price</th>
                        <th class="text-right" style="width: 20%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <strong>{{ $item['name'] }}</strong>
                            @if(!empty($item['sku']))
                                <br><small style="color:#666;">SKU: {{ $item['sku'] }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                        <td class="text-right">{{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($discount_amount > 0)
            <tr>
                <td class="label">Discount ({{ $discount_percent }}%):</td>
                <td class="value">-{{ number_format($discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label">Total:</td>
                <td class="value">{{ number_format($total, 2) }}</td>
            </tr>
        </table>

        <!-- Validity Notice -->
        <div class="validity">
            <strong>Note:</strong> This quotation is valid for 30 days from the date of issue (until {{ $valid_until }}).
            Prices are subject to change after the validity period. This is not a tax invoice.
        </div>

        @if(!empty($notes))
        <div class="notes">
            <div class="notes-title">Notes:</div>
            {{ $notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for considering <strong>{{ $company['name'] }}</strong>!</p>
            <p>To place an order based on this quotation, please contact us at {{ $company['email'] }}</p>
            <p style="margin-top:5px; font-style:italic;">This is a computer-generated quotation and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
