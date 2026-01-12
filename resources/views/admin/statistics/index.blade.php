{{-- $totalSalesRevenue = Order::whereIn('status', ['delivered', 'shipped', 'processing'])
    ->selectRaw('SUM(total_amount) as total_revenue, COUNT(id) as total_sales')
    ->first();
 --}}
