@extends('layouts.app')
@section('title', 'NidusCart - Manage Products')
@section('content')

    <section class="content-main">
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Products</h2>
                <p>Manage Products</p>
            </div>
            <div>
                <a href="#" class="btn btn-light rounded font-md">Export</a>
                <a href="#" class="btn btn-light rounded font-md">Import</a>
            </div>
        </div>
        <div class="card mb-4">
            <header class="card-header">
                <div>
                    <a href="javascript:void(0)" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#promotionModal"
                        onclick="$('#productForm')[0].reset();"><strong>Add Product</strong></a>
                </div>
            </header>
            <div class="card-body">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th width="15%">Product</th>
                                    <th width="10%">Price</th>
                                    <th width="10%">Stock</th>
                                    <th width="10%">Sku</th>
                                    <th width="10%">trending</th>
                                    <th width="10%">featured</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Create Date</th>
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
        <div class="modal fade" id="promotionModal" tabindex="-1" aria-labelledby="promotionModalLabel" aria-hidden="true"
            data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        {{-- <h5 class="modal-title" id="promotionModalLabel">Add New Product</h5> --}}
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" id="productForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="content-header">
                                        <h2 class="content-title">Add New Product <br>
                                            <h6 id="show-error"></h6>
                                        </h2>
                                        <div>
                                            <button class="btn btn-md rounded font-sm hover-up" type="submit">Save</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-8">
                                    <div class="card mb-4">
                                        <div class="card-body">

                                            <div class="row py-5">
                                                <div class="col-lg-6">
                                                    <div class="row">
                                                        <div class="col-lg-12 mb-4">
                                                            <label for="product_name" class="form-label">Product Name
                                                                *</label>
                                                            <input type="text" placeholder="Type here"
                                                                class="form-control" id="product_name" name="product_name"
                                                                value="{{ old('product_name') }}"
                                                                data-parsley-required="true" />
                                                            <div class="parsley-errors-list"></div>
                                                            @error('product_name')
                                                                <div class="text-danger mt-2">{{ $message }}</div>
                                                            @enderror
                                                        </div>


                                                        <div class="col-lg-6">
                                                            <div class="mb-4">
                                                                <label class="form-label">Price *</label>
                                                                <input type="text" class="form-control" id="price"
                                                                    name="price" value="{{ old('price') }}"
                                                                    data-parsley-required="true" />
                                                                @error('price')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-4">
                                                                <label class="form-label">Stock</label>
                                                                <input type="text" class="form-control" id="stock"
                                                                    name="stock" value="{{ old('stock') }}"
                                                                    data-parsley-required="true" />
                                                                @error('stock')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <div class="mb-4">
                                                                <label class="form-label">Weight Type *</label>
                                                                <select class="form-select" id="weight_type"
                                                                    name="weight_type" data-parsley-required="true">
                                                                    {{-- <option value="gm" {{ old('weight_type') == 'gm' ? 'selected' : '' }}>gm</option> --}}
                                                                    <option value="kg"
                                                                        {{ old('weight_type') == 'kg' ? 'selected' : '' }}>
                                                                        kg</option>
                                                                    {{-- <option value="lb" {{ old('weight_type') == 'lb' ? 'selected' : '' }}>lb</option> --}}
                                                                </select>
                                                                @error('weight_type')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="mb-4">
                                                                <label class="form-label">Weight Value *</label>
                                                                <input type="text" class="form-control"
                                                                    id="weight_value" name="weight_value"
                                                                    value="{{ old('weight_value') }}"
                                                                    data-parsley-required="true" />
                                                                @error('weight_value')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label class="form-label">Height</label>
                                                                <input type="text" class="form-control"
                                                                    id="height" name="height"
                                                                    value="{{ old('height') }}"
                                                                    data-parsley-required="true" />
                                                                @error('height')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label class="form-label">Width</label>
                                                                <input type="text" class="form-control"
                                                                    id="width" name="width"
                                                                    value="{{ old('width') }}"
                                                                    data-parsley-required="true" />
                                                                @error('width')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label class="form-label">Length</label>
                                                                <input type="text" class="form-control"
                                                                    id="length" name="length"
                                                                    value="{{ old('length') }}"
                                                                    data-parsley-required="true" />
                                                                @error('length')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-12 d-flex justify-content-between">
                                                            <div class="mb-4">
                                                                <label class="form-check-label">Virtual Product</label>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="is_virtual" name="is_virtual"
                                                                        {{ old('is_virtual') ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="is_virtual">Mark as
                                                                        Virtual</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-check-label">Trending</label>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="trending" name="trending"
                                                                        {{ old('trending') ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="trending">Mark as
                                                                        Trending</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-4">
                                                                <label class="form-check-label">Featured</label>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="featured" name="featured"
                                                                        {{ old('featured') ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="featured">Mark as
                                                                        Featured</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-8">
                                                            <div id="variationContainer">
                                                            </div>

                                                            <!-- HTML Structure for the Popup -->
                                                            <div id="variationOptionPopup" style="display: none;">
                                                                <div class="popup-content">
                                                                    <h3>Enter Variation Option Details</h3>
                                                                    <form id="variationOptionForm">
                                                                        <input type="hidden" id="optionId">
                                                                        <input type="hidden" id="variationOptionId">
                                                                        <div>
                                                                            <label for="price">Price:</label>
                                                                            <input class="form-control" type="text"
                                                                                id="variation_price" name="variation_price">
                                                                        </div>
                                                                        <div>
                                                                            <label for="imageIds">Image IDs:</label>
                                                                            {{-- <input class="form-control" type="file" id="files"
                                                                            data-parsley-required="true" name="variation_option_images[]" multiple id="variation_option_images" name="variation_option_images"> --}}

                                                                            <input class="form-control" type="file" id="variation_option_images" name="variation_option_images[]" multiple>

                                                                        </div>
                                                                        <button class="btn btn-sm btn-light"
                                                                            id="saveOptionDetails">Save</button>
                                                                    </form>
                                                                    <button class="btn btn-sm btn-outline-danger"
                                                                        id="closePopup">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>




                                                    </div>


                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="form-label">Description *</label>
                                                    <textarea placeholder="Type here" class="form-control" rows="4" id="description" name="description" data-parsley-required="true">{{ old('description') }}</textarea>
                                                    @error('description')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <!-- Trending and Featured Checkboxes -->
                                            </div>
                                            <!-- SEO start -->
                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h4>SEO</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label for="meta_title" class="form-label">Meta Title
                                                                    *</label>
                                                                <input type="text" placeholder="Type here"
                                                                    class="form-control" id="meta_title"
                                                                    name="meta_title" data-parsley-required="true"
                                                                    value="{{ old('meta_title') }}" />
                                                                @error('meta_title')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label for="slug" class="form-label">Slug *</label>
                                                                <input type="text" placeholder="Type here"
                                                                    class="form-control" data-parsley-required="true"
                                                                    id="slug" name="slug"
                                                                    value="{{ old('slug') }}" />
                                                                @error('slug')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label for="meta_description" class="form-label">Meta
                                                                    description *</label>
                                                                <textarea placeholder="Type here" class="form-control" rows="4" data-parsley-required="true"
                                                                    id="meta_description" name="meta_description">{{ old('meta_description') }}</textarea>
                                                                @error('meta_description')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <div class="mb-4">
                                                                <label for="canonical_url" class="form-label">Canonical
                                                                    URL</label>
                                                                <input type="text" placeholder="Type here"
                                                                    class="form-control" id="canonical_url"
                                                                    data-parsley-required="true" name="canonical_url"
                                                                    value="{{ old('canonical_url') }}" />
                                                                @error('canonical_url')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- SEO end -->
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4>Upload Images</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="input-upload">
                                                {{-- <img src="assets/imgs/theme/upload.svg" alt="Upload Icon" id="upload-icon" /> --}}
                                                <input class="form-control" type="file" id="files"
                                                    data-parsley-required="true" name="images[]" multiple>
                                                <br><br>
                                                <div id="image-preview-container"></div>
                                                @error('images.*')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- card end// -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h4>Organization</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 gx-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category</label>
                                                        <select class="form-select" name="category_id" id="category_id"
                                                            data-parsley-required="true">
                                                            <option value="">Select Category</option>
                                                        </select>
                                                        @error('category_id')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <!-- row.// -->

                                                <div class="col-md-6 gx-2">
                                                    <div class="mb-3">
                                                        <label class="form-label">Brand</label>
                                                        <select class="form-select" id="brand" name="brand"
                                                            data-parsley-required="true">
                                                            <option value="">Select Brand</option>
                                                        </select>
                                                        @error('brand')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <!-- row.// -->

                                                <div class="col-md-6 gx-2">
                                                    <label class="form-label">Model</label>
                                                    <select class="form-select" id="product_model" name="product_model"
                                                        data-parsley-required="true">
                                                        <option value="">Select Model</option>
                                                    </select>
                                                    @error('product_model')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="col-md-6 gx-2">
                                                    <div class="mb-4">
                                                        <label class="form-label">SKU * </label>
                                                        <input type="text" class="form-control mb-2" id="sku" name="sku" value="{{ old('sku') }}" data-parsley-required="true" />
                                                        <button type="button" class="btn btn-sm btn-light" onclick="generateSKU()">Generate SKU</button>
                                                        @error('sku')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- row.// -->
                                        </div>
                                    </div>
                                    <!-- card end// -->
                                    <!-- card end// -->
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h4>Shipping</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="col-sm-12 mb-3">
                                                <label class="form-label">Select Shipping</label>
                                                <select class="form-select" id="shipping_id" name="shipping_id"
                                                    data-parsley-required="true">
                                                    <option value="">Select Model</option>
                                                </select>
                                                @error('shipping_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://cdn.tiny.cloud/1/k9rv9gl56bd9lhc7lqqb6mfhcxbpacbcsrenu0hfbxt1fvc6/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>

    {{-- <script src="https://cdn.ckeditor.com/ckeditor5/ckeditor.js"></script> --}}

    <script type="text/javascript">
        $(function() {

            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.show.product') }}",
                columns: [{
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'trending',
                        name: 'trending'
                    },
                    {
                        data: 'featured',
                        name: 'featured'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_date',
                        name: 'created_date'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });

        // image preview
        document.getElementById('files').addEventListener('change', function(event) {
            const fileInput = event.target;
            const files = fileInput.files;
            const previewContainer = document.getElementById('image-preview-container');

            // Clear previous previews
            previewContainer.innerHTML = '';

            Array.from(files).forEach((file, index) => {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Create a container for the image and the remove button
                        const imgContainer = document.createElement('div');
                        imgContainer.classList.add('img-container');

                        const imgElement = document.createElement('img');
                        imgElement.src = e.target.result;
                        imgElement.classList.add('preview-img');

                        const removeButton = document.createElement('button');
                        removeButton.textContent = 'Remove';
                        removeButton.classList.add('remove-btn');
                        removeButton.onclick = function() {
                            imgContainer.remove();
                            // Optional: Handle file removal if necessary
                        };

                        imgContainer.appendChild(imgElement);
                        imgContainer.appendChild(removeButton);
                        previewContainer.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });


        // Helper function to create a FileList from an array of files
        function createFileList(files) {
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            return dataTransfer.files;
        }


        $(document).on('click', '.change-status', function(e) {
            e.preventDefault();

            var status = $(this).data('status');
            var id = $(this).data('id');

            // Ask for confirmation
            if (confirm('Are you sure you want to change the status to ' + status + '?')) {
                // Send AJAX request to update the status
                $.ajax({
                    url: '{{ route('admin.update.promotionStatus') }}', // Using Laravel's route helper
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
                    console.log(data);

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

        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Initialize Parsley validation
            const form = $(this).parsley();
            const messageDiv = document.getElementById('show-error');
            messageDiv.innerHTML = '';
            $('#price').parsley().addConstraint('type', 'number');
            $('#stock').parsley().addConstraint('type', 'number');
            $('#weight_value').parsley().addConstraint('type', 'number');
            // Validate the form
            if (form.validate()) {
                const formData = new FormData(document.getElementById('productForm'));
                axios.post('{{ route('admin.product.store') }}', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then(response => {
                        notyf.success(response.data.success || "Product saved successfully!");
                        document.getElementById('productForm').reset();
                        $('.data-table').DataTable().ajax.reload(null, false);
                        $('#promotionModal').modal('hide');
                        messageDiv.innerHTML = '';
                        document.getElementById('image-preview-container').innerHTML = '';
                    })
                    .catch(error => {
                        // Handle error
                        if (error.response && error.response.data && error.response.data.errors) {
                            const errors = error.response.data.errors;
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    // Highlight invalid fields
                                    const input = document.querySelector(`#${key}`);
                                    if (input) {
                                        input.classList.add('is-invalid');
                                        const errorList = input.nextElementSibling;
                                        if (errorList && errorList.classList.contains('parsley-errors-list')) {
                                            errorList.innerHTML =
                                                `<div class="text-danger">${errors[key][0]}</div>`;
                                        }
                                    }
                                }
                            }
                        } else {
                            messageDiv.innerHTML =
                                '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
                        }
                    });
            } else {
                // Display message if validation fails
                messageDiv.innerHTML =
                    '<div class="alert alert-danger">Please fix the validation errors and try again.</div>';
            }
        });






        // document.getElementById('productForm').addEventListener('submit', function (e) {
        //     e.preventDefault();

        //     // Clear previous messages
        //     const messageDiv = document.getElementById('message');
        //     messageDiv.innerHTML = '';

        //     // Prepare form data
        //     const formData = new FormData(document.getElementById('productForm'));

        //     // Simple validation for required fields
        //     const requiredFields = ['product_name', 'price', 'weight_type', 'weight_value', 'description', 'meta_title', 'slug', 'category_id', 'brand', 'product_model', 'shipping_id'];
        //     let isValid = true;

        //     requiredFields.forEach(field => {
        //         const input = document.getElementById(field);
        //         if (!input.value) {
        //             input.classList.add('is-invalid');
        //             isValid = false;
        //         } else {
        //             input.classList.remove('is-invalid');
        //         }
        //     });

        //     if (!isValid) {
        //         messageDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
        //         return;
        //     }

        //     // Send data with Axios
        //     axios.post('{{ route('admin.product.store') }}', formData, {
        //         headers: {
        //             'Content-Type': 'multipart/form-data'
        //         }
        //     })
        //     .then(response => {
        //         // Handle success
        //         notyf.success(response.data.success || "Product saved successfully!");

        //         // Reset form fields
        //         document.getElementById('productForm').reset();

        //         // Optionally reload or update the products list
        //         $('.data-table').DataTable().ajax.reload(null, false);

        //         // Optionally close the modal after a successful save
        //         $('#promotionModal').modal('hide');
        //     })
        //     .catch(error => {
        //         // Handle error
        //         if (error.response && error.response.data && error.response.data.errors) {
        //             const errors = error.response.data.errors;
        //             let errorMessages = '<div class="alert alert-danger"><ul>';
        //             for (let key in errors) {
        //                 if (errors.hasOwnProperty(key)) {
        //                     // Highlight invalid fields
        //                     document.querySelectorAll(`[name="${key}"]`).forEach(el => el.classList.add('is-invalid'));
        //                     errorMessages += `<li>${errors[key][0]}</li>`;
        //                 }
        //             }
        //             errorMessages += '</ul></div>';
        //             messageDiv.innerHTML = errorMessages;
        //         } else {
        //             messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again later.</div>';
        //         }
        //     });
        // });



        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#description',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate ai mentions tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss markdown',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                tinycomments_mode: 'embedded',
                tinycomments_author: 'Author name',
                mergetags_list: [{
                        value: 'First.Name',
                        title: 'First Name'
                    },
                    {
                        value: 'Email',
                        title: 'Email'
                    },
                ],
                ai_request: (request, respondWith) => respondWith.string(() => Promise.reject(
                    "See docs to implement AI Assistant")),
            });


            // Pass categories data from Blade to JavaScript
            var categories = @json($categories);
            // Function to create the category options recursively
            function createCategoryOptions(categories, prefix = '') {
                let options = '';
                categories.forEach(function(category) {
                    options += `<option value="${category.id}">${prefix}${category.category_name}</option>`;
                    if (category.children_recursive && category.children_recursive.length > 0) {
                        options += createCategoryOptions(category.children_recursive, prefix + '--');
                    }
                });
                return options;
            }
            // Generate and insert the options into the select element
            document.getElementById('category_id').innerHTML += createCategoryOptions(categories);
            // Pass brand list data from Blade to JavaScript
            var brands = @json($brandList);
            // Function to create the brand options
            function createBrandOptions(brands) {
                let options = '';
                brands.forEach(function(brand) {
                    options += `<option value="${brand.id}">${brand.brand_name}</option>`;
                });
                return options;
            }
            // Generate and insert the options into the select element
            document.getElementById('brand').innerHTML += createBrandOptions(brands);

            // Pass model list data from Blade to JavaScript
            var models = @json($modelList);

            // Function to create the model options
            function createModelOptions(models) {
                let options = '';
                models.forEach(function(model) {
                    options += `<option value="${model.id}">${model.model_name}</option>`;
                });
                return options;
            }

            window.generateSKU = function() {
                const categorySelect = document.getElementById('category_id');
                const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
                const categoryPrefix = selectedCategory.value ? selectedCategory.text.substr(0, 3).toUpperCase() : '';
                const prefix = categoryPrefix.length === 3 ? categoryPrefix : 'CAT';
                const uniqueNumber = Math.floor(10000 + Math.random() * 90000);
                let sku = prefix + uniqueNumber;
                sku = sku.replace(/[^A-Z0-9]/g, '');
                document.getElementById('sku').value = sku;
            };



            // Generate and insert the options into the select element
            document.getElementById('product_model').innerHTML += createModelOptions(models);

            // shipping
            // Pass categories data from Blade to JavaScript
            var shippingList = @json($shippingList);
            console.log(shippingList);
            // Function to create the category options recursively
            function createShippingOptions(shippingList, prefix = '') {
                let options = '';
                shippingList.forEach(function(item) {
                    options += `<option value="${item.id}">${prefix}${item.shipping_class}</option>`;
                    if (item.children_recursive && item.children_recursive.length > 0) {
                        options += createShippingOptions(item.children_recursive, prefix + '--');
                    }
                });
                console.log(options);

                return options;
            }
            // Generate and insert the options into the select element
            document.getElementById('shipping_id').innerHTML += createShippingOptions(shippingList);

           // Pass the variations array to JavaScript
           const variations = @json($variations);

           // Container to append the variations
           const variationContainer = document.getElementById('variationContainer');

           // Loop through each variation
           variations.forEach(variation => {
               // Create a header for each variation name
               const nameHeader = document.createElement('h6');
               const nameCheckbox = document.createElement('input');
               nameCheckbox.type = 'checkbox';
               nameCheckbox.name = `variation[${variation.id}]`;
               nameCheckbox.value = variation.name;

               nameHeader.appendChild(nameCheckbox);
               nameHeader.appendChild(document.createTextNode(` ${variation.name}`));
               variationContainer.appendChild(nameHeader);

               // Loop through the options for each variation
               variation.options.forEach(option => {
                   const variationValueDiv = document.createElement('div');
                   variationValueDiv.className = 'variation-value-tag';

                   const valueCheckbox = document.createElement('input');
                   valueCheckbox.type = 'checkbox';
                   valueCheckbox.name = `option[${option.id}]`;
                   valueCheckbox.value = option.value;

                   const valueText = document.createTextNode(` ${option.value}`);
                   variationValueDiv.appendChild(valueCheckbox);
                   variationValueDiv.appendChild(valueText);

                   const removeButton = document.createElement('button');
                   removeButton.className = 'remove-variation-value';
                   removeButton.innerHTML = '&times;';
                   variationValueDiv.appendChild(removeButton);

                   variationContainer.appendChild(variationValueDiv);

                   // Add event listener to remove the value when the button is clicked
                   removeButton.addEventListener('click', function() {
                       variationValueDiv.remove();
                   });

                   // Add event listener to open the popup when the value is clicked
                   valueCheckbox.addEventListener('click', function() {

               $('#variationOptionForm').trigger("reset");

                       // Show the popup
                       document.getElementById('variationOptionPopup').style.display = 'block';

                       // Set the input fields with current option data
                       document.getElementById('optionId').value = option.id;
                       document.getElementById('variationOptionId').value = option.variation_id;
                   });
               });
           });

           // Handle Save button click in the popup
           document.getElementById('saveOptionDetails').addEventListener('click', function(event) {
               event.preventDefault(); // Prevent form submission

               const optionId = document.getElementById('optionId').value;
               const variationOptionId = document.getElementById('variationOptionId').value;
               const price = document.getElementById('variation_price').value;
               const images = document.getElementById('variation_option_images').files;

               // Collect image IDs or save images and get their IDs
               let imageIds = [];
               for (let i = 0; i < images.length; i++) {
                   // For simplicity, just push file names here. You might need to upload them and store their IDs.
                   imageIds.push(images[i].name);
               }

               // Convert the data to a JSON string or a string format you prefer
               const variationData = {
                   optionId: optionId,
                   variationOptionId: variationOptionId,
                   price: price,
                   images: imageIds
               };

               // Save data to a hidden input (or any other method you prefer)
               const input = document.createElement('input');
               input.type = 'hidden';
               input.name = `variation_data[${optionId}]`;
               input.value = JSON.stringify(variationData);

               variationContainer.appendChild(input);

               // Hide the popup after saving
               document.getElementById('variationOptionPopup').style.display = 'none';
               $('#variationOptionForm').trigger("reset");
               document.getElementById('optionId').value = '';
               document.getElementById('variationOptionId').value = '';
               document.getElementById('variation_price').value = '';
               document.getElementById('variation_option_images').value = '';
           });

           // Handle Close button click in the popup
           document.getElementById('closePopup').addEventListener('click', function() {
               document.getElementById('variationOptionPopup').style.display = 'none';
               $('#variationOptionForm').trigger("reset");
           });


        });


        //
        // delete promotion
        $(document).ready(function() {
            $(document).on('click', '.delete-promotion', function() {
                var promotionId = $(this).data('id');
                var token = $('meta[name="csrf-token"]').attr(
                'content'); // Ensure you have CSRF token in your meta tags

                if (confirm('Are you sure you want to delete this promotion?')) {
                    $.ajax({
                        url: '/admin/promotion/' + promotionId,
                        type: 'DELETE',
                        data: {
                            "_token": token, // CSRF token
                        },
                        success: function(response) {
                            notyf.success(response.success ||
                            "Promotion deleted successfully!"); // Show success notification
                            $('.data-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            notyf.error(
                                'An error occurred while trying to delete the promotion. Please try again.'
                                ); // Show error notification
                        }
                    });
                }
            });
        });
    </script>
@endsection
