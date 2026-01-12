@php
    $badgeClass = '';
    switch ($orderDetails->status) {
        case 'pending':
            $badgeClass = 'warning';
            break;
        case 'processing':
            $badgeClass = 'info';
            break;
        case 'shipped':
            $badgeClass = 'primary';
            break;
        case 'delivered':
            $badgeClass = 'success';
            break;
        case 'cancelled':
            $badgeClass = 'danger';
            break;
        default:
            $badgeClass = 'secondary';
            break;
    }
@endphp
<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Order detail</h2>
        </div>
    </div>
    <div class="card">
        <header class="card-header">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 mb-lg-0 mb-15">
                    <span> <i class="material-icons md-calendar_today"></i> <b>{{ date('D, M d, Y, h:i A', strtotime($orderDetails->created_at)) }}</b> </span> <br>
                    <small>Order Status: <span class="badge rounded-pill alert-{{$badgeClass}} text-{{$badgeClass}}"> {{ucfirst($orderDetails->status)}}</span></small> <br>
                    <small>Tracking Number: <span class="text-primary">{{ $orderDetails->tracking_number }}</span></small> <br>

                </div>
                <div class="col-lg-6 col-md-6 ms-auto text-md-end">
                    <form action="{{ route('admin.orders.updateStatus', $orderDetails->id) }}" method="POST">
                        <span class="fs-6 text-center">Update Status </span>
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select d-inline-block mb-lg-0 mr-5 mw-200">
                            <option>Change status</option>
                            <option value="awaiting_payment" {{ $orderDetails->status == 'awaiting_payment' ? 'selected' : '' }}>Awaiting payment</option>
                            <option value="pending" {{ $orderDetails->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $orderDetails->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="confirmed" {{ $orderDetails->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="shipped" {{ $orderDetails->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $orderDetails->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $orderDetails->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="return_requested" {{ $orderDetails->status == 'return_requested' ? 'selected' : '' }}>Return Requested</option>
                            <option value="return_approved" {{ $orderDetails->status == 'return_approved' ? 'selected' : '' }}>Return Approved</option>
                            <option value="return_shipped" {{ $orderDetails->status == 'return_shipped' ? 'selected' : '' }}>Return Shipped</option>
                            <option value="refund_requested" {{ $orderDetails->status == 'refund_requested' ? 'selected' : '' }}>Refund Requested</option>
                            <option value="refund_approved" {{ $orderDetails->status == 'refund_approved' ? 'selected' : '' }}>Refund Approved</option>
                            <option value="refunded" {{ $orderDetails->status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="replacement_requested" {{ $orderDetails->status == 'replacement_requested' ? 'selected' : '' }}>Replacement Requested</option>
                            <option value="replacement_approved" {{ $orderDetails->status == 'replacement_approved' ? 'selected' : '' }}>Replacement Approved</option>
                            <option value="replacement_shipped" {{ $orderDetails->status == 'replacement_shipped' ? 'selected' : '' }}>Replacement Shipped</option>
                        </select>

                        <button type="submit" class="btn btn-primary">Save</button>
                        <a class="btn btn-secondary print ms-2" href="#"><i class="icon material-icons md-print"></i></a>
                    </form>
                </div>
            </div>
        </header>

        <div class="card-body">
            <div class="row mb-50 mt-20 order-info-wrap">
                <div class="col-md-4">
                    <article class="icontext align-items-start">
                        <span class="icon icon-sm rounded-circle bg-primary-light">
                            <i class="text-primary material-icons md-person"></i>
                        </span>
                        <div class="text">
                            <h6 class="mb-1">Customer</h6>
                            <p class="mb-1">
                                {{ $orderDetails->user->fname }} {{ $orderDetails->user->lname }} <br>
                                {{ $orderDetails->user->email }} <br>
                                {{ $orderDetails->shippingAddress->contact_no ?? '+N/A' }}
                            </p>
                            <a href="#">View profile</a>
                        </div>
                    </article>
                </div>

                <div class="col-md-4">
                    <article class="icontext align-items-start">
                        <span class="icon icon-sm rounded-circle bg-primary-light">
                            <i class="text-primary material-icons md-local_shipping"></i>
                        </span>
                        <div class="text">
                            <h6 class="mb-1">Shipping Address</h6>
                            <p class="mb-1">
                                Shipping: {{ $orderDetails->shippingAddress->address_line_1 }} <br>
                                {{ $orderDetails->shippingAddress->address_line_2 }} <br>
                                State: {{ $orderDetails->shippingAddress->state }} <br>
                                City: {{ $orderDetails->shippingAddress->city }} <br>
                                Zip-code: {{ $orderDetails->shippingAddress->zip_code }}
                                {{-- Contact: {{ ucfirst($orderDetails->status) }} --}}
                            </p>
                            <a href="#">Download info</a>
                        </div>
                    </article>
                </div>

                <div class="col-md-4">
                    <article class="icontext align-items-start">
                        <span class="icon icon-sm rounded-circle bg-primary-light">
                            <i class="text-primary material-icons md-place"></i>
                        </span>
                        <div class="text">
                            <h6 class="mb-1">Deliver to</h6>
                            <p class="mb-1">
                                City: {{ $orderDetails->shippingAddress->city ?? 'N/A' }}, {{ $orderDetails->shippingAddress->country ?? 'N/A' }} <br>
                                {{ $orderDetails->shippingAddress->address_line_1 ?? 'N/A' }} <br>
                                {{ $orderDetails->shippingAddress->zip_code ?? 'N/A' }}
                            </p>
                            <a href="#">View profile</a>
                        </div>
                    </article>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="40%">Product</th>
                                    <th width="20%">Unit Price</th>
                                    <th width="20%">Quantity</th>
                                    <th width="20%" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderDetails->items as $item)
                                    <tr>
                                        <td>
                                            <a class="itemside" href="#">
                                                <div class="left">
                                                    @if ($item->product->image_url)
                                                    <img src="{{ $item->product->image_url }}" width="40" height="40" class="img-xs" alt="Item">
                                                    @endif

                                                </div>
                                                <div class="info">{{ $item->product->product_name }}</div>
                                            </a>
                                        </td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4">
                                        <article class="float-end">
                                            <dl class="dlist">
                                                <dt>Subtotal:</dt>
                                                <dd>${{ number_format($orderDetails->subtotal, 2) }}</dd>
                                            </dl>
                                            <dl class="dlist">
                                                <dt>Shipping cost:</dt>
                                                <dd>${{ number_format($orderDetails->shipping_cost, 2) }}</dd>
                                            </dl>
                                            <dl class="dlist">
                                                <dt>Grand total:</dt>
                                                <dd><b class="h5">${{ number_format($orderDetails->total_amount, 2) }}</b></dd>
                                            </dl>
                                            <dl class="dlist">
                                                <dt class="text-muted">Status:</dt>
                                                <dd>
                                                    <span class="badge rounded-pill alert-success text-success">Payment done</span>
                                                </dd>
                                            </dl>
                                        </article>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-1"></div>
                <div class="col-lg-4">
                    <div class="box shadow-sm bg-light">
                        <h6 class="mb-15">Payment info</h6>
                        <p>
                            <img src="{{ asset('assets/imgs/card-brands/2.png') }}" class="border" height="20"> {{ $orderDetails->payment_method }} **** **** {{ substr($orderDetails->payment_last4, -4) }} <br>
                            Business name: Grand Market LLC <br>
                            Phone: +1 (800) 555-154-52
                        </p>
                    </div>
                    <div class="h-25 pt-4">
                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" id="notes" placeholder="Type some note">{{ $orderDetails->notes }}</textarea>
                        </div>
                        <button class="btn btn-primary">Save note</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



