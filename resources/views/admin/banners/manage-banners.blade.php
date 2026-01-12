@extends('layouts.app')
@section('title', 'NidusCart - Manage Banners')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-11 my-5">
        <div class="container mt-5 card p-3">
            <h2>Manage Promotional Banners</h2>
            <button id="createNewBanner" class="btn btn-primary mb-3 w-25">Add New Banner</button>

            <table class="table table-bordered yajra-datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Button Label</th>
                        <th>Background Color</th>
                        <th>Link</th>
                        <th>Position</th>
                        <th>Image</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>


        </div>
    </div>
    <!-- Bootstrap Modal -->
    <div class="modal fade" id="ajaxModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close btn btn-danger p-2 rounded" style="height: 20px;" data-dismiss="modal" aria-label="Close" onclick="closeModal()">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <form id="bannerForm" name="bannerForm" class="form-horizontal needs-validation" novalidate enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="banner_id" id="banner_id">
                        <div class="form-group mb-2">
                            <label for="title" class="col-sm-4 control-label">Title</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="title" name="title" required>
                                <div class="invalid-feedback">Please enter a title.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="button_label" class="col-sm-4 control-label">Button Label</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="button_label" name="button_label" required>
                                <div class="invalid-feedback">Please enter a button label.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="background_color" class="col-sm-4 control-label">Background Color</label>
                            <div class="col-sm-12">
                                <div class="d-flex">
                                    <input type="color" class="form-control w-25 m-1" id="background_color" name="background_color" required>
                                    <input type="text" class="form-control w-50 m-1" id="background_color_hex" name="background_color_hex" required placeholder="#123132">
                                </div>
                                <div class="invalid-feedback">Please select or enter a background color.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="link" class="col-sm-4 control-label">Link</label>
                            <div class="col-sm-12">
                                <input type="url" class="form-control" id="link" name="link" required>
                                <div class="invalid-feedback">Please enter a valid URL.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="position" class="col-sm-4 control-label">Position</label>
                            <div class="col-sm-12">
                                <input type="number" class="form-control" id="position" name="position" required min="1">
                                <div class="invalid-feedback">Please enter a valid position.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label for="image" class="col-sm-4 control-label">Image</label>
                            <div class="col-sm-12">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="invalid-feedback">Please upload a valid image file.</div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <img id="imagePreview" src="" alt="Image Preview" style="max-width: 100%; display: none;">
                        </div>
                        <div class="col-sm-offset-2 col-sm-10 my-2">
                            <button type="submit" class="btn btn-light btn-sm" id="saveBtn" value="create">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>

<script type="text/javascript">
function closeModal() {
    $('#ajaxModel').modal('hide')
}
document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.getElementById('background_color');
        const colorInput = document.getElementById('background_color_hex');

        // Synchronize color input field with color picker
        colorPicker.addEventListener('input', function() {
            colorInput.value = colorPicker.value;
        });

        // Synchronize color picker with color input field
        colorInput.addEventListener('input', function() {
            colorPicker.value = colorInput.value;
        });
    });
    $(function () {
        var table = $('.yajra-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.banners.data') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'title', name: 'title'},
                {data: 'button_label', name: 'button_label'},
                {
                    data: 'background_color',
                    name: 'background_color',
                    render: function(data, type, row) {
                        return '<div style="width: 50px; height: 30px; background-color: ' + data + ';"></div>';
                    }
                },
                {data: 'link', name: 'link'},
                {data: 'position', name: 'position'},
                {data: 'image', name: 'image'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('#createNewBanner').click(function () {
            $('#saveBtn').val("create-banner");
            $('#banner_id').val('');
            $('#bannerForm').trigger("reset");
            $('#modelHeading').html("Create New Banner");
            $('#ajaxModel').modal('show');
            $('#imagePreview').attr('src', '').hide();
            $('#bannerForm').removeClass('was-validated');
        });

        $('body').on('click', '.editBanner', function () {
            var banner_id = $(this).data('id');
            $.get("{{ route('admin.banners.index') }}" + '/' + banner_id + '/edit', function (data) {
                $('#modelHeading').html("Edit Banner");
                $('#saveBtn').val("edit-banner");
                $('#ajaxModel').modal('show');
                $('#banner_id').val(data.id);
                $('#title').val(data.title);
                $('#button_label').val(data.button_label);
                $('#background_color').val(data.background_color);
                $('#background_color_hex').val(data.background_color); // Sync the hex input
                $('#link').val(data.link);
                $('#position').val(data.position);
                if (data.image_path) {
                    $('#imagePreview').attr('src', '{{ asset('storage/') }}/' + data.image_path).show();
                } else {
                    $('#imagePreview').attr('src', '').hide();
                }
                $('#bannerForm').removeClass('was-validated');
            })
        });

        $('#saveBtn').click(function (e) {
            e.preventDefault();
            var form = $('#bannerForm');
            if (form[0].checkValidity() === false) {
                e.stopPropagation();
                form.addClass('was-validated');
            } else {
                $(this).html('Sending..');

                var formData = new FormData(form[0]);

                $.ajax({
                    data: formData,
                    url: "{{ route('admin.banners.store') }}",
                    type: "POST",
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        form.trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                        $('#saveBtn').html('Save changes');
                        form.removeClass('was-validated');
                        notyf.success(data.success);

                    },
                    error: function (data) {
                        console.log('Error:', data);
                        notyf.error(data.success);
                        $('#saveBtn').html('Save changes');
                    }
                });
            }
        });

        $('body').on('click', '.deleteBanner', function () {
            var banner_id = $(this).data("id");
            if (confirm("Are you sure you want to delete this banner?")) {
                $.ajax({
                    type: "DELETE",
                    url: "{{ route('admin.banners.destroy', '') }}/" + banner_id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        notyf.success(data.success);
                        table.draw();
                    },
                    error: function (data) {
                        console.log('Error:', data);
                    }
                });
            }
        });


        // Synchronize color picker and hex input
        $('#background_color').on('input', function () {
            $('#background_color_hex').val($(this).val());
        });

        $('#background_color_hex').on('input', function () {
            $('#background_color').val($(this).val());
        });
    });
</script>
@endsection
