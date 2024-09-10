<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpParser\Builder\Function_;
use App\Models\OrderItem;
use App\Models\Transaction;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    {
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));
    }

    public function add_to_cart(Request $request)
    {
        Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }
    public function increase_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        Cart::instance('cart')->update($rowId, $cartItem->qty + 1);
        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $cartItem = Cart::instance('cart')->get($rowId);
        if ($cartItem->qty > 1) {
            Cart::instance('cart')->update($rowId, $cartItem->qty - 1);
            return redirect()->back();
        }

        return redirect()->back()->with('info', 'Item quantity cannot be less than 1.');
    }
    public function remove_from_cart($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->route('cart.index');
    }
    public function emptyCart(Request $request)
    {
        Cart::instance('cart')->destroy();
        return redirect()->route('cart.index')->with('success', 'Cart has been emptied.');
    }
    public function apply_coupon_code(Request $request)
    {
        $coupon_code = $request->coupon_code;

        if (isset($coupon_code)) {
            $coupon = Coupon::where('code', $coupon_code)
                ->where('expiry_date', '>=', Carbon::today())
                ->where('cart_value', '<=', Cart::instance('cart')->subtotal()) // Check for subtotal requirement
                ->first();
            if (!$coupon) {
                return redirect()->back()->with('error', 'Invalid coupon code!');
            } else {
                Session::put('coupon', [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value
                ]);
                $this->calculateDiscount();
                return redirect()->back()->with('success', 'Coupon code applied successfully!');
            }
        } else {
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }
    }
    public function calculateDiscount()
    {
        $discount = 0;

        if (Session::has('coupon')) {
            if (Session::get('coupon')['type'] === 'fixed') {
                $discount = Session::get('coupon')['value'];
            } else {
                $discount = Cart::instance('cart')->subtotal() * Session::get('coupon')['value'] / 100;
            }

            $subtotalAfterDiscount = Cart::instance('cart')->subtotal() - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts', [
                'discount' => number_format(floatval($discount), 2, '.', ''),
                'subtotal' => number_format(floatval($subtotalAfterDiscount), 2, '.', ''),
                'tax' => number_format(floatval($taxAfterDiscount), 2, '.', ''),
                'total' => number_format(floatval($totalAfterDiscount), 2, '.', '')
            ]);
        }
    }
    public function remove_coupon_code()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return redirect()->back()->with('success', 'Coupon removed successfully!');
    }
    // public function setAmountForCheckout()
    // {
    //     if (Cart::instance('cart')->content()->count() === 0) {
    //         // If the cart is empty, reset the checkout session and return
    //         Session::forget('checkout');
    //         return;
    //     }
    //     // Set the session data based on whether a coupon is applied or not
    //     if (Session::has('discounts')) {
    //         $discounts = Session::get('discounts');
    //         Session::put('checkout', [
    //             'discount' => $discounts['discount'] ?? 0,
    //             'subtotal' => $discounts['subtotal'] ?? Cart::instance('cart')->subtotal(),
    //             'tax' => $discounts['tax'] ?? Cart::instance('cart')->tax(),
    //             'total' => $discounts['total'] ?? Cart::instance('cart')->total(),
    //         ]);
    //     } else {
    //         Session::put('checkout', [
    //             'discount' => 0,
    //             'subtotal' => Cart::instance('cart')->subtotal(),
    //             'tax' => Cart::instance('cart')->tax(),
    //             'total' => Cart::instance('cart')->total(),
    //         ]);
    //     }
    // }
    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $address = Address::where('user_id', Auth::user()->id)->where('is_default', 1)->first();
        return view('checkout', compact('address'));
    }
    public function place_an_order(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('is_default', true)->first();

        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);

            $address = new Address();
            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = "";
            $address->user_id = $user_id;
            $address->is_default = true;
            $address->save();
        }
        $this->setAmountForCheckout();
        $order = new Order();
        $order->user_id = $user_id;
        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->save();

        // Create order items
        foreach (Cart::instance('cart')->content() as $item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        // Handle payment mode
        $mode = $request->input('mode');

        if ($mode === 'cod') {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $mode;
            $transaction->status = 'pending';
            $transaction->save();
        } elseif ($mode === 'card') {
            // Code for card payment
        } elseif ($mode === 'paypal') {
            // Code for PayPal payment
        } else {
            return redirect()->route('cart.index')->withErrors('Invalid payment mode.');
        }

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        return redirect()->route('cart.order.confirmation');
    }
    public function setAmountForCheckout()
    {
        if (Cart::instance('cart')->content()->count() > 0) {
            $checkoutData = [
                'discount' => 0,
                'subtotal' => Cart::instance('cart')->subtotal(),
                'tax' => Cart::instance('cart')->tax(),
                'total' => Cart::instance('cart')->total(),
            ];
            if (Session::has('coupon')) {
                $discounts = Session::get('discounts', []);
                $checkoutData['discount'] = $discounts['discount'] ?? 0;
                $checkoutData['subtotal'] = $discounts['subtotal'] ?? $checkoutData['subtotal'];
                $checkoutData['tax'] = $discounts['tax'] ?? $checkoutData['tax'];
                $checkoutData['total'] = $discounts['total'] ?? $checkoutData['total'];
            }
            Session::put('checkout', $checkoutData);
        } else {
            // Clear checkout session if no items in cart
            Session::forget('checkout');
        }
    }


    public function order_confirmation()
    {
        if (session()->has('order_id')) {
            $order = Order::find(session()->get('order_id'));
            return view('order-confirmation', compact('order'));
        }

        return redirect()->route('cart.index');
    }
}
