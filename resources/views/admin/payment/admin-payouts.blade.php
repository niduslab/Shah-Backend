@extends('layouts.app')
@section('title', 'NidusCart - Manage Payouts')
@section('content')

<section class="content-main">
    <section class="content-body p-xl-4">
        <h2>Vendor Payouts Management</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Admin Wallet Balance -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Admin Wallet Balance: <span class="text-primary">${{ $adminWallet->balance }}</span></h4>
            </div>
        </div>

        <!-- Payouts Table -->
        <div class="card p-3">
            <table class="table table-hover yajra-datatable">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Vendor ID</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>
</section>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script type="text/javascript">
$(function () {
    var table = $('.yajra-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.payouts.manage') }}",
        columns: [
            {data: 'id', name: 'id'},
            {data: 'vendor_id', name: 'vendor_id'},
            {data: 'amount', name: 'amount'},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'requested_at', name: 'requested_at'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false},
        ]
    });
});
</script>

@endsection
