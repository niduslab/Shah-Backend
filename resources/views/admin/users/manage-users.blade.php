@extends('layouts.app')
@section('title', 'NidusCart - Manage Users')
@section('content')

<section class="content-main">
    <div class="card">
        <header class="card-header">
            <div>
                <h2 class="content-title card-title">Manage Users</h2>
            </div>
        </header>
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive table-hover data-table">
                        <thead>
                            <tr>
                                <th width="25%">User</th>
                                <th width="20%">Email</th>
                                <th width="15%">Account Status</th>
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
    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userDetailsForm">
                        @csrf
                        <input type="hidden" id="userId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fname">First Name</label>
                                    <input type="text" id="fname" name="fname" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="lname">Last Name</label>
                                    <input type="text" id="lname" name="lname" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="user_type">User Type</label>
                                    <select id="user_type" name="user_type" class="form-control">
                                        <option value="vendor">Vendor</option>
                                        <option value="customer">Customer</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="phone_no">Phone Number</label>
                                    <input type="text" id="phone_no" name="phone_no" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="text" id="website" name="website" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select id="gender" name="gender" class="form-control">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="others">Others</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Address fields -->
                                <div class="form-group">
                                    <label for="address_line_1">Address Line 1</label>
                                    <input type="text" id="address_line_1" name="address_line_1" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="address_line_2">Address Line 2</label>
                                    <input type="text" id="address_line_2" name="address_line_2" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" id="state" name="state" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="zip_code">Zip Code</label>
                                    <input type="text" id="zip_code" name="zip_code" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="contact_no">Contact Number</label>
                                    <input type="text" id="contact_no" name="contact_no" class="form-control">
                                </div>
                            </div>
                        </div>




                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveBtn">Save changes</button>
                </div>
            </div>
        </div>
    </div>




</section>

{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script> --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.7/axios.min.js"></script>



<script type="text/javascript">
    $(function () {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.manage.users') }}",
            columns: [
                {data: 'user', name: 'user'},
                {data: 'email', name: 'email'},
                {data: 'status', name: 'status'},
                {data: 'created_date', name: 'created_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    function changeUserStatus(userId) {
    // Show confirmation dialog
    const confirmation = confirm("Are you sure you want to change the status of this user?");

    if (confirmation) {
        // Proceed with the AJAX request if the user confirms
        $.ajax({
            url: "{{ route('admin.change.userStatus', '') }}/" + userId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
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
                let errorMessage = 'An error occurred while changing the status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                notyf.error(errorMessage);
            }
        });
    } else {
        // Do nothing if the user cancels
        notyf.error('Action canceled');
    }
}

 // Add Model Button Click
 $('#addModelBtn').click(function () {
        $('#modelForm').trigger("reset");
        $('#modelModalLabel').text("Add New Product Model");
        $('#modelId').val('');
        $('#modelModal').modal('show');
    });

    $('body').on('click', '.edit-btn', function () {
    var userId = $(this).data('id');

    // Clear the form and modal content before loading new data
    $('#userDetailsForm').trigger("reset");
    $('#userDetailsModalLabel').text("Edit User");

    // Axios request to get user information
    axios.get('/admin/user-info/' + userId)
        .then(function (response) {
            var user = response.data;

            // Populate the modal's input fields with the user data
            $('#userId').val(user.id);
            $('#fname').val(user.fname);
            $('#lname').val(user.lname);
            $('#email').val(user.email);
            $('#user_type').val(user.user_type);
            $('#phone_no').val(user.user_profile.phone_no);
            $('#website').val(user.user_profile.website);
            $('#gender').val(user.user_profile.gender);
            $('#date_of_birth').val(user.user_profile.date_of_birth);

            // If addresses exist, populate them
            if (user.addresses.length > 0) {
                $('#address_line_1').val(user.addresses[0].address_line_1);
                $('#address_line_2').val(user.addresses[0].address_line_2);
                $('#city').val(user.addresses[0].city);
                $('#state').val(user.addresses[0].state);
                $('#zip_code').val(user.addresses[0].zip_code);
                $('#contact_no').val(user.addresses[0].contact_no);
            }

            // Show the modal after populating the fields
            $('#userDetailsModal').modal('show');
        })
        .catch(function (error) {
            console.log(error);
            alert('Unable to fetch user details');
        });
});

$('#saveBtn').click(function (e) {
    e.preventDefault();

    var userId = $('#userId').val(); // Get user ID from the hidden input field
    var url = userId ? "/admin/user-info/" + userId : "/admin/user-info"; // If userId exists, it's an update
    var method = userId ? 'PUT' : 'POST'; // If userId exists, use PUT for update

    // Collect the form data
    var formData = {
        fname: $('#fname').val(),
        lname: $('#lname').val(),
        email: $('#email').val(),
        user_type: $('#user_type').val(),
        user_profile: {
            phone_no: $('#phone_no').val(),
            website: $('#website').val(),
            gender: $('#gender').val(),
            date_of_birth: $('#date_of_birth').val(),
        },
        addresses: [{
            address_line_1: $('#address_line_1').val(),
            address_line_2: $('#address_line_2').val(),
            city: $('#city').val(),
            state: $('#state').val(),
            zip_code: $('#zip_code').val(),
            contact_no: $('#contact_no').val(),
        }]
    };

    // Make an Axios request to update user data
    axios({
        method: method,
        url: url,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Set CSRF token in the headers
        }
    })
    .then(function (response) {
        // Handle success - close modal and show a success message
        $('#userDetailsModal').modal('hide');
        notyf.success('User details updated successfully!');

        // Optionally, reload the DataTable to reflect the changes
        $('.data-table').DataTable().ajax.reload(null, false);
    })
    .catch(function (error) {
        console.log(error);
        notyf.error('Failed to update user details.');
    });
});


    // Delete Button Click
    $('body').on('click', '.delete-btn', function () {
        if (confirm("Are you sure you want to delete this model?")) {
            var id = $(this).data('id');
            var url = "{{ route('admin.product_models.destroy', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                method: 'DELETE',
                data: {_token: "{{ csrf_token() }}"},
                success: function (response) {
                    table.draw();
                    notyf.success(response.success);
                },
                error: function (error) {
                    notyf.error('Something went wrong!');
                }
            });
        }
    });

    // Close modal explicitly when cross (X) or close button is clicked
    $('.close, .btn-secondary').click(function () {
        $('#modelModal').modal('hide');
    });

</script>

@endsection
