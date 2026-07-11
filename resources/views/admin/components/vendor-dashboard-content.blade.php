
<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Vendor Dashboard</h2>
            <p>Overview of your shop's performance</p>
        </div>
        <div>
            <a href="#" class="btn btn-primary"><i class="text-muted material-icons md-post_add"></i>Create report</a>
        </div>
    </div>

    <div class="row">
        <!-- Revenue -->
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-primary-light">
                        <i class="text-primary material-icons md-monetization_on"></i>
                    </span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Revenue</h6>
                        <span>৳{{ number_format($totalSalesRevenue, 2) }}</span>
                        <span class="text-sm"> Shipping fees not included </span>
                    </div>
                </article>
            </div>
        </div>

        <!-- Orders -->
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-success-light">
                        <i class="text-success material-icons md-local_shipping"></i>
                    </span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Orders</h6>
                        <span>{{ $orderStatusBreakdown->sum('count') }}</span>
                        <span class="text-sm"> Excluding in-transit orders </span>
                    </div>
                </article>
            </div>
        </div>

        <!-- Products -->
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-warning-light">
                        <i class="text-warning material-icons md-qr_code"></i>
                    </span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Products</h6>
                        <span>{{ $topSellingProducts->count() }}</span>
                        <span class="text-sm"> Across categories </span>
                    </div>
                </article>
            </div>
        </div>

        <!-- Average Purchase Value -->
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-info-light">
                        <i class="text-info material-icons md-shopping_basket"></i>
                    </span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Avg. Purchase Value</h6>
                        <span>৳{{ number_format($averagePurchaseValue, 2) }}</span>
                        <span class="text-sm"> Based on local time. </span>
                    </div>
                </article>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Statistics -->
        <div class="col-xl-8 col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Sales Statistics</h5>
                    <canvas id="salesChart" height="120px"></canvas>
                </article>
            </div>
        </div>

        <!-- Refunds and Cancellations -->
        <div class="col-xl-4 col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Refunds & Cancellations</h5>
                    <ul class="list-unstyled">
                        <li>Refunds: {{ $refundCount }}</li>
                        <li>Cancellations: {{ $cancellationCount }}</li>
                    </ul>
                </article>
            </div>
        </div>

        <!-- Top Selling Products -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Top Selling Products</h5>
                    <ul class="list-unstyled">
                        @foreach ($topSellingProducts as $product)
                            <li>{{ $product->name ?? 'Unknown Product' }} - {{ $product->total_quantity }} sold</li>
                        @endforeach
                    </ul>
                </article>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    const salesData = {
        labels: {!! json_encode($revenueByMonth->pluck('month')->toArray()) !!},
        datasets: [{
            label: 'Sales Revenue',
            data: {!! json_encode($revenueByMonth->pluck('total_revenue')->toArray()) !!},
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
        }]
    };

    new Chart(ctxSales, {
        type: 'line',
        data: salesData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

