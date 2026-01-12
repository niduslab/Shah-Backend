@extends('layouts.app')

@section('title', 'NidusCart - Manage Brands')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Brands</h2>
            <p>Add New Brand</p>
        </div>

        <div>
            @if (\Session::has('success'))
                <div class="alert alert-success">
                    <ul>
                        <li>{!! \Session::get('success') !!}</li>
                    </ul>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>


        <div>
            <input type="text" placeholder="Search Categories" class="form-control bg-white" />
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <form action="{{ route('admin.brand.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="brand_name" class="form-label">Brand Name</label>
                            <input type="text" placeholder="Type here" class="form-control" name="brand_name" value="{{ old('brand_name') }}" id="brand_name" required />
                        </div>
                        <div class="mb-4">
                            <label for="logo" class="form-label">Brand Image</label>
                            <input type="file" class="form-control" name="logo" id="logo" />
                            <img id="logo_preview" src="#" alt="Brand Logo" style="display:none; margin-top: 10px; max-height: 100px;" />
                        </div>
                        <div class="mb-4">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-control" name="priority" id="priority" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="form-label">Brand Description</label>
                            <textarea name="description" class="form-control" id="description">{{ old('description') }}</textarea>
                        </div>
                        <input type="hidden" name="brand_id" id="brand_id">

                        <div class="d-grid">

                        </div>
                        <button class="btn btn-light btn-sm" type="submit" id="add-brand">Create Brand</button>
                    </form>
                </div>
                <div class="col-md-9 card p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-responsive data-table">
                            <thead>
                                <tr>
                                    <th width="10%">Brands</th>
                                    <th width="30%">Logo</th>
                                    <th width="30%">Description</th>
                                    <th width="20%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- .col// -->
            </div>
            <!-- .row // -->
        </div>
        <!-- card body .// -->
    </div>
    <!-- card .// -->
</section>


<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script type="text/javascript">

    $(function () {
      var table = $('.data-table').DataTable({

          processing: true,
          serverSide: true,
          ajax: "{{ route('admin.manage.brands') }}",

          columns: [
              {data: 'brand_name', name: 'brand_name'},
              {data: 'logo', name: 'logo'},
              {data: 'description', name: 'description'},
              {data: 'action', name: 'action', orderable: false, searchable: false},
          ]

      });

    });

    // JavaScript AJAX for editing a brand
    function editBrand(id) {
        $('#add-brand').html('Update Brand');
        $.ajax({
            type: "GET",
            url: "/admin/brand/edit/" + id, // Pass the id in the URL
            success: function (response) {
                // Ensure response object contains correct keys
                $('#brand_name').val(response.brand_name);
                $('#description').val(response.description);
                $('#priority').val(response.priority);
                $('#brand_id').val(response.id);
                // Handle logo preview
                if (response.logo) {
                    $('#logo_preview').attr('src', response.logo).show();
                } else {
                    $('#logo_preview').hide();
                }
            },
            error: function (xhr) {
                alert('An error occurred while fetching the brand data.');
            }
        });
    }


    function deleteBrand(id) {
    if (confirm("Are you sure you want to delete this brand?")) {
        $.ajax({
            type: "GET",
            url: "/admin/brand-delete/" + id,
            success: function (response) {
                if (response.success) {
                    alert('Brand deleted successfully.');
                    location.reload(); // Refresh the page to reflect the changes
                } else {
                    alert('Failed to delete the brand. Please try again.');
                }
            },
            error: function (xhr, status, error) {
                alert('An error occurred: ' + error);
            }
        });
    }
}




  </script>

@endsection
