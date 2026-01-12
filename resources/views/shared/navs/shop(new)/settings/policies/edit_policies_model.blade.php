<div class="modal fade" id="editPolicyModal" tabindex="-1" aria-labelledby="editPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg p-5">
        <div class="modal-content">
            <form id="editPolicyForm">
                @csrf
                <input type="hidden" id="policy_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPolicyModalLabel">Add/Edit Store Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="policy_type" class="form-label">Policy Type</label>
                        <input type="text" class="form-control" id="policy_type" name="policy_type">
                    </div>
                    <div class="mb-3">
                        <label for="policy_text" class="form-label">Policy Text</label>
                        <textarea id="policy_text" class="form-control" name="policy_text"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="shop_id" class="form-label">Shop</label>
                        <select id="shop_id" class="form-select" name="shop_id">
                            <option value="">Select Shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}">{{ $shop->shopname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">Default Policy</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePolicyBtn">Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
