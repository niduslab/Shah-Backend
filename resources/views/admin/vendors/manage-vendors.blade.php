@extends('layouts.app')
@section('title', 'NidusCart - Manage Vendors')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Vendors List</h2>
            <p>Manage Vendors</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
        </div>
    </div>
    <div class="card mb-4">
        <header class="card-header">
            <div class="row align-items-center">
                <div class="col col-check flex-grow-0">
                    <div class="form-check ms-2">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                </div>
                <div class="col-md-3 col-12 me-auto mb-md-0 mb-3">
                    <select class="form-select">
                        <option selected>All category</option>
                        <option>Electronics</option>
                        <option>Clothes</option>
                        <option>Automobile</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <input type="date" class="form-control" />
                </div>
                <div class="col-md-2 col-6">
                    <select class="form-select">
                        <option selected>Status</option>
                        <option>Active</option>
                        <option>Disabled</option>
                        <option>Show all</option>
                    </select>
                </div>
            </div>
        </header>
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive data-table">
                        <thead>
                            <tr>
                                <th width="20%">Seller</th>
                                <th width="20%">Email</th>
                                <th width="10%">Shop Status</th>
                                <th width="20%">Registered</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <!-- Order details will be loaded here via AJAX -->
                <div id="orderDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script> --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script type="text/javascript">
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.manage.vendors') }}",
            columns: [
                {data: 'seller', name: 'seller'},
                {data: 'shop_email', name: 'shop_email'},
                {data: 'status', name: 'status'},
                {data: 'created_date', name: 'created_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });



    // $(document).ready(function() {
    // $('.data-table').on('click', '.view-order-details', function(e) {
    //     e.preventDefault();
    //     var orderId = $(this).data('order-id');

    //     $.ajax({
    //         url: '{{ route("admin.order.details") }}', // Define a route for fetching order details
    //         type: 'GET',
    //         data: { id: orderId },
    //         success: function(response) {
    //             $('#orderDetailsContent').html(response);
    //             $('#orderDetailsModal').modal('show');
    //         }
    //     });
    // });

// });

</script>

@endsection
