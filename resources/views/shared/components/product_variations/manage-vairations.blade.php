@extends('layouts.app')
@section('title', 'NidusCart - Manage Product Variations')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Variations</h2>
            <p>Manage Variations</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
           <div class="row justify-content-around">
            <div class="col-md-3">
                <h6>Add Variation</h6>
                <div id="message-div"></div>
                <form method="POST" id="variation-form">
                    @csrf
                    {{-- <input type="hidden" name="variation_id" id="variation_form_id"> --}}
                    <div class="mb-4">
                        <label for="name" class="form-label">Variation Name</label>
                        <input type="text" placeholder="Type here" class="form-control" name="name" value="" id="name">
                    </div>
                    <div class="mb-4">
                        <label for="variation_value" class="form-label">Variation Values</label>
                        <div class="variation-tags-container">
                            <input type="text" placeholder="Enter variation value" name="values" class="form-control variation-input" id="variation-input">
                            <button type="button" class="btn btn-secondary" id="add-variation-value">Add</button>
                        </div>
                        <div id="variation-values-container" class="variation-values-container"></div>
                    </div>
                    <div class="">
                        <button type="submit" class="btn btn-primary">Add Variation</button>
                    </div>
                </form>

            </div>

            <div class="col-md-8 card p-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive data-table">
                        <thead>
                            <tr>
                                <th width="20%">Variation Name</th>
                                <th width="60%">Values</th>
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
    </div>


    <!-- Edit Promotion Modal -->
<div class="modal fade" id="editPromotionModal" tabindex="-1" aria-labelledby="editPromotionLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editPromotionLabel">Edit Variation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="message-div-mod"></div>
          <form method="POST" id="editVariationForm">
            @csrf
            <input type="hidden" id="variation_id" name="variation_id">

            <div class="mb-3">
              <label for="name" class="form-label">Variation Name</label>
              <input type="text" class="form-control" id="name-modify" name="name">
            </div>

            <div class="mb-4">
                <label for="variation_value" class="form-label">Variation Values</label>
                <div class="variation-tags-container">
                    <input type="text" placeholder="Enter variation value" name="values-modify" class="form-control variation-input" id="variation-input-modify">
                    <button type="button" class="btn btn-secondary" id="add-variation-value-modify">Add</button>
                </div>
                <div id="variation-values-container-modify" class="variation-values-container-modify"></div>
            </div>
            <!-- Add more fields as needed -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
          </form>
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
            ajax: "{{ route('variations.index') }}",
            columns: [
                {data: 'name', name: 'name'},
                {data: 'values', name: 'values'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });

    document.getElementById('add-variation-value').addEventListener('click', function() {
    var variationInput = document.getElementById('variation-input');
    var variationValue = variationInput.value.trim();

    if (variationValue !== "") {
        // Capitalize the first letter
        variationValue = variationValue.charAt(0).toUpperCase() + variationValue.slice(1);

        var newTag = document.createElement('div');
        newTag.classList.add('variation-tag');
        newTag.innerHTML = `
            ${variationValue}
            <button type="button" class="remove-tag">×</button>
            <input type="hidden" name="variation_values[]" value="${variationValue}">
        `;

        // Add the new tag to the container
        document.getElementById('variation-values-container').appendChild(newTag);

        // Clear the input field
        variationInput.value = "";

        // Add event listener to remove tag
        newTag.querySelector('.remove-tag').addEventListener('click', function() {
            newTag.remove();
        });
    }
});

    $(document).on('click', '.edit-promotion', function() {
        // Get the promotion ID from the data attribute
        document.getElementById('variation-values-container-modify').innerHTML = '';

        let promotionId = $(this).data('id');

        // Send an Axios GET request to fetch the promotion data
        axios.get(`/variations/${promotionId}/edit`)
            .then(function(response) {
                // Populate the modal fields with the fetched data
                $('#variation_id').val(response.data[0].id);
                $('#name-modify').val(response.data[0].name);
                // $('#variation-input-modify').val(response.data[0].options);
                response.data[0].options.map(item => {
                    let newTag = document.createElement('div');
                    newTag.classList.add('variation-tag');
                    newTag.innerHTML = `
                        ${item.value}
                        <button type="button" class="remove-tag remove-tag-db" data-id="${item.id}">×</button>
                        <input type="hidden" name="variation_values_mod[]" value="${item.value}">
        `;

        // Add event listener to remove tag
        newTag.querySelector('.remove-tag-db').addEventListener('click', function() {
            let optionId = this.getAttribute('data-id');

            // Confirm before deleting
            if (confirm('Are you sure you want to remove this variation option?')) {
                axios.delete(`/variation-options/${optionId}`)
                    .then(function(response) {
                        if (response.data.success) {
                            newTag.remove();
                            notyf.success("Variation option removed successfully!");
                            $('.data-table').DataTable().ajax.reload(null, false);
                        }
                    })
                    .catch(function(error) {
                        console.error(error);
                        notyf.error("Failed to remove the variation option.");
                    });
            }
        });

        // Add the new tag to the container
        document.getElementById('variation-values-container-modify').appendChild(newTag);
    });

    document.getElementById('add-variation-value-modify').addEventListener('click', function() {
        let variationInput = document.getElementById('variation-input-modify');
        let variationValue = variationInput.value.trim();

        if (variationValue !== "") {
            // Capitalize the first letter
            variationValue = variationValue.charAt(0).toUpperCase() + variationValue.slice(1);

            let newTag = document.createElement('div');
            newTag.classList.add('variation-tag');
            newTag.innerHTML = `
                ${variationValue}
                <button type="button" class="remove-tag">×</button>
                <input type="hidden" name="variation_values-modify[]" value="${variationValue}">
            `;
            document.getElementById('variation-values-container-modify').appendChild(newTag);
            variationInput.value = "";
            newTag.querySelector('.remove-tag').addEventListener('click', function() {
                newTag.remove();
            });
        }
    });

        // Show the modal
        $('#editPromotionModal').modal('show');
    })
    .catch(function(error) {
        console.error(error);
        // Handle the error, e.g., show an alert or log to console
    });
});

    // Create variation
document.getElementById('variation-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    let formData = new FormData(this); // Use 'this' to refer to the form
    let isValid = true;
    let messageDiv = document.getElementById('message-div'); // Assume there's a div to display messages

    // Custom validation for required fields
    const requiredFields = ['name'];
    requiredFields.forEach(field => {
        if (!formData.get(field)) {
            document.getElementById(field).classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById(field).classList.remove('is-invalid');
        }
    });

    // Check if there are any variation values
    let variationValues = [];
    document.querySelectorAll('input[name="variation_values[]"]').forEach(function(input) {
        variationValues.push(input.value);
    });

    if (variationValues.length === 0) {
        isValid = false;
        messageDiv.innerHTML = '<div class="alert alert-danger">Please add at least one variation value.</div>';
        return;
    } else {
        messageDiv.innerHTML = ''; // Clear error message if variations are present
    }

    if (!isValid) {
        messageDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
        return;
    }

    // Axios request
    axios.post('/variations/store', {
            name: formData.get('name'),
            variation_values: variationValues
        })
        .then(function(response) {
            // Success notification
            notyf.success(response.data.success || "Variation saved successfully!");
            $('.data-table').DataTable().ajax.reload(null, false);
            // Reset form fields
            document.getElementById('variation-form').reset();
            // Clear dynamically added tags
            document.getElementById('variation-values-container').innerHTML = '';
            // Clear any error messages
            messageDiv.innerHTML = '';
        })
        .catch(function(error) {
            console.error(error);
            // Handle error response (optional)
            messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        });
});

    // update variation
    // document.getElementById('editVariationForm').addEventListener('submit', function(event) {

    //     event.preventDefault(); // Prevent the default form submission

    //     let formData = new FormData(this);
    //     let isValid = true;
    //     let messageDiv = document.getElementById('message-div-mod'); // Assume there's a div to display messages

    //     // Custom validation for required fields
    //     const requiredFields = ['name'];
    //     requiredFields.forEach(field => {
    //         if (!formData.get(field)) {
    //             document.getElementById(field).classList.add('is-invalid');
    //             isValid = false;
    //         } else {
    //             document.getElementById(field).classList.remove('is-invalid');
    //         }
    //     });

    //     // Check if there are any variation values
    //     let variationValues = [];
    //     document.querySelectorAll('input[name="variation_values_mod[]"]').forEach(function(input) {
    //         variationValues.push(input.value);
    //     });

    //     if (variationValues.length === 0) {
    //         isValid = false;
    //         messageDiv.innerHTML = '<div class="alert alert-danger">Please add at least one variation value.</div>';
    //         return;
    //     } else {
    //         messageDiv.innerHTML = ''; // Clear error message if variations are present
    //     }

    //     if (!isValid) {
    //         messageDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
    //         return;
    //     }

    //     // Axios request
    //     axios.post('/variations/update', {
    //             name: formData.get('name'),
    //             variation_values: variationValues
    //         })
    //         .then(function(response) {
    //             $('#editVariationModal').modal('hide');

    //             // Success notification
    //             notyf.success(response.data.success || "Variation saved successfully!");
    //             $('.data-table').DataTable().ajax.reload(null, false);
    //             document.getElementById('editVariationForm').reset();
    //             document.getElementById('variation-values-container-modify').innerHTML = '';
    //             messageDiv.innerHTML = '';
    //         })
    //         .catch(function(error) {
    //             console.error(error);
    //             // Handle error response (optional)
    //             messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    //         });
    // });


    $(document).on('click', '.destroy-variation', function() {
                    var variationId = $(this).data('id');
                    var token = $('meta[name="csrf-token"]').attr('content'); // Ensure you have CSRF token in your meta tags

                    if (confirm('Are you sure you want to delete this variation?')) {
                        $.ajax({
                            url: '/variation/' + variationId,
                            type: 'DELETE',
                            data: {
                                "_token": token, // CSRF token
                            },
                            success: function(response) {
                                notyf.success(response.success || "Variation deleted successfully!"); // Show success notification
                                $('.data-table').DataTable().ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                notyf.error('An error occurred while trying to delete the variation. Please try again.'); // Show error notification
                            }
                        });
                    }
                });


    document.getElementById('editVariationForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission

        let formData = new FormData(this);
        let variationId = formData.get('variation_id');
        let isValid = true;
        let messageDiv = document.getElementById('message-div-mod'); // Assume there's a div to display messages

        // Custom validation for required fields
        const requiredFields = ['name'];
        requiredFields.forEach(field => {
            if (!formData.get(field)) {
                document.getElementById(field + '-modify').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById(field + '-modify').classList.remove('is-invalid');
            }
        });

        // Check if there are any variation values
        let variationValues = [];
        document.querySelectorAll('input[name="variation_values-modify[]"]').forEach(function(input) {
            variationValues.push(input.value);
        });

        if (variationValues.length === 0) {
            isValid = false;
            messageDiv.innerHTML = '<div class="alert alert-danger">Please add at least one variation value.</div>';
            return;
        } else {
            messageDiv.innerHTML = ''; // Clear error message if variations are present
        }

        if (!isValid) {
            messageDiv.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
            return;
        }

        // Axios request
        axios.post('/variations/update', {
                variation_id: variationId,
                name: formData.get('name'),
                variation_values: variationValues
            })
            .then(function(response) {
                $('#editPromotionModal').modal('hide');

                // Success notification
                notyf.success(response.data.success || "Variation updated successfully!");
                $('.data-table').DataTable().ajax.reload(null, false);
                document.getElementById('editVariationForm').reset();
                document.getElementById('variation-values-container-modify').innerHTML = '';
                messageDiv.innerHTML = '';
            })
            .catch(function(error) {
                console.error(error);
                // Handle error response (optional)
                messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            });
    });


</script>


@endsection
