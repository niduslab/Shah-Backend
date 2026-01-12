@extends('layouts.app')
@section('title', 'NidusCart - Manage Product Models')
@section('content')
<div class="container">
    <div class="row justify-content-center my-5">
        <div class="col-md-6 card p-3">
            <h2>Product Models</h2>
            <button class="btn btn-sm btn-light w-25 my-2" id="addModelBtn">Add Model</button>

            @if (session('success'))
                <div class="alert alert-success mt-3">
                    {{ session('success') }}
                </div>
            @endif

            <table class="table table-bordered data-table mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Model Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="modelModal" tabindex="-1" aria-labelledby="modelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelModalLabel">Add New Product Model</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modelForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="modelId">
                    <div class="form-group">
                        <label for="model_name">Model Name</label>
                        <input type="text" name="model_name" class="form-control" id="model_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script>
   $(document).ready(function () {
    // Initialize DataTable
    var table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.product_models.index') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'model_name', name: 'model_name'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    // Add Model Button Click
    $('#addModelBtn').click(function () {
        $('#modelForm').trigger("reset");
        $('#modelModalLabel').text("Add New Product Model");
        $('#modelId').val('');
        $('#modelModal').modal('show');
    });

    // Edit Button Click
    $('body').on('click', '.edit-btn', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');

        $('#modelForm').trigger("reset");
        $('#modelModalLabel').text("Edit Product Model");
        $('#modelId').val(id);
        $('#model_name').val(name);
        $('#modelModal').modal('show');
    });

    // Save Button Click
    $('#saveBtn').click(function (e) {
        e.preventDefault();
        var id = $('#modelId').val();
        var url = id ? "{{ route('admin.product_models.update', ':id') }}".replace(':id', id) : "{{ route('admin.product_models.store') }}";
        var method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $('#modelForm').serialize(),
            success: function (response) {
                // Close the modal after saving
                $('#modelModal').modal('hide');
                table.draw();
                notyf.success(response.success);
            },
            error: function (error) {
                notyf.error('Something went wrong!');
            }
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
});

</script>

@endsection
