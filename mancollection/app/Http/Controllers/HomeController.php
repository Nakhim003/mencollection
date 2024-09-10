<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index(Request $request)
    {
        $slides = Slide::where('status', 1)->take(3)->get();
        $categories = Category::orderBy('name')->get();
        $sproducts = Product::whereNotNull('sale_price')
            ->where('sale_price', '<>', '')
            ->inRandomOrder()
            ->take(8)
            ->get();
        // Paginate featured products, initially showing 4 products
        $perPage = 4;
        $fproducts = Product::where('featured', 1)->paginate($perPage);
        if ($request->ajax()) {
            // Return only the HTML for the new products
            return view('partials.featured-products', compact('fproducts'))->render();
        }
        return view('index', compact('slides', 'categories', 'sproducts', 'fproducts'));
    }
    public function loadMore(Request $request)
    {
        $perPage = $request->input('per_page', 8);
        $page = $request->input('page', 1);

        $fproducts = Product::where('featured', 1)->paginate($perPage, ['*'], 'page', $page);

        return view('partials.featured-products', compact('fproducts'))->render();
    }
    public function contact()
    {
        return view('contacts');
    }
    public function contact_store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required'
        ]);
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->comment = $request->comment;
        $contact->save();

        return redirect()->back()->with('success', 'Your message has been sent successfully');
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }
}
