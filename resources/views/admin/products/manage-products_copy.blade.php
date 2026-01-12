@extends('admin.dashboard')
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
                                    <th width="28%">Product</th>
                                    <th width="10%">Price</th>
                                    <th width="10%">Stock</th>
                                    <th width="10%">Sku</th>
                                    <th width="5%">Trending</th>
                                    <th width="5%">Featured</th>
                                    <th width="12%">Status</th>
                                    <th width="10%">Create Date</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

      @include('admin.products.product-model');

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

    // Array to hold all the tags
    let tags = [];

// Function to update the hidden input with JSON
function updateTagsJson() {
    document.getElementById('tags-json').value = JSON.stringify(tags);
}

function addTag(tag) {

    const tagInput = document.getElementById('tag-input');
    const tagValue = tag ? tag : tagInput.value.trim();

    if (tagValue === '') {
        alert('Please enter a tag.');
        return;
    }
    // Add the tag to the array
    tags.push(tagValue);
    updateTagsJson(); // Update the hidden input with the new tags array

    // Create a new div for the tag
    const tagDiv = document.createElement('div');
    tagDiv.classList.add('tag');
    tagDiv.innerHTML = `
        ${tagValue}
        <button type="button" class="remove-tag">×</button>
    `;

    // Append the new tag to the container
    const tagContainer = document.getElementById('tags-values-container');
    tagContainer.appendChild(tagDiv);

    // Clear the input field
    tagInput.value = '';

    // Add event listener to remove the tag when clicking the remove button
    tagDiv.querySelector('.remove-tag').addEventListener('click', function() {
        // Remove the tag from the array
        const index = tags.indexOf(tagValue);
        if (index !== -1) {
            tags.splice(index, 1); // Remove the tag from the array
        }
        updateTagsJson(); // Update the hidden input with the new array

        // Remove the tag div from the DOM
        tagDiv.remove();
    });
}

// Function to handle adding and displaying tags
document.getElementById('add-tag-button').addEventListener('click', function() {
    const tagInput = document.getElementById('tag-input');
    const tagValue = tagInput.value.trim();
    addTag(tagValue);
});


document.getElementById('product_name').addEventListener('input', function() {
    let productName = this.value;
    let metaTitleField = document.getElementById('meta_title');
    metaTitleField.value = productName;
    let slugField = document.getElementById('slug');
    let slugFeedback = document.getElementById('slug_feedback');

    // Generate the slug: convert to lowercase, replace spaces with hyphens, and remove non-alphanumeric characters
    let slug = productName.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove non-alphanumeric characters
                .trim()                         // Trim whitespace
                .replace(/\s+/g, '-')           // Replace spaces with hyphens
                .replace(/-+/g, '-');           // Ensure only one hyphen between words

    // AJAX call to check if the slug is unique
    fetch(`/check-slug-unique?slug=${slug}`)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                // slugFeedback.textContent = "Slug already exists. Slug will be changed to automatically generated or modify the product name.";
                slugFeedback.textContent = "Slug already exists. Automatically generated to a new slug or modify the product name.";
                slugFeedback.style.color = 'red';
                slugField.value = slug + '-' + Math.floor(Math.random() * 1000); // Add a random number to make it unique
            } else {
                slugFeedback.textContent = "Slug is available.";
                slugFeedback.style.color = 'green';
                slugField.value = slug;
            }
            // console.log('Feedback color:', slugFeedback.style.color); // Debugging: check color value
        })
        .catch(error => {
            console.error('Error checking slug uniqueness:', error);
        });
});


        document.getElementById('short_description').addEventListener('input', function() {
        var shortDescription = this.value;
        var charCount = shortDescription.length;
        var charCountElement = document.getElementById('char_count');

        charCountElement.textContent = charCount + "/150 characters used";

        // Additional validation if required
        if (charCount < 50 || charCount > 150) {
            charCountElement.textContent = charCount + "/150 characters used, type at least 50 characters and no more than 150 characters.";
            charCountElement.style.color = 'red';

        } else {
            charCountElement.style.color = 'green';
            charCountElement.textContent = charCount + "/150 characters used";
        }

        var meta_descriptionField = document.getElementById('meta_description');
        meta_descriptionField.value = shortDescription;
    });


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

        document.getElementById('files').addEventListener('change', function(event) {
    const fileInput = event.target;
    const newFiles = Array.from(fileInput.files); // Get the new files
    const previewContainer = document.getElementById('image-preview-container');

    // Create a DataTransfer object to manage the files
    const dt = new DataTransfer();

    // Keep track of previously selected files (if any)
    let previousFiles = Array.from(fileInput.previousFiles || []);

    // Append the new files to the previous ones
    const allFiles = [...previousFiles, ...newFiles];

    // Clear the preview container before re-rendering all images
    previewContainer.innerHTML = '';

    // Loop through all files (previous + new)
    allFiles.forEach((file, index) => {
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

                // Handle the remove functionality
                removeButton.onclick = function() {
                    imgContainer.remove(); // Remove the image preview

                    // Remove the file from the list and re-render the previews
                    const updatedFiles = allFiles.filter((f, i) => i !== index);
                    fileInput.previousFiles = updatedFiles; // Save the updated files
                    fileInput.files = createFileList(updatedFiles); // Update input files

                    // Clear and re-render the preview container
                    previewContainer.innerHTML = '';
                    updatedFiles.forEach((file, idx) => {
                        if (file && file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                // Re-create image container for each file
                                const imgContainer = document.createElement('div');
                                imgContainer.classList.add('img-container');

                                const imgElement = document.createElement('img');
                                imgElement.src = e.target.result;
                                imgElement.classList.add('preview-img');

                                const removeButton = document.createElement('button');
                                removeButton.textContent = 'Remove';
                                removeButton.classList.add('remove-btn');

                                // Attach the same remove functionality recursively
                                removeButton.onclick = function() {
                                    imgContainer.remove(); // Remove the image preview

                                    const newUpdatedFiles = updatedFiles.filter((f, j) => j !== idx);
                                    fileInput.previousFiles = newUpdatedFiles; // Save the updated files
                                    fileInput.files = createFileList(newUpdatedFiles); // Update input files
                                    previewContainer.innerHTML = ''; // Clear the previews
                                    newUpdatedFiles.forEach((f) => reader.readAsDataURL(f)); // Re-render the previews
                                };

                                imgContainer.appendChild(imgElement);
                                imgContainer.appendChild(removeButton);
                                previewContainer.appendChild(imgContainer);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                };

                // Append the image and remove button to the container
                imgContainer.appendChild(imgElement);
                imgContainer.appendChild(removeButton);
                previewContainer.appendChild(imgContainer);
            };

            reader.readAsDataURL(file);
            dt.items.add(file); // Add the file to the DataTransfer object
        }
    });

    // Update the input element's files with all files (previous + new)
    fileInput.previousFiles = allFiles; // Save the full list of files
    fileInput.files = createFileList(allFiles); // Update the file input
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


        function loadStoredVariations(variationsString) {
    // Parse the JSON string into an object
    let variations;
    try {
        variations = JSON.parse(variationsString);
    } catch (e) {
        console.error("Invalid JSON format", e);
        return; // Exit if there's an error
    }

    // Get all variation containers
    const variationContainers = document.querySelectorAll('#variationContainer');

    // Clear all previous selections and prices before loading new ones
    variationContainers.forEach(container => {
        // Clear all checkboxes (both parent and option checkboxes)
        const allCheckboxes = container.querySelectorAll('input[type="checkbox"]');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = false; // Uncheck all checkboxes
        });

        // Clear all price tags
        const allPriceTags = container.querySelectorAll('.price-tag');
        allPriceTags.forEach(priceTag => {
            priceTag.textContent = ''; // Clear any previous prices
        });
    });

    // Loop through each variation container
    variationContainers.forEach(container => {
        // Get all the parent variation checkboxes
        const parentCheckboxes = container.querySelectorAll('h6 > input[type="checkbox"]');

        // Get all the option checkboxes inside the container
        const optionCheckboxes = container.querySelectorAll('.options-container input[type="checkbox"]');

        // Loop through the variations data to check options and parent checkboxes
        variations.forEach(variation => {
            // Check for the corresponding parent variation checkbox
            parentCheckboxes.forEach(parentCheckbox => {
                if (parentCheckbox.value === variation.variation) {
                    let parentChecked = false;

                    // Loop through the options of this variation
                    variation.options.forEach(option => {
                        // Loop through option checkboxes to find matches
                        optionCheckboxes.forEach(optionCheckbox => {
                            if (optionCheckbox.value === option.option) {
                                // If the option is found, check the checkbox
                                optionCheckbox.checked = true;
                                parentChecked = true; // If any option is checked, mark the parent as needing to be checked
                                // console.log(`Checked option: ${optionCheckbox.name} - ${optionCheckbox.value}`);

                                // Find the closest .options-container and set display: block
                                const optionsContainer = optionCheckbox.closest('.options-container');
                                if (optionsContainer) {
                                    optionsContainer.style.display = 'block';
                                    // console.log(`Displayed options-container for: ${optionCheckbox.value}`);
                                }

                                // Add the price to the price tag span
                                const priceTag = optionCheckbox.closest('.variation-value-tag').querySelector('.price-tag');
                                if (priceTag) {
                                    priceTag.textContent = `$${option.price}`; // Add price to span
                                    // console.log(`Added price for ${option.option}: $${option.price}`);
                                }
                            }
                        });
                    });

                    // Check the parent variation checkbox if any options were checked
                    if (parentChecked) {
                        parentCheckbox.checked = true;
                        // console.log(`Checked parent variation: ${parentCheckbox.name} - ${parentCheckbox.value}`);
                    }
                }
            });
        });
    });
}

        function editProduct(productId) {
            document.getElementById('image-preview-container').innerHTML = '';
            document.getElementById('slug_feedback').innerHTML = '';
            document.getElementById('char_count').innerHTML = '';


       axios.get(`/admin/product/${productId}`)
        .then(response => {
            const product = response.data.product;
            const seo = product.seo_config;
            const images = response.data.images;
            console.log("Product data:", seo);

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
            setElementValue('short_description', product.short_description);
            setElementValue('meta_title', product.meta_title);
            setElementValue('meta_description', product.short_description);

            // Check if TinyMCE is initialized before setting content
            if (tinymce.get('description')) {
                tinymce.get('description').setContent(product.description);
            } else {
                console.warn("TinyMCE editor 'description' not initialized.");
            }

            setElementValue('category_id', product.category_id);
            product.brand_id && setElementValue('brand', product.brand_id);
            product.model_id && setElementValue('product_model', product.model_id);
            setElementValue('sku', product.sku);
            setElementValue('shipping_id', product.shipping_rate_id);

            // Ensure 'files' exists before removing attribute
            const filesElement = document.getElementById('files');
            if (filesElement) {
                filesElement.removeAttribute('data-parsley-required');
            }

            // Populate SEO fields with seo data
            // if (seo) {
            //     setElementValue('meta_title', seo.meta_title);
            //     setElementValue('slug', seo.slug);
            //     setElementValue('meta_description', seo.meta_description);
            //     setElementValue('canonical_url', seo.canonical_url);

            //     const tagInput = document.getElementById('tag-input');
            //     let tags = JSON.parse(seo.tags);
            //     tags.forEach(tag => {
            //         addTag(tag);
            //     })

            // }

            if (seo) {
    setElementValue('meta_title', seo.meta_title);
    setElementValue('slug', seo.slug);
    setElementValue('meta_description', seo.meta_description);
    setElementValue('canonical_url', seo.canonical_url);

    const tagInput = document.getElementById('tag-input');

    // Check if seo.tags exists and is valid JSON
    let tags = [];
    try {
        if (seo.tags) {
            tags = JSON.parse(seo.tags);
        }
    } catch (error) {
        console.error("Invalid JSON for tags:", error);
    }

    // Ensure tags is an array before using forEach
    if (Array.isArray(tags)) {
        tags.forEach(tag => {
            addTag(tag);
        });
    } else {
        console.warn("Tags is not an array:", tags);
    }
}


            // Handle checkboxes safely
            setElementValue('is_virtual', product.is_virtual, true);
            setElementValue('trending', product.trending, true);
            setElementValue('featured', product.featured, true);

            // Load existing images and preserve previous ones
            function loadExistingImages(images) {
                const imageContainer = document.getElementById('image-preview-container');
                // console.log('existing images:', images);

                if (imageContainer) {
                    images.forEach(image => {
                        const imgContainer = document.createElement('div');
                        imgContainer.classList.add('img-container');

                        const imgElement = document.createElement('img');
                        imgElement.src = `${image}`; // Adjust the path as needed
                        imgElement.classList.add('preview-img');
                        imgElement.alt = 'Product Image';

                        const removeButton = document.createElement('button');
                        removeButton.textContent = 'Remove';
                        removeButton.classList.add('remove-btn');
                        removeButton.onclick = function() {
                            imgContainer.remove();
                            // Optional: Handle file removal logic
                        };

                        imgContainer.appendChild(imgElement);
                        imgContainer.appendChild(removeButton);
                        imageContainer.appendChild(imgContainer);
                    });
                } else {
                    console.warn("Image preview container not found.");
                }
            }

            // Load existing images without clearing the container
            loadExistingImages(images);

            const storedVariations = JSON.parse(product.variations);
            loadStoredVariations(storedVariations);


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
                let variations = [];

                document.querySelectorAll('#variationContainer h6 input:checked').forEach(function(variationCheckbox) {
                    let variationName = variationCheckbox.value; // e.g., "Color" or "Size"
                    let optionsContainer = variationCheckbox.closest('h6').nextElementSibling;

                    let selectedOptions = [];

                    optionsContainer.querySelectorAll('.variation-value-tag input:checked').forEach(function(optionCheckbox) {
                        let optionName = optionCheckbox.value; // e.g., "Black" or "M"

                        // Correctly targeting the price within the variation-value-tag
                        let priceElement = optionCheckbox.closest('.variation-value-tag').querySelector('.price-tag');
                        let price = priceElement ? priceElement.innerText.replace('Price: $', '').trim() : null;

                        selectedOptions.push({
                            option: optionName,
                            price: price
                        });
                    });

                    if (selectedOptions.length > 0) {
                        variations.push({
                            variation: variationName,
                            options: selectedOptions
                        });
                    }
                });

                document.getElementById('variation_values').value = JSON.stringify(variations);



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
                        document.getElementById('slug_feedback').innerHTML = '';
                        document.getElementById('char_count').innerHTML = '';
                        setTimeout(() => {
                            // location.reload();
                        }, 500);

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
                            console.log(error.response);

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

            window.generateSKU = async function() {
                const categorySelect = document.getElementById('category_id');
                const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
                let categoryText = selectedCategory.value ? selectedCategory.text : 'SKU';

                // Remove spaces and hyphens, then take the first 3 characters and convert to uppercase
                const categoryPrefix = categoryText.replace(/[\s-]/g, '').substr(0, 3).toUpperCase();

                // Generate SKU prefix
                const prefix = categoryPrefix.length === 3 ? categoryPrefix : 'SKU';

                try {
                    // Fetch unique SKU from the backend
                    const response = await fetch('/generate-unique-sku?prefix=' + prefix);
                    const data = await response.json();


                    if (data.sku) {
                        document.getElementById('sku').value = data.sku;
                        document.getElementById('skuFeedback').textContent = "Generated SKU: " + data.sku;
                        document.getElementById('skuFeedback').style.color = 'green';
                    } else {
                        document.getElementById('skuFeedback').textContent = "Error generating SKU.";
                        document.getElementById('skuFeedback').style.color = 'red';
                    }
                } catch (error) {
                    document.getElementById('skuFeedback').textContent = "Error generating SKU.";
                    document.getElementById('skuFeedback').style.color = 'red';
                    console.error('Error:', error);
                }
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

                // Create a div to hold options (hidden by default)
                const optionsContainer = document.createElement('div');
                optionsContainer.className = 'options-container';
                variationContainer.appendChild(optionsContainer);

                // Toggle options visibility when clicking the variation checkbox
                nameCheckbox.addEventListener('click', function() {
                    optionsContainer.style.display = nameCheckbox.checked ? 'block' : 'none';
                });

                // Loop through the options for each variation
                variation.options.forEach(option => {
                    const variationValueDiv = document.createElement('div');
                    variationValueDiv.className = 'variation-value-tag';

                    const valueCheckbox = document.createElement('input');
                    valueCheckbox.type = 'checkbox';
                    valueCheckbox.classList.add('option-checkbox');
                    valueCheckbox.name = `option[${option.id}]`;
                    valueCheckbox.value = option.value;

                    const valueText = document.createTextNode(` ${option.value}`);
                    variationValueDiv.appendChild(valueCheckbox);
                    variationValueDiv.appendChild(valueText);

                    const priceTag = document.createElement('span');
                    priceTag.className = 'price-tag badge bg-primary';
                    // priceTag.textContent = `Price: $0.00`; // Default price
                    variationValueDiv.appendChild(priceTag);

                    const addPriceButton = document.createElement('button');
                    addPriceButton.className = 'btn-add-price';
                    addPriceButton.innerHTML = '<small style="font-size: 11px;" class="border border-primary p-1 rounded">Add price</small>';
                    variationValueDiv.appendChild(addPriceButton);

                    const removeButton = document.createElement('button');
                    removeButton.className = 'remove-variation-value';
                    removeButton.innerHTML = '&times;';
                    removeButton.title = 'Remove option';
                    variationValueDiv.appendChild(removeButton);

                    optionsContainer.appendChild(variationValueDiv);

                    // Event listener to remove the value when the button is clicked
                    removeButton.addEventListener('click', function() {
                        variationValueDiv.remove();
                    });

                    // Open popup to edit price when Add Price button is clicked
                    addPriceButton.addEventListener('click', function(event) {
                        event.preventDefault();
                        document.getElementById('invalid-optionPrice-feedback').innerText = '';
                        document.getElementById('variationOptionPopup').style.display = 'flex';
                        document.getElementById('optionId').value = option.id;
                        document.getElementById('variationOptionId').value = option.variation_id;

                        // Set current price in the input field
                        document.getElementById('variation_price').value = parseFloat(priceTag.textContent.replace('Price: $', '')) || '';
                    });

                    // Handle Save button click in the popup
                    document.getElementById('saveOptionDetails').addEventListener('click', function(event) {
                        event.preventDefault();

                        const optionId = document.getElementById('optionId').value;
                        const price = document.getElementById('variation_price').value;

                        if (price === '') {
                            document.getElementById('variation_price').focus();
                            document.getElementById('invalid-optionPrice-feedback').innerText = 'Please enter a valid price.';
                            return;
                        } else if (isNaN(price)) {
                            document.getElementById('invalid-optionPrice-feedback').innerText = 'Please enter a valid price.';
                            return;
                        }
                        const optionDiv = Array.from(optionsContainer.children).find(child => child.querySelector(`input[name="option[${optionId}]"]`));
                        const priceTag = optionDiv.querySelector('.price-tag');

                        // Update price tag
                        priceTag.textContent = `Price: $${parseFloat(price).toFixed(2)}`;
                        document.getElementById('variationOptionPopup').style.display = 'none';
                        $('#variationOptionForm').trigger("reset");
                    });

                    // Handle Close button click in the popup
                    document.getElementById('closePopup').addEventListener('click', function() {
                        document.getElementById('variationOptionPopup').style.display = 'none';
                        $('#variationOptionForm').trigger("reset");
                    });
                });
            });
        });


        //
        // delete promotion
        $(document).ready(function() {
            $(document).on('click', '.delete-product', function() {
                var promotionId = $(this).data('id');
                var token = $('meta[name="csrf-token"]').attr('content'); // Ensure you have CSRF token in your meta tags

                if (confirm('Are you sure you want to delete this product?')) {
                    $.ajax({
                        url: '/admin/product-delete/' + promotionId,
                        type: 'DELETE',
                        data: {
                            "_token": token, // CSRF token
                        },
                        success: function(response) {
                            if (response.success) {
                                notyf.success(response.success); // Show success notification
                            } else {
                                notyf.error("Couldn't delete!");
                            }
                            $('.data-table').DataTable().ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            notyf.error('An error occurred while trying to delete the product. Please try again.'); // Show error notification
                        }
                    });
                }
            });
        });

    </script>
@endsection
