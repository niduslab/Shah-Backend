@extends('layouts.app')
@section('title', 'NidusCart - Store Policies')

@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title"><h1>Store Policies</h1></h2>
            <p>Manage Policies</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <button class="btn btn-sm btn-light mb-2" id="addPolicyBtn">Add Policy</button>

            <table class="table table-bordered" id="policiesTable">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="10%">Policy Type</th>
                        <th width="40%">Policy Text</th>
                        <th width="10%">Shop</th>
                        <th width="10%">Created By</th>
                        <th width="10%">Default Policy</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal for adding and editing policies -->
    @include('shared.shop.settings.policies.edit_policies_model')

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/k9rv9gl56bd9lhc7lqqb6mfhcxbpacbcsrenu0hfbxt1fvc6/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        $(document).ready(function () {
            const notyf = new Notyf();

            var table = $('#policiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('store_policies.index') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'policy_type', name: 'policy_type' },
                    { data: 'policy_text', name: 'policy_text' },
                    { data: 'shop', name: 'shop' },
                    { data: 'user', name: 'user' },
                    { data: 'is_default', name: 'is_default' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            // Initialize TinyMCE
            tinymce.init({
                selector: '#policy_text',
                menubar: false
            });

            // Add Policy Button
            $('#addPolicyBtn').on('click', function () {
                $('#editPolicyForm')[0].reset();
                tinymce.get('policy_text').setContent('');
                $('#editPolicyModalLabel').text('Add New Policy');
                $('#policy_id').val('');
                $('#savePolicyBtn').data('action', 'create');
                $('#editPolicyModal').modal('show');
            });

            // Handle Edit Button Click
            $(document).on('click', '.edit-policy', function () {
                const policyId = $(this).data('id');
                axios.get(`/store_policies/${policyId}/edit`).then(response => {
                    const policy = response.data.policy;
                    $('#policy_id').val(policy.id);
                    $('#policy_type').val(policy.policy_type);
                    tinymce.get('policy_text').setContent(policy.policy_text);
                    $('#shop_id').val(policy.shop_id);
                    $('#is_default').prop('checked', policy.is_default);
                    $('#editPolicyModalLabel').text('Edit Policy');
                    $('#savePolicyBtn').data('action', 'update');
                    $('#editPolicyModal').modal('show');
                }).catch(error => {
                    notyf.error('Error fetching policy data.');
                });
            });

            // Save Policy
            $('#savePolicyBtn').on('click', function () {
                const action = $(this).data('action');
                const policyId = $('#policy_id').val();
                const url = action === 'create' ? '/store_policies' : `/store_policies/${policyId}`;
                const method = action === 'create' ? 'post' : 'put';

                const policyData = {
                    policy_type: $('#policy_type').val(),
                    policy_text: tinymce.get('policy_text').getContent(),
                    shop_id: $('#shop_id').val(),
                    is_default: $('#is_default').is(':checked')
                };

                axios({ url, method, data: policyData })
                    .then(response => {
                        $('#editPolicyModal').modal('hide');
                        table.ajax.reload();
                        notyf.success(response.data.message);
                    })
                    .catch(error => {
                        notyf.error(error.response.data.message || 'An unexpected error occurred.');
                    });
            });

            // Handle Delete Button Click
            $(document).on('click', '.delete-policy', function (e) {
                e.preventDefault();
                const policyId = $(this).data('id');
                if (confirm('Are you sure you want to delete this policy?')) {
                    axios.delete(`/store_policies/${policyId}`)
                        .then(response => {
                            table.ajax.reload();
                            notyf.success(response.data.message);
                        })
                        .catch(error => {
                            notyf.error(error.response.data.message || 'An unexpected error occurred.');
                        });
                }
            });
        });
    </script>
</section>
@endsection
