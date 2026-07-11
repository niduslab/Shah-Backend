<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Dashboard</h2>
            <p>Whole data about your business here</p>
        </div>
        {{-- <div>
            <a href="#" class="btn btn-primary"><i class="text-muted material-icons md-post_add"></i>Create report</a>
        </div> --}}
    </div>
    <div class="row">
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-primary-light"><i class="text-primary material-icons md-monetization_on"></i></span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Revenue</h6>
                        <span>৳{{ number_format($totalSalesRevenue, 2) }}</span>
                        <span class="text-sm"> Shipping fees are not included </span>
                    </div>
                </article>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <a href="{{ route('admin.manage.orders') }}" class="icontext">
                    <span class="icon icon-sm rounded-circle bg-success-light"><i class="text-success material-icons md-local_shipping"></i></span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Orders</h6>
                        <span>{{ $orderStatusBreakdown->sum('count') }}</span>
                        <span class="text-sm"> Excluding orders in transit </span>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <a href="{{ route('admin.show.product') }}" class="icontext">
                    <span class="icon icon-sm rounded-circle bg-warning-light"><i class="text-warning material-icons md-qr_code"></i></span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Products</h6>
                        <span>{{ $topSellingProducts->count() }}</span>
                        <span class="text-sm"> In {{ $categoryPerformance->count() }} Categories </span>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card card-body mb-4">
                <article class="icontext">
                    <span class="icon icon-sm rounded-circle bg-info-light"><i class="text-info material-icons md-shopping_basket"></i></span>
                    <div class="text">
                        <h6 class="mb-1 card-title">Monthly Earning</h6>
                        <span>৳{{ number_format($averageOrderValue, 2) }}</span>
                        <span class="text-sm"> Based on your local time. </span>
                    </div>
                </article>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8 col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Sale statistics</h5>
                    <canvas id="myChart" height="120px"></canvas>
                </article>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4">
                            <article class="card-body">
                                <h5 class="card-title">Brand Performance</h5>
                                <div class="list-group">
                                    @foreach ($brandPerformance as $brand)
                                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-box-seam text-primary"></i> {{ $brand->brand_name ?? 'Unknown Brand' }}</span>
                                            <span class="badge bg-info rounded-pill">$ {{ $brand->total_sales }} </span>
                                        </a>
                                    @endforeach
                                </div>
                            </article>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card mb-4">
                            <article class="card-body">
                                <h5 class="card-title">Category Performance</h5>
                                <div class="list-group">
                                    @foreach ($categoryPerformance as $category)
                                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <span><i class="bi bi-box-seam text-primary"></i> {{ $category->category_name ?? 'Unknown Brand' }}</span>
                                            <span class="badge bg-info rounded-pill">$ {{ $category->total_sales }} </span>
                                        </a>
                                    @endforeach
                                </div>
                            </article>
                    </div>
                </div>
                {{-- <div class="col-lg-7">
                    <div class="card mb-4">
                        <article class="card-body">
                            <h5 class="card-title">Recent activities</h5>
                            <ul class="verti-timeline list-unstyled font-sm">
                                <li class="event-list">
                                    <div class="event-timeline-dot">
                                        <i class="material-icons md-play_circle_outline font-xxl"></i>
                                    </div>
                                    <div class="media">
                                        <div class="me-3">
                                            <h6><span>Today</span> <i class="material-icons md-trending_flat text-brand ml-15 d-inline-block"></i></h6>
                                        </div>
                                        <div class="media-body">
                                            <div>Lorem ipsum dolor sit amet consectetur</div>
                                        </div>
                                    </div>
                                </li>
                                <!-- Repeat for other activities... -->
                            </ul>
                        </article>
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="col-xl-4 col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Revenue Based on Areas</h5>
                    <canvas id="areaChart" height="200px"></canvas>
                </article>
            </div>
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Top Selling Products</h5>
                    <div class="list-group">
                        @foreach ($topSellingProducts as $product)
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-box-seam text-primary"></i> {{ $product->product_name ?? 'Unknown Product' }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $product->total_quantity }} sold</span>
                            </a>
                        @endforeach
                    </div>
                </article>
            </div>

        </div>
        <div class="col-xl-4 col-lg-12">
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Revenue Based on Areas</h5>
                    <canvas id="areaChart" height="200px"></canvas>
                </article>
            </div>
            <div class="card mb-4">
                <article class="card-body">
                    <h5 class="card-title">Top Selling Products</h5>
                    <div class="list-group">
                        @foreach ($topSellingProducts as $product)
                            <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-box-seam text-primary"></i> {{ $product->product_name ?? 'Unknown Product' }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $product->total_quantity }} sold</span>
                            </a>
                        @endforeach
                    </div>
                </article>
            </div>

        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const areaChart = document.getElementById('areaChart').getContext('2d');

    const salesRevenueData = {
        labels: {!! json_encode($revenueByMonth->pluck('month')->toArray()) !!},
        datasets: [{
            label: 'Sales Revenue',
            data: {!! json_encode($revenueByMonth->pluck('total_revenue')->toArray()) !!},
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
        }]
    };

    const areaData = {
        labels: {!! json_encode($orderStatusBreakdown->pluck('status')->toArray()) !!},
        datasets: [{
            label: 'Order Status Breakdown',
            data: {!! json_encode($orderStatusBreakdown->pluck('count')->toArray()) !!},
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1,
        }]
    };

    const myChart = new Chart(ctx, {
        type: 'line',
        data: salesRevenueData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const areaChartInstance = new Chart(areaChart, {
        type: 'bar',
        data: areaData,
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
