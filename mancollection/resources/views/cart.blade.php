@extends('layouts.app')

@section('content')
    <style>
        .qty-control__reduce,
        .qty-control__increase {
            background: none;
            border: none;
            padding: 0;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            color: #333;
            display: inline-flex;
            /* Use inline-flex for consistent sizing */
            align-items: center;
            /* Center the text vertically */
            justify-content: center;
            /* Center the text horizontally */
            width: 30px;
            /* Fixed width */
            height: 30px;
            /* Fixed height */
            text-align: center;
            transition: color 0.2s ease, transform 0.2s ease;
            /* Smooth color and scale transition */
        }

        .qty-control__reduce:hover,
        .qty-control__increase:hover {
            color: #000;
            transform: scale(1.1);
            /* Slightly enlarge on hover for feedback */
        }

        .qty-control__number {
            width: 50px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
            /* Smooth border-color transition */
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 5px;
            /* Add space between buttons and input */
        }

        .qty-control__form {
            display: inline-block;
        }

        .remove-cart {
            background: none;
            border: none;
            cursor: pointer;
            color: #767676;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            width: 30px;
            height: 30px;
            text-align: center;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .remove-cart:hover {
            color: #000;
            transform: scale(1.1);
        }

        .text-success {
            color: green !important;
        }

        .text-danger {
            color: red !important;
        }
    </style>
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="shop-checkout container">
            <h2 class="page-title">Cart</h2>
            <div class="checkout-steps">
                <a href="javascript:void(0)" class="checkout-steps__item active">
                    <span class="checkout-steps__item-number">01</span>
                    <span class="checkout-steps__item-title">
                        <span>Shopping Bag</span>
                        <em>Manage Your Items List</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">02</span>
                    <span class="checkout-steps__item-title">
                        <span>Shipping and Checkout</span>
                        <em>Checkout Your Items List</em>
                    </span>
                </a>
                <a href="javascript:void(0)" class="checkout-steps__item">
                    <span class="checkout-steps__item-number">03</span>
                    <span class="checkout-steps__item-title">
                        <span>Confirmation</span>
                        <em>Review And Submit Your Order</em>
                    </span>
                </a>
            </div>
            <div class="shopping-cart">
                @if ($items->count() > 0)
                    <div class="cart-table__wrapper">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th></th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="shopping-cart__product-item">
                                                <img loading="lazy"
                                                    src="{{ asset('uploads/products/' . $item->model->image) }}"
                                                    width="120" height="120" alt="{{ $item->name }}" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="shopping-cart__product-item__detail">
                                                <h4>{{ $item->name }}</h4>
                                                <ul class="shopping-cart__product-item__options">
                                                    {{-- <li>Color: {{ $item->options->color }}</li> --}}
                                                    {{-- <li>Size: {{ $item->options->size }}</li> --}}
                                                </ul>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="shopping-cart__product-price">${{ $item->price }}</span>
                                        </td>
                                        <td>
                                            <div class="qty-control position-relative">

                                                <form class="qty-control__form"
                                                    action="{{ route('cart.decrease', $item->rowId) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="qty-control__reduce">-</button>
                                                </form>
                                                <!-- Quantity Input -->
                                                <input type="number" name="quantity" value="{{ $item->qty }}"
                                                    min="1" class="qty-control__number text-center" readonly>
                                                <!-- Increase Quantity Form -->
                                                <form class="qty-control__form"
                                                    action="{{ route('cart.increase', $item->rowId) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="qty-control__increase">+</button>
                                                </form>
                                            </div>

                                        </td>
                                        <td>
                                            <span class="shopping-cart__subtotal">${{ $item->subtotal }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('cart.remove', $item->rowId) }}" method="POST"
                                                class="delete" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="remove-cart" title="Remove Item">
                                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="#767676"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                                                        <path
                                                            d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="cart-table-footer">
                            @if (!Session::has('coupon'))
                                <form action="{{ route('cart.coupon.apply') }}" method="POST"
                                    class="position-relative bg-body">
                                    @csrf
                                    <input class="form-control" type="text" name="coupon_code" placeholder="Coupon Code"
                                        value="">
                                    <button class="btn-link fw-medium position-absolute top-0 end-0 h-100 px-4"
                                        type="submit" value="APPLY COUPON">APPLY COUPON</button>
                                </form>
                            @else
                                <form action="{{ route('cart.remove.coupon') }}" method="POST"
                                    class="position-relative bg-body">
                                    @csrf
                                    @method('DELETE')
                                    <input class="form-control" type="text" name="coupon_code" placeholder="Coupon Code"
                                        value="@if (Session::has('coupon')) {{ Session::get('coupon')['code'] }} - Applied! @endif">
                                    <button class="btn-link fw-medium position-absolute top-0 end-0 h-100 px-4"
                                        type="submit" value="APPLY COUPON">REMOVE COUPON</button>
                                </form>
                            @endif

                            <form action="{{ route('cart.empty') }}" method="POST" class="empty-cart-form">
                                @csrf
                                <button type="submit" class="btn btn-danger">CLEAR CART</button>
                            </form>
                        </div>
                        <div>
                            @if (Session::has('success'))
                                <p class="text-success">{{ Session::get('success') }}</p>
                            @elseif (Session::has('error'))
                                <p class="text-danger">{{ Session::get('error') }}</p>
                            @endif

                        </div>
                    </div>
                    <div class="shopping-cart__totals-wrapper">
                        <div class="sticky-content">
                            <div class="shopping-cart__totals">
                                <h3>Cart Totals</h3>
                                @if (Session::has('discounts'))
                                    <table class="cart-totals">
                                        <tbody>
                                            <tr>
                                                <th>Subtotal</th>
                                                <td>${{ Cart::instance('cart')->subtotal() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Discount
                                                    @if (Session::has('coupon'))
                                                        ({{ Session::get('coupon')['code'] }})
                                                    @endif
                                                </th>
                                                <td>
                                                    ${{ Session::get('discounts')['discount'] ?? '0.00' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Subtotal After Discount</th>
                                                <td>${{ Session::get('discounts')['subtotal'] ?? Cart::instance('cart')->subtotal() }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Shipping</th>
                                                <td>Free</td>
                                            </tr>
                                            <tr>
                                                <th>VAT</th>
                                                <td>${{ Session::get('discounts')['tax'] ?? Cart::instance('cart')->tax() }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total</th>
                                                <td>${{ Session::get('discounts')['total'] ?? Cart::instance('cart')->total() }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @else
                                    <table class="cart-totals">
                                        <tbody>
                                            <tr>
                                                <th>Subtotal</th>
                                                <td>${{ Cart::instance('cart')->subtotal() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Shipping</th>
                                                <td>Free</td>
                                            </tr>
                                            <tr>
                                                <th>VAT</th>
                                                <td>${{ Cart::instance('cart')->tax() }}</td>
                                            </tr>
                                            <tr>
                                                <th>Total</th>
                                                <td>${{ Cart::instance('cart')->total() }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                            <div class="mobile_fixed-btn_wrapper">
                                <div class="button-wrapper container">
                                    <a href="{{ route('cart.checkout') }}" class="btn btn-primary btn-checkout">PROCEED TO
                                        CHECKOUT</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-12 text-center pt-5 bp-5">
                            <p>No items found in your cart</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-info">Shop Now</a>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection
@push('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('.qty-control__form');

            forms.forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const actionUrl = form.getAttribute('action');
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(actionUrl, {
                            method: 'PuT',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: formData
                        });

                        if (response.ok) {
                            const qtyInput = form.closest('.qty-control').querySelector(
                                '.qty-control__number');
                            const currentQty = parseInt(qtyInput.value, 10);

                            if (form.querySelector('.qty-control__increase')) {
                                qtyInput.value = currentQty + 1;
                            } else if (form.querySelector('.qty-control__reduce') &&
                                currentQty > 1) {
                                qtyInput.value = currentQty - 1;
                            }

                            // Optionally, animate the input value change
                            qtyInput.classList.add('animate-change');
                            setTimeout(() => qtyInput.classList.remove('animate-change'), 200);
                        } else {
                            throw new Error('Failed to update the quantity.');
                        }
                    } catch (error) {
                        alert(error.message || 'Something went wrong. Please try again.');
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $('.delete').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this item?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    <script>
        $(function() {
            $('.empty-cart-form').on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to empty your cart?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, empty it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
