<section class="content-body p-xl-4">
    <h2>Vendor Financial Overview</h2>
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-2">
            <aside class="pr-2 border-end">
                <nav class="nav nav-pills flex-lg-column mb-2" style="font-size: 12px">
                    <a class="nav-link-2 border p-2 mb-2 d-flex align-items-center" href="#" data-target="vendor-wallet">
                        <i class="material-icons md-account_balance_wallet" style="font-size: 14px; margin-right:3px"></i>
                        <span class="ml-2">Vendor Wallet</span>
                    </a>
                    <a class="nav-link-2 border p-2 mb-2 d-flex align-items-center" href="#" data-target="vendor-transactions">
                        <i class="material-icons md-store" style="font-size: 14px; margin-right:3px"></i>
                        <span class="ml-2">Vendor Transactions</span>
                    </a>
                </nav>
            </aside>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-10">
            <!-- Vendor Wallet Section -->
            <div id="vendor-wallet" class="content-section-2" style="display:block;">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Wallet Balance: <span id="balance" class="text-primary"></span></h4>
                        <p class="card-text">Manage your vendor wallet and view payouts and transactions.</p>
                    </div>
                </div>

                <!-- Payout Request Form -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title">Request Payout</h4>
                        <form id="payout-request-form">
                            <div class="form-group">
                                <label for="payout-amount">Amount to Withdraw</label>
                                <input type="number" class="form-control" id="payout-amount" min="1" step="0.01" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Request Payout</button>
                        </form>
                    </div>
                </div>

                <h3>Vendor Wallet Payouts</h3>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Vendor ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Paid At</th>
                        </tr>
                    </thead>
                    <tbody id="vendor-payouts-body">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Vendor Transactions Section -->
            <div id="vendor-transactions" class="content-section-2" style="display:none;">
                <h3>Vendor Transactions</h3>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Vendor ID</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Transaction Type</th>
                            <th>Date Time</th>
                        </tr>
                    </thead>
                    <tbody id="vendor-transactions-body">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <br />
    <button class="btn btn-primary" type="submit">Save changes</button>
</section>

@push('store_commision_withdrawal')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const user = @json($user);
        if (!user || !user.vendor_shop) return;
        console.log(user);


        let vendorPayouts = user.vendor_shop.vendor_payout || [];
        let vendorTransactions = user.vendor_shop.vendor_transaction || [];
        let vendorWallet = user.vendor_shop.wallet || [];

        let balance = document.getElementById('balance');
        balance.textContent = vendorWallet.balance ? `$${vendorWallet.balance}` : "0.00 USD";

        // Populate Vendor Payouts Table
        const payoutsBody = document.getElementById('vendor-payouts-body');
        vendorPayouts.forEach(payout => {
            let row = `<tr>
                <td>${payout.id}</td>
                <td>${payout.vendor_id}</td>
                <td>$${payout.amount}</td>
                <td>
                    <span class="badge bg-${payout.status === 'paid' ? 'success' : payout.status === 'approved' ? 'info' : 'warning'}">
                        ${payout.status.charAt(0).toUpperCase() + payout.status.slice(1)}
                    </span>
                </td>
                <td>${payout.requested_at}</td>
                <td>${payout.paid_at}</td>
            </tr>`;
            payoutsBody.innerHTML += row;
        });

        // Populate Vendor Transactions Table
        const transactionsBody = document.getElementById('vendor-transactions-body');
        vendorTransactions.forEach(transaction => {
            const dateObj = new Date(transaction.created_at);
            const formattedDate = dateObj.toLocaleString();
            let row = `<tr>
                <td>${transaction.id}</td>
                <td>${transaction.vendor_id}</td>
                <td>${transaction.order_id}</td>
                <td>$${transaction.amount}</td>
                <td><span class="badge bg-info">${transaction.transaction_type}</span></td>
                <td>${formattedDate}</td>
            </tr>`;
            transactionsBody.innerHTML += row;
        });

        // Handle Payout Request
        document.getElementById('payout-request-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const amount = parseFloat(document.getElementById('payout-amount').value);

            if (amount > vendorWallet.balance) {
                notyf.error('Insufficient balance for the payout request.');
                return;
            }

            // Here you would typically make an AJAX request to your server to handle the payout request.
            // For demonstration purposes, we'll just update the table and balance on the client side.

            const newPayout = {
                id: vendorPayouts.length + 1,
                vendor_id: user.vendor_shop.vendor_id,
                amount: amount.toFixed(2),
                status: 'requested',
                requested_at: new Date().toISOString().split('T')[0], // Just the date part
                paid_at: null
            };

            vendorPayouts.push(newPayout);
            vendorWallet.balance -= amount;
            balance.textContent = `$${vendorWallet.balance.toFixed(2)}`;

            // Make an AJAX request to the server to handle the payout request
        fetch('{{ route("vendor.request.payout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ amount: amount })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                vendorWallet.balance -= amount;
                balance.textContent = `$${vendorWallet.balance.toFixed(2)}`;

                // Update Payouts Table
                let newPayout = data.payout;
                let row = `<tr>
                    <td>${newPayout.id}</td>
                    <td>${newPayout.vendor_id}</td>
                    <td>$${newPayout.amount}</td>
                    <td><span class="badge bg-warning">requested</span></td>
                    <td>${newPayout.requested_at}</td>
                    <td></td>
                </tr>`;
                document.getElementById('vendor-payouts-body').innerHTML += row;
                document.getElementById('payout-amount').value = '';

                notyf.success('Payout request submitted successfully.');
            } else if (data.error) {
                notyf.error(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notyf.error('An error occurred while requesting the payout.');
        });
        });

        // Toggle between sections
        document.querySelectorAll('.nav-link-2').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelectorAll('.content-section-2').forEach(section => {
                    section.style.display = 'none';
                });
                const target = this.getAttribute('data-target');
                document.getElementById(target).style.display = 'block';
            });
        });
    });
</script>
@endpush
