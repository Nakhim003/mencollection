@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/sweetalert.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.min.css') }}">
@section('content')
    <style>
        /* Table Header and Body Styles */
        table th,
        table td {
            background-color: transparent !important;
            color: black !important;
        }

        /* Add Background Color to Specific Table Headers */
        #top-table th {
            background-color: #9ab973;
            /* Set your desired background color */
            color: white;
            /* Set the text color */
        }

        /* Table Border Color */
        .table-bordered>:not(caption)>tr>th,
        .table-bordered>:not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        /* Product Image Size and Alignment */
        .table .pname .image img {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }

        .pname .image {
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            /* Space between image and name */
        }

        .pname .name {
            display: inline-block;
            vertical-align: middle;
        }

        /* Badge Styles */
        .badge.bg-success {
            background-color: #48c718 !important;
        }

        .badge.bg-danger {
            background-color: #ff4032 !important;
        }

        .badge.bg-warning {
            background-color: #ff5d00 !important;
        }

        .badge.bg-secondary {
            background-color: #6c757d !important;
        }

        /* Custom Badge Styles */
        .badge.bg-ordered {
            background-color: #ffc107 !important;
            /* Example: Yellow background */
            color: black;
        }

        .badge.bg-canceled {
            background-color: #dc3545 !important;
            /* Example: Red background */
            color: white;
        }

        .badge.bg-delivered {
            background-color: #28a745 !important;
            /* Example: Green background */
            color: white;
        }

        .table-color {
            background-color: #9ab973 !important;
        }

        /* Back Button Styles */
        .tf-button.style-1.w208 {
            background-color: #6a6e51;
            /* Set your desired background color */
            color: white;
            padding: 10px 20px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .tf-button.style-1.w208:hover {
            background-color: #9ab973;
            /* Background color on hover */
            color: white;
            text-decoration: none;
        }
    </style>
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Order's Details</h2>
            <div class="row">
                <div class="wg-box">
                    <div class="flex items-center justify-between gap10 flex-wrap">
                        <div class="wg-filter flex-grow">
                            <h5>Ordered Items</h5>
                        </div>
                        <a class="tf-button style-1 w208" href="{{ route('admin.orders') }}">Back</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="top-table">
                            <tr>
                                <th>Order No</th>
                                <td>{{ $order->id }}</td>
                                <th>Mobile</th>
                                <td>{{ $order->phone }}</td>
                                <th>Zip Code</th>
                                <td>{{ $order->zip }}</td>
                            </tr>
                            <tr>
                                <th>Order Date</th>
                                <td>{{ $order->created_at }}</td>
                                <th>Delivered Date</th>
                                <td>{{ $order->delivered_date ? $order->delivered_date : 'N/A' }}</td>
                                <th>Canceled Date</th>
                                <td>{{ $order->canceled_date ? $order->canceled_date : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Order Status</th>
                                <td colspan="5">
                                    @if ($order->status == 'delivered')
                                        <span class="badge bg-delivered">Delivered</span>
                                    @elseif($order->status == 'canceled')
                                        <span class="badge bg-canceled">Canceled</span>
                                    @else
                                        <span class="badge bg-ordered">Ordered</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="divider"></div>
                </div>
                <div class="wg-box">
                    <div class="flex items-center justify-between gap10 flex-wrap">
                        <div class="wg-filter flex-grow">
                            <h5>Ordered Items</h5>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-color">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Brand</th>
                                    <th class="text-center">Options</th>
                                    <th class="text-center">Return Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderItems as $item)
                                    <tr>
                                        <td class="pname">
                                            <div class="image">
                                                <img src="{{ asset('uploads/products/thumbnails/' . $item->product->image) }}"
                                                    alt="{{ $item->product->name }}">
                                            </div>
                                            <div class="name">
                                                <a href="{{ route('shop.products.details', ['product_slug' => $item->product->slug]) }}"
                                                    target="_blank" class="body-title-2">{{ $item->product->name }}</a>
                                            </div>
                                        </td>
                                        <td class="text-center">${{ number_format($item->price, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center">{{ $item->product->SKU }}</td>
                                        <td class="text-center">{{ $item->product->category->name }}</td>
                                        <td class="text-center">{{ $item->product->brand->name }}</td>
                                        <td class="text-center">{{ $item->options ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $item->return_status ? 'Yes' : 'No' }}</td>
                                        <td class="text-center">
                                            <div class="list-icon-function view-icon">
                                                <div class="item eye">
                                                    <i class="icon-eye"></i>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                        {{ $orderItems->links('pagination::bootstrap-5') }}
                    </div>
                </div>

                <div class="wg-box mt-5">
                    <h5>Shipping Address</h5>
                    <div class="my-account__address-item col-md-6">
                        <div class="my-account__address-item__detail">
                            <p>{{ $order->name }}</p>
                            <p>{{ $order->address }}</p>
                            <p>{{ $order->locality }}</p>
                            <p>{{ $order->city }}, {{ $order->country }}</p>
                            <p>{{ $order->landmark }}</p>
                            <p>{{ $order->zip }}</p>
                            <br>
                            <p>Mobile: {{ $order->phone }}</p>
                        </div>
                    </div>
                </div>
                <div class="wg-box mt-5">
                    <h5>Transactions</h5>
                    <table class="table table-striped table-bordered table-transaction">
                        <tbody>
                            <tr>
                                <th>Subtotal</th>
                                <td>${{ $order->subtotal }}</td>
                                <th>Tax</th>
                                <td>${{ $order->tax }}</td>
                                <th>Discount</th>
                                <td>${{ $order->discount }}</td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td>${{ $order->total }}</td>
                                <th>Payment Mode</th>
                                <td>{{ $transaction->mode }}</td>
                                <th>Status</th>
                                <td>
                                    @if ($transaction->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($transaction->status == 'declined')
                                        <span class="badge bg-danger">Declined</span>
                                    @elseif($transaction->status == 'refunded')
                                        <span class="badge bg-secondary">Refunded</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="wg-box mt-5 text-right">
                    <form action="{{ route('user.orders.cancel') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="order_id" value="{{ $order->id }}" />
                        <button type="submit" class="btn btn-danger cancel-order">Cancel Order</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection
@push('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script>
        $(function() {
            $('.cancel-order').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'You want to cancel this order?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, cancel it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
