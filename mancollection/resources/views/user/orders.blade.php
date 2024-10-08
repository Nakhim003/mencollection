@extends('layouts.app')
@section('content')
    <style>
        .table> :not(caption)>tr>th {
            padding: 0.625rem 1.5rem .625rem !important;
            background-color: #6a6e51 !important;
        }

        .table>tr>td {
            padding: 0.625rem 1.5rem .625rem !important;
        }

        .table-bordered> :not(caption)>tr>th,
        .table-bordered> :not(caption)>tr>td {
            border-width: 1px 1px;
            border-color: #6a6e51;
        }

        .table> :not(caption)>tr>td {
            padding: .8rem 1rem !important;
        }

        /* Custom styles for order statuses */
        .status-ordered {
            background-color: orange;
            color: black;
        }

        .status-canceled {
            background-color: red;
            color: white;
        }

        .status-delivered {
            background-color: green;
            color: white;
        }

        .bg-ordered {
            background-color: rgb(214, 175, 0);
            color: rgb(255, 255, 255);
            font-size: 12px;
        }

        .bg-canceled {
            background-color: red;
            color: white;

        }

        .bg-delivered {
            background-color: green;
            color: white;
        }
    </style>
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Orders</h2>
            <div class="row">
                <div class="col-lg-2">
                    @include('user.account-nav')
                </div>
                <div class="col-lg-10">
                    <div class="wg-table table-all-user">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 80px">OrderNo</th>
                                        <th>Name</th>
                                        <th class="text-center">Phone</th>
                                        <th class="text-center">Subtotal</th>
                                        <th class="text-center">Tax</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Order Date</th>
                                        <th class="text-center">Items</th>
                                        <th class="text-center">Delivered On</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td class="text-center">{{ $order->id }}</td>
                                            <td class="text-center">{{ $order->name }}</td>
                                            <td class="text-center">{{ $order->phone }}</td>
                                            <td class="text-center">${{ number_format($order->subtotal, 2) }}</td>
                                            <td class="text-center">${{ number_format($order->tax, 2) }}</td>
                                            <td class="text-center">${{ number_format($order->total, 2) }}</td>
                                            <td class="text-center ">
                                                @if ($order->status == 'delivered')
                                                    <span class="badge bg-delivered">Delivered</span>
                                                @elseif($order->status == 'canceled')
                                                    <span class="badge bg-canceled">Canceled</span>
                                                @else
                                                    <span class="badge bg-ordered">Ordered</span>
                                                @endif

                                            </td>
                                            <td class="text-center">{{ $order->created_at }}</td>
                                            <td class="text-center">{{ $order->orderItems->count() }}</td>
                                            <td class="text-center">{{ $order->delivered_date }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('user.orders.details', $order->id) }}">
                                                    <div class="list-icon-function view-icon">
                                                        <div class="item eye">
                                                            <i class="fa fa-eye"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <!-- Pagination links -->
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
