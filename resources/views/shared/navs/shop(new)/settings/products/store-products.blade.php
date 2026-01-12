
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
                                    <th width="10">Product</th>
                                    <th width="10">Price</th>
                                    <th width="10">Stock</th>
                                    <th width="10">Sku</th>
                                    <th width="10">trending</th>
                                    <th width="10">featured</th>
                                    <th width="10">Status</th>
                                    <th width="10">Create Date</th>
                                    <th width="20">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        </div>
    </section>


@section('vendor_product_script')
<script type="text/javascript">
    $(function() {

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('vendor.show.product') }}",
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
                    orderable: true,
                    searchable: true
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

    // $(document).on('click', '.edit-promotion', function() {
    //     var promotionId = $(this).data('id');
    //     $.ajax({
    //         url: '/admin/promotion/' + promotionId + '/edit',
    //         method: 'GET',
    //         success: function(data) {
    //             console.log(data);

    //             $('#promotionForm')[0].reset();

    //             // Populate the form with the existing promotion data
    //             $('#promotion_name').val(data.promotion_name);
    //             $('#promotion_code').val(data.promotion_code);
    //             $('#start_date').val(data.start_date);
    //             $('#end_date').val(data.end_date);
    //             $('#discount_percentage').val(data.discount_percentage);
    //             $('#min_purchase_amount').val(data.min_purchase_amount);
    //             $('#max_discount').val(data.max_discount);
    //             $('#status').val(data.status);
    //             $('#promotional_id').val(data.id);

    //             // Set form action and method for update
    //             $('#promotionForm').attr('action', '/admin/promotion/' + promotionId);
    //             $('#promotionForm').attr('method', 'POST');

    //             $('#promotionModal').modal('show');
    //         },
    //         error: function(response) {
    //             notyf.error('Error fetching promotion data. Please try again later.');
    //         }
    //     });
    // });

    // Assume product.variations is passed from your backend


    // Function to load existing variations when editing a product
    function loadExistingVariations(productVariations) {
        try {
            // Check if productVariations is valid and parse if necessary
            if (productVariations && typeof productVariations === 'string') {
                productVariations = JSON.parse(productVariations);
            }

            // Ensure productVariations is now an object
            if (productVariations && typeof productVariations === 'object') {
                // Iterate over the outer object
                Object.keys(productVariations).forEach(key => {
                    let variation = productVariations[key];

                    // Parse the nested JSON string if it's still a string
                    if (variation && typeof variation === 'string') {
                        variation = JSON.parse(variation);
                    }

                    // Ensure variation is a valid object
                    if (variation && typeof variation === 'object') {
                        const variationContainer = document.getElementById('variationContainer');

                        // Create a header for each variation name
                        const nameHeader = document.createElement('h6');
                        const nameCheckbox = document.createElement('input');
                        nameCheckbox.type = 'checkbox';
                        nameCheckbox.name = `variation[${variation.optionId}]`;  // Use optionId for unique identification
                        nameCheckbox.value = variation.optionId;

                        nameHeader.appendChild(nameCheckbox);
                        nameHeader.appendChild(document.createTextNode(` Variation ID: ${variation.optionId}`));
                        variationContainer.appendChild(nameHeader);

                        // Ensure variation.images is an array
                        if (Array.isArray(variation.images)) {
                            // Assuming images is an array of image filenames
                            variation.images.forEach(image => {
                                const variationValueDiv = document.createElement('div');
                                variationValueDiv.className = 'variation-value-tag';

                                const imgElement = document.createElement('img');
                                imgElement.src = `/storage/${image}`;
                                imgElement.alt = `Product Image for Variation ${variation.optionId}`;
                                imgElement.width = 100;  // Adjust size as needed

                                variationValueDiv.appendChild(imgElement);

                                const removeButton = document.createElement('button');
                                removeButton.className = 'remove-variation-value';
                                removeButton.innerHTML = '&times;';
                                variationValueDiv.appendChild(removeButton);

                                variationContainer.appendChild(variationValueDiv);

                                // Add event listener to remove the value when the button is clicked
                                removeButton.addEventListener('click', function() {
                                    variationValueDiv.remove();
                                });
                            });
                        }
                    }
                });
            } else {
                console.error('productVariations is not a valid object:', productVariations);
            }
        } catch (error) {
            console.error('Error processing product variations:', error);
        }
    }





    function editProduct(productId) {
        axios.get(`/admin/product/${productId}`)
            .then(response => {
                const product = response.data.product;
                const seo = product.seo_config;
                const images = response.data.images;
                console.log(product);

                // Function to safely set element value
                function setElementValue(elementId, value, isCheckbox = false) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        if (isCheckbox) {
                            element.checked = value;
                        } else {
                            element.value = value || '';
                        }
                    } else {
                        console.warn(`Element with id '${elementId}' not found.`);
                    }
                }

                // Populate form fields with product data
                setElementValue('product_id', product.id);
                setElementValue('product_name', product.product_name);
                setElementValue('price', product.price);
                setElementValue('stock', product.stock);
                setElementValue('weight_type', product.weight_name);
                setElementValue('weight_value', product.weight_value);
                setElementValue('height', product.height);
                setElementValue('width', product.width);
                setElementValue('length', product.length);
                tinymce.get('description').setContent(product.description);

                setElementValue('category_id', product.category_id);
                setElementValue('brand', product.brand_id);
                setElementValue('product_model', product.model_id);
                setElementValue('sku', product.sku);
                setElementValue('shipping_id', product.shipping_rate_id);

                // document.getElementById('description').removeAttribute('data-parsley-required');
                document.getElementById('files').removeAttribute('data-parsley-required');

                // Populate SEO fields with seo data
                if (seo) {
                    console.log(product, seo);
                    document.getElementById('meta_title').value = seo.meta_title;
                    document.getElementById('slug').value = seo.slug;
                    document.getElementById('meta_description').value = seo.meta_description;
                    document.getElementById('canonical_url').value = seo.canonical_url;
                }

                // Handle checkboxes
                document.getElementById('is_virtual').checked = product.is_virtual;
                document.getElementById('trending').checked = product.trending;
                document.getElementById('featured').checked = product.featured;

                function loadExistingImages(images) {
                    const imageContainer = document.getElementById('image-preview-container');
                    imageContainer.innerHTML = ''; // Clear existing images

                    images.forEach(image => {
                        const imgContainer = document.createElement('div');
                        imgContainer.classList.add('img-container');

                        const imgElement = document.createElement('img');
                        imgElement.src = `/storage/${image}`; // Adjust the path as needed
                        imgElement.classList.add('preview-img');
                        imgElement.alt = 'Product Image';

                        const removeButton = document.createElement('button');
                        removeButton.textContent = 'Remove';
                        removeButton.classList.add('remove-btn');
                        removeButton.onclick = function() {
                            imgContainer.remove();
                            // Optional: Handle file removal logic, e.g., removing from server
                        };

                        imgContainer.appendChild(imgElement);
                        imgContainer.appendChild(removeButton);
                        imageContainer.appendChild(imgContainer);
                    });
                }
                loadExistingImages(images);

                loadExistingVariations(product.variations);


            })
            .catch(error => {
                console.error(error);
            });
    }



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


    document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
        selector: '#description',
        setup: function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });

    // Ensure the content is updated before form submission
    document.querySelector('form').addEventListener('submit', function () {
        tinymce.triggerSave();
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
        // Function to create the category options recursively
        function createShippingOptions(shippingList, prefix = '') {
            let options = '';
            shippingList.forEach(function(item) {
                options += `<option value="${item.id}">${prefix}${item.shipping_class}</option>`;
                if (item.children_recursive && item.children_recursive.length > 0) {
                    options += createShippingOptions(item.children_recursive, prefix + '--');
                }
            });

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
                   document.getElementById('variationOptionPopup').style.display = 'block';
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
