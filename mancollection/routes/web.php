<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.products.details');
Route::get('/shop/{category_slug}', [ShopController::class, 'search'])->name('shop.search');

//Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.increase');
Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.decrease');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_from_cart'])->name('cart.remove');
Route::post('/cart/empty', [CartController::class, 'emptyCart'])->name('cart.empty');
//coupon
Route::post('/cart/coupon/apply', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/coupon/remove', [CartController::class, 'remove_coupon_code'])->name('cart.remove.coupon');
//Checkout
Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/cart/checkout/place-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
Route::get('/cart/checkout/confirm-order', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.item.move');
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::delete('/wishlist/{rowId}', [WishlistController::class, 'remove'])->name('wishlist.remove');
Route::post('/wishlist/clear', [WishlistController::class, 'clear_wishlist'])->name('wishlist.clear');

Route::get('/contact', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact/store', [HomeController::class, 'contact_store'])->name('home.contact.store');
Route::get('/home/search', [HomeController::class, 'search'])->name('home.search');

// Routes for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/user-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/user/orders/{order_id}', [UserController::class, 'order_details'])->name('user.orders.details');
    Route::put('/user/orders/cancel', [UserController::class, 'order_cancel'])->name('user.orders.cancel');
});
// Routes for authenticated users
// Route::middleware(['auth'])->group(function () {
//     // User routes
//     Route::get('/user-dashboard', [Auth::class, 'index'])->name('user.index');
// });

// Routes for authenticated users
Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    //Brand route
    Route::get('/admin-brand', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'addBrands'])->name('admin.brands.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brands_store'])->name('admin.brands.store');
    Route::get('/admin/brands/edit/{id}', [AdminController::class, 'brands_edit'])->name('admin.brands.edit');
    Route::put('/admin/brands/update/{id}', [AdminController::class, 'brands_update'])->name('admin.brands.update');
    Route::delete('/admin/brands/delete/{id}', [AdminController::class, 'brands_destroy'])->name('admin.brands.delete');

    // Categories routes
    Route::get('/admin/categories', [AdminController::class, 'category'])->name('admin.categories');
    Route::get('/admin/categories/create', [AdminController::class, 'category_add'])->name('admin.categories.add');
    Route::post('/admin/categories/store', [AdminController::class, 'category_store'])->name('admin.categories.store');
    Route::get('/admin/categories/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/update/{id}', [AdminController::class, 'category_update'])->name('admin.categories.update');
    Route::delete('/admin/categories/delete/{id}', [AdminController::class, 'category_destroy'])->name('admin.categories.delete');


    // Products routes
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/products/add', [AdminController::class, 'products_add'])->name('admin.products.add');
    Route::post('admin/products/store', [AdminController::class, 'products_store'])->name('admin.products.store');
    Route::get('/admin/products/show', [AdminController::class, 'show'])->name('admin.products.show');
    Route::get('/admin/products/{id}/edit', [AdminController::class, 'products_edit'])->name('admin.products.edit');
    Route::put('/admin/products/{id}/update', [AdminController::class, 'products_update'])->name('admin.products.update');
    Route::delete('/admin/products/{id}', [AdminController::class, 'products_destroy'])->name('admin.products.destroy');

    // Route to display the list of coupons
    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('coupon.index');
    Route::get('/admin/coupons/create', [AdminController::class, 'coupon_add'])->name('coupon.add');
    Route::post('/admin/coupons', [AdminController::class, 'coupons_store'])->name('coupon.store');
    Route::get('/admin/coupons/{id}/edit', [AdminController::class, 'coupon_edit'])->name('coupon.edit');
    Route::put('/admin/coupons/{id}', [AdminController::class, 'coupons_update'])->name('coupon.update');
    Route::delete('/admin/coupons/{id}', [AdminController::class, 'coupons_destroy'])->name('coupon.destroy');
    //order
    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/orders/{order_id}', [AdminController::class, 'order_details'])->name('admin.order.details');
    Route::put('/admin/orders/update/status', [AdminController::class, 'updateOrderStatus'])->name('admin.order.status.update');
    //slide
    Route::get('/admin/slide', [AdminController::class, 'slide'])->name('admin.slide');
    Route::get('/admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
    Route::get('/admin/slide/edit/{id}', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
    Route::put('/admin/slide/update/{id}', [AdminController::class, 'slide_update'])->name('admin.slide.update');
    Route::delete('/admin/slide/{id}', [AdminController::class, 'slide_destoy'])->name('admin.slide.delete');

    //contact
    Route::get('/admin/contact', [AdminController::class, 'contact'])->name('admin.contact');
    Route::delete('/admin/contact/delete/{id}', [AdminController::class, 'contact_destroy'])->name('admin.contact.delete');
    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');
});
