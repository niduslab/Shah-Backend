@extends('layouts.app')
@section('title', 'Weight Cost Rule Items | Niduscart')
@section('content')

<div class="container p-5">
    <div class="card mb-4">
        <header class="card-header">
            <h2>Manage Weight Cost Rule Items</h2>
            <button class="btn btn-primary mb-3" onclick="openCreateModal()">Add Weight Cost Rule Item</button>
        </header>

        <div class="card-body">
            <table id="weightCostRuleItemsTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Weight Cost Rule</th>
                        <th>Weight (kg)</th>
                        <th>Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="weightCostRuleItemModal" tabindex="-1" aria-labelledby="weightCostRuleItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="weightCostRuleItemForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="weightCostRuleItemModalLabel">Add Weight Cost Rule Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="weightCostRuleItemId">
                    <div class="mb-3">
                        <label for="weight_cost_rule_id" class="form-label">Weight Cost Rule</label>
                        <select class="form-select" id="weight_cost_rule_id" name="weight_cost_rule_id" required>
                            <option value="">Select Rule</option>
                        </select>
                    </div>

                    <div class="d-flex gap-3 w-100">
                        <div class="mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" class="form-control" id="weight" name="weight" required>
                        </div>
                        <div class="mb-3">
                            <label for="cost" class="form-label">Cost</label>
                            <input type="number" class="form-control" id="cost" name="cost" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
<script type="text/javascript">

    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('weight_cost_rule_id');
        let CountryStateCity = @json($CountryStateCity);
        CountryStateCity.forEach(item => {
            let option = document.createElement('option');
            option.value = item.id;

            if (item.shipping_rate) {
                option.textContent = `${item.shipping_rate.class ?? 'No Class'} -> ${item.shipping_rate.country ?? 'No Country'} -> ${item.state ?? 'No State'}`;
            } else {
                option.textContent = `No Shipping Rate -> ${item.state ?? 'No State'}`;
            }

            selectElement.appendChild(option);
        });
    });

    $(document).ready(function() {
        // Initialize Datatable
        $('#weightCostRuleItemsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('weight-cost-rule-items.index') }}',
            columns: [
                { data: 'id' },
                { data: 'CountryStateCity' }, // Assuming you have a 'name' field in the weight cost rule
                { data: 'weight' },
                { data: 'cost' },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });
    });

    // Open modal for creating or editing weight cost rule item
    function openCreateModal() {
        $('#weightCostRuleItemForm').trigger("reset");
        $('#weightCostRuleItemId').val('');
        $('#weightCostRuleItemModalLabel').text("Add Weight Cost Rule Item");
        $('#weightCostRuleItemModal').modal('show');
    }

    function openEditModal(id) {
        $.get(`/weight-cost-rule-items/${id}`, function(data) {
            $('#weightCostRuleItemModalLabel').text("Edit Weight Cost Rule Item");
            $('#weightCostRuleItemId').val(data.id);
            $('#weight_cost_rule_id').val(data.weight_cost_rule_id);
            $('#weight').val(data.weight);
            $('#cost').val(data.cost);
            $('#weightCostRuleItemModal').modal('show');
        }).fail(function(xhr) {
            console.error('Error fetching weight cost rule item data:', xhr.responseText);
            alert('Failed to fetch weight cost rule item data.');
        });
    }

    // Submit form to add or update weight cost rule item
    $('#weightCostRuleItemForm').submit(function(e) {
        e.preventDefault();
        let id = $('#weightCostRuleItemId').val();
        let url = id ? `/weight-cost-rule-items/${id}` : "{{ route('weight-cost-rule-items.store') }}";
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: $('#weightCostRuleItemForm').serialize(),
            success: function(response) {
                $('#weightCostRuleItemModal').modal('hide');
                $('#weightCostRuleItemsTable').DataTable().ajax.reload();
                notyf.success(response.message);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                notyf.error(response.error || 'Something went wrong.');
            }
        });
    });

    // Delete weight cost rule item
    function deleteWeightCostRuleItem(id) {
        if (confirm("Are you sure you want to delete this weight cost rule item?")) {
            $.ajax({
                url: `/weight-cost-rule-items/${id}`,
                type: 'DELETE',
                success: function(response) {
                    $('#weightCostRuleItemsTable').DataTable().ajax.reload();
                    notyf.success(response.message);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    notyf.error(response.error || 'Something went wrong.');
                }
            });
        }
    }
</script>

@endsection
