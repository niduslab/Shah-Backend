@extends('layouts.app')
@section('title', 'NidusCart - Manage Promotions')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Discount/Promotions</h2>
            <p>Manage Discount/Promotions</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
        </div>
    </div>
    <div class="card mb-4">
        <header class="card-header">
            <div>
                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#promotionModal" onclick="$('#promotionForm')[0].reset();"><strong>Create New Promotion</strong></button>
            </div>
        </header>
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover data-table">
                        <thead>
                            <tr>
                                <th width="15%">Promotion</th>
                                <th width="10%">Code</th>
                                <th width="10%">Min Dis. Amount</th>
                                <th width="10%">Max Dis. Amount</th>
                                <th width="10%">Percentage</th>
                                <th width="10%">Start Date</th>
                                <th width="10%">End Date</th>
                                <th width="10%">Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Promotion Modal -->
    <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promotionModalLabel">Create New Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="promotionForm">
                        @csrf
                        <input type="hidden" name="promotional_id" id="promotional_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="promotion_name" class="form-label">Promotion Name</label>
                                <input type="text" class="form-control" id="promotion_name" name="promotion_name">
                            </div>
                            <div class="col-md-6">
                                <label for="promotion_code" class="form-label">Promotion Code</label>
                                <input type="text" class="form-control" id="promotion_code" name="promotion_code">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                <input type="number" step="0.01" class="form-control" id="discount_percentage" name="discount_percentage">
                            </div>
                            <div class="col-md-6">
                                <label for="min_purchase_amount" class="form-label">Minimum Purchase Amount</label>
                                <input type="number" step="0.01" class="form-control" id="min_purchase_amount" name="min_purchase_amount">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="max_discount" class="form-label">Maximum Discount</label>
                                <input type="number" step="0.01" class="form-control" id="max_discount" name="max_discount">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Promotion</button>
                    </form>
                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script> --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>



<script type="text/javascript">
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.manage.promotions') }}",
            columns: [
                {data: 'promotion_name', name: 'promotion_name'},
                {data: 'promotion_code', name: 'promotion_code'},
                {data: 'min_purchase_amount', name: 'min_purchase_amount'},
                {data: 'max_discount', name: 'max_discount'},
                {data: 'discount_percentage', name: 'discount_percentage'},
                {data: 'start_date', name: 'start_date'},
                {data: 'end_date', name: 'end_date'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });


    $(document).on('click', '.change-status', function(e) {
        e.preventDefault();

        var status = $(this).data('status');
        var id = $(this).data('id');

        // Ask for confirmation
        if (confirm('Are you sure you want to change the status to ' + status + '?')) {
            // Send AJAX request to update the status
            $.ajax({
                url: '{{ route("admin.update.promotionStatus") }}', // Using Laravel's route helper
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        notyf.success(response.success);
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
        }
    });

    // create new promotion
    // $(document).ready(function() {
    //     // Initialize Notyf

    //     $('#promotionForm').on('submit', function(e) {
    //         e.preventDefault(); // Prevent the form from submitting via the browser

    //         var formData = $(this).serialize(); // Serialize the form data

    //         $.ajax({
    //             url: '{{ route('promotions.store') }}', // Update with your route
    //             type: 'POST',
    //             data: formData,
    //             success: function(response) {
    //                 // Handle success
    //                 // notyf.success(response.success); // Show success notification
    //                 notyf.success("Promotion created successfully"); // Show success notification
    //                 $('#promotionForm')[0].reset();
    //                 $('#promotionModal').modal('hide'); // Hide the modal
    //             },
    //             error: function(xhr) {
    //                 // Handle error
    //                 var response = xhr.responseJSON;
    //                 if (response.error) {
    //                     var errorMessage = 'Please fix the following errors:<ul>';
    //                     $.each(response.error, function(key, value) {
    //                         errorMessage += '<li>' + value[0] + '</li>';
    //                     });
    //                     errorMessage += '</ul>';
    //                     notyf.error(errorMessage); // Show error notification
    //                 } else {
    //                     notyf.error('An unexpected error occurred.'); // Show general error notification
    //                 }
    //             }
    //         });
    //     });
    // });

    $(document).on('click', '.edit-promotion', function() {
    var promotionId = $(this).data('id');
    $.ajax({
        url: '/admin/promotion/' + promotionId + '/edit',
        method: 'GET',
        success: function(data) {
            $('#promotionForm')[0].reset();

            // Populate the form with the existing promotion data
            $('#promotion_name').val(data.promotion_name);
            $('#promotion_code').val(data.promotion_code);
            $('#start_date').val(data.start_date);
            $('#end_date').val(data.end_date);
            $('#discount_percentage').val(data.discount_percentage);
            $('#min_purchase_amount').val(data.min_purchase_amount);
            $('#max_discount').val(data.max_discount);
            $('#status').val(data.status);
            $('#promotional_id').val(data.id);

            // Set form action and method for update
            $('#promotionForm').attr('action', '/admin/promotion/' + promotionId);
            $('#promotionForm').attr('method', 'POST');

            $('#promotionModal').modal('show');
        },
        error: function(response) {
            notyf.error('Error fetching promotion data. Please try again later.');
        }
    });
});




document.getElementById('promotionForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Clear previous messages
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = '';

    // Get form data
    const formData = new FormData(document.getElementById('promotionForm'));

    // Simple form validation
    let isValid = true;
    const requiredFields = ['promotion_name', 'promotion_code', 'start_date', 'end_date'];
    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            document.getElementById(field).classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById(field).classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        messageDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
        return;
    }

    // Send data with Axios
    axios.post('/admin/promotions/store', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(response => {
        // Handle success
        notyf.success(response.data.success || "Promotion saved successfully!");

        // Reset form fields
        document.getElementById('promotionForm').reset();

        // Optionally reload or update the promotions list
        $('.data-table').DataTable().ajax.reload(null, false);

        // Optionally close the modal after a successful save
        $('#promotionModal').modal('hide');
    })
    .catch(error => {
        // Handle error
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            let errorMessages = '<div class="alert alert-danger"><ul>';
            for (let key in errors) {
                if (errors.hasOwnProperty(key)) {
                    // Highlight invalid fields
                    document.getElementById(key).classList.add('is-invalid');
                    errorMessages += `<li>${errors[key][0]}</li>`;
                }
            }
            errorMessages += '</ul></div>';
            messageDiv.innerHTML = errorMessages;
        } else {
            messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
        }
    });
});







    // delete promotion
    $(document).ready(function() {
    $(document).on('click', '.delete-promotion', function() {
        var promotionId = $(this).data('id');
        var token = $('meta[name="csrf-token"]').attr('content'); // Ensure you have CSRF token in your meta tags

        if (confirm('Are you sure you want to delete this promotion?')) {
            $.ajax({
                url: '/admin/promotion/' + promotionId,
                type: 'DELETE',
                data: {
                    "_token": token, // CSRF token
                },
                success: function(response) {
                    notyf.success(response.success || "Promotion deleted successfully!"); // Show success notification
                    $('.data-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    notyf.error('An error occurred while trying to delete the promotion. Please try again.'); // Show error notification
                }
            });
        }
    });
});




</script>


@endsection
