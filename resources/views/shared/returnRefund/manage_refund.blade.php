@extends('layouts.app')
@section('title', 'NidusCart - Manage Returns')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Manage Refunds</h2>
            <p>Manage all return orders</p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Tracking Number</th>
                                <th>User Name</th>
                                <th>Refund Status</th>
                                <th>Reason</th>
                                <th>Requested At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">Change Return Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        @csrf
                        <input type="hidden" name="return_id" id="return_id">
                        <div class="mb-3">
                            <label for="status" class="form-label">Select New Status</label>
                            <select class="form-select" id="status" name="status">
                                {{-- <option value="requested">Requested</option>
                                <option value="approved">Approved</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option> --}}
                                <option value="requested">Return Requested</option>
                                <option value="processed">Replacement Processed</option>
                                <option value="approved">Return Approved</option>
                                <option value="shipped">Replacement Shipped</option>
                                <option value="delivered">Replacement Delivered</option>
                                <option value="rejected">Replacement Rejected</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                    <div id="statusMessage" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>

<script type="text/javascript">
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('manage.productReturns') }}",
            columns: [
                {data: 'order_id', name: 'order.id'},
                {data: 'tracking_number', name: 'order.tracking_number'},
                {data: 'user_name', name: 'user.fname'},
                {data: 'return_status', name: 'return_status'},
                {data: 'reason', name: 'reason'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    $(document).on('click', '.change-status', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        $('#return_id').val(id); // Set the return ID in the modal
        $('#changeStatusModal').modal('show'); // Show the modal
    });

    $(document).on('submit', '#changeStatusForm', function(e) {
        e.preventDefault();

        var id = $('#return_id').val();
        var status = $('#status').val();

        // Send AJAX request to update the status
        $.ajax({
            url: '{{ route("update.returnStatus") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    notyf.success(response.success);
                    $('#changeStatusModal').modal('hide'); // Hide the modal
                } else {
                    notyf.error(response.error || 'An unexpected error occurred.');
                }
                $('.data-table').DataTable().ajax.reload(null, false);
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while updating the status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                notyf.error(errorMessage);
            }
        });
    });
</script>

@endsection
