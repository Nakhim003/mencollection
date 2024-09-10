@extends('layouts.app')

@section('content')
    <style>
        .wishlist-item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .move-to-cart-btn {
            border-radius: 5px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .move-to-cart-btn:hover {
            transform: scale(1.05);
            background-color: #28a745;
            color: #fff;
        }

        .btn-clear-wishlist {
            background-color: #dadada;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-clear-wishlist:hover {
            background-color: #c0c0c0;
        }

        .page-title {
            font-size: 2rem;
            font-weight: bold;
        }

        .cart-table__wrapper {
            margin-top: 20px;
        }
    </style>

    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="shop-checkout container">
            <h2 class="page-title text-center">Wishlist</h2>

            <div class="shopping-cart">
                <div class="cart-table__wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th></th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>
                                        <div class="shopping-cart__product-item">
                                            <img loading="lazy"
                                                src="{{ asset('uploads/products/thumbnails/' . $item->model->image) }}"
                                                class="wishlist-item-image" alt="{{ $item->name }}" />
                                        </div>
                                    </td>
                                    <td>
                                        <div class="shopping-cart__product-item__detail">
                                            <h4>{{ $item->name }}</h4>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="shopping-cart__product-price">${{ $item->price }}</span>
                                    </td>
                                    <td>
                                        <span class="shopping-cart__product-quantity">{{ $item->qty }}</span>
                                    </td>
                                    <td>
                                        <div class="row">
                                            <div class="col-6">
                                                <form method="POST"
                                                    action="{{ route('wishlist.item.move', ['rowId' => $item->rowId]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning move-to-cart-btn">
                                                        Move to Cart
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="col-6">
                                                <form method="POST"
                                                    action="{{ route('wishlist.remove', ['rowId' => $item->rowId]) }}"
                                                    id="remove-item-{{ $item->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="javascript:void(0)" class="remove-cart"
                                                        onclick="document.getElementById('remove-item-{{ $item->id }}').submit();">
                                                        <svg width="10" height="18" viewBox="0 0 18 18"
                                                            fill="#767676" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M0.259435 8.85506L9.11449 0L10 0.885506L1.14494 9.74056L0.259435 8.85506Z" />
                                                            <path
                                                                d="M0.885506 0.0889838L9.74057 8.94404L8.85506 9.82955L0 0.97449L0.885506 0.0889838Z" />
                                                        </svg>
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Your wishlist is empty.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Center the Clear Wishlist button and update its style -->
                    <div class="text-center mt-4">
                        <form method="POST" action="{{ route('wishlist.clear') }}">
                            @csrf
                            <button type="submit" class="btn btn-clear-wishlist">CLEAR WISHLIST</button>
                        </form>
                    </div>

                </div>
            </div>
        </section>
    </main>
@endsection
