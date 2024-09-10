<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
use App\Models\AboutUs;
class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->take(10)->get();
        $dashboardDatas = DB::select("
            SELECT 
                SUM(total) AS TotalAmount,
                SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount,
                COUNT(*) AS Total,
                SUM(IF(status = 'ordered', 1, 0)) AS TotalOrdered,
                SUM(IF(status = 'delivered', 1, 0)) AS TotalDelivered,
                SUM(IF(status = 'canceled', 1, 0)) AS TotalCanceled
            FROM orders
        ");
        // Query to get monthly data
        $monthlyDatas = DB::select("
            SELECT 
                M.id AS MonthNo, 
                M.name AS MonthName,
                IFNULL(D.TotalAmount, 0) AS TotalAmount,
                IFNULL(D.TotalOrderedAmount, 0) AS TotalOrderedAmount,
                IFNULL(D.TotalDeliveredAmount, 0) AS TotalDeliveredAmount,
                IFNULL(D.TotalCanceledAmount, 0) AS TotalCanceledAmount
            FROM month_names M
            LEFT JOIN (
                SELECT 
                    DATE_FORMAT(created_at, '%b') AS MonthName,
                    MONTH(created_at) AS MonthNo,
                    SUM(total) AS TotalAmount,
                    SUM(IF(status = 'ordered', total, 0)) AS TotalOrderedAmount,
                    SUM(IF(status = 'delivered', total, 0)) AS TotalDeliveredAmount,
                    SUM(IF(status = 'canceled', total, 0)) AS TotalCanceledAmount
                FROM orders 
                WHERE YEAR(created_at) = YEAR(NOW()) 
                GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
                ORDER BY MONTH(created_at)
            ) D 
            ON D.MonthNo = M.id
        ");
        // Preparing data for charts
        $Amount = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $OrderedAmount = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmount = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmount = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());
        // Summing up the totals
        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
        return view('admin.index', compact(
            'orders',
            'dashboardDatas',
            'Amount',
            'OrderedAmount',
            'DeliveredAmount',
            'CanceledAmount',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
    }
    // Start Brand Controller 
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function addBrands()
    {
        return view('admin.brands-add');
    }
    public function brands_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|unique:brands,slug',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $imagePath = $this->Brand_storeImage($request->file('image'));
        if (!$imagePath) {
            return redirect()->back()->withErrors(['image' => 'Error uploading image']);
        }
        $brand->image = $imagePath;
        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand created successfully.');
    }
    public function brands_edit($id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands-edit', compact('brand'));
    }
    // Handle the submission of the edit form to update the brand
    public function brands_update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        // Validate the request inputs
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|unique:brands,slug,' . $brand->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // Update the brand's name and slug
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        // Handle the image if one is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($brand->image && file_exists(public_path('uploads/brands/' . $brand->image))) {
                unlink(public_path('uploads/brands/' . $brand->image));
            }
            // Store the new image
            $imagePath = $this->Brand_storeImage($request->file('image'));
            // Handle errors during image upload
            if (!$imagePath) {
                return redirect()->back()->withErrors(['image' => 'Error uploading image']);
            }
            // Assign the new image path to the brand
            $brand->image = $imagePath;
        }
        // Save the updated brand to the database
        $brand->save();
        // Redirect with a success message
        return redirect()->route('admin.brands')->with('status', 'Brand updated successfully.');
    }

    private function Brand_storeImage($image)
    {
        if ($image) {
            $destinationPath = public_path('uploads/brands');
            $imageName = time() . '_' . $image->getClientOriginalName();
            // Use Intervention Image to resize and save the image
            $img = Image::make($image->path());
            $img->fit(124, 124, function ($constraint) {
                $constraint->upsize(); // Prevent upsizing the image
            });
            $img->save($destinationPath . '/' . $imageName);
            return $imageName;
        }
        return null;
    }

    public function brands_destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            // Delete the old image if it exists
            if ($brand->image && file_exists(public_path('uploads/brands/' . $brand->image))) {
                unlink(public_path('uploads/brands/' . $brand->image));
            }
            $brand->delete();
            return redirect()->route('admin.brands')->with('status', 'Brand deleted successfully.');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error deleting brand with ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('admin.brands')->with('error', 'Error deleting brand. Please try again.');
        }
    } //End Brand Controller

    //Start Category Controller 
    public function category()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }
    public function category_add()
    {
        // $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories-add');
    }
    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:categories,slug',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $imagePath = $this->storeCategoryImage($request->file('image'), $imageName);
            if (!$imagePath) {
                return redirect()->back()->withErrors(['image' => 'Error uploading image']);
            }
            $category->image = $imagePath;
        }
        $category->parent_id = $request->parent_id;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category created successfully.');
    }
    public function category_edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories-edit', compact('category'));
    }

    public function category_update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:categories,slug,' . $category->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
        ]);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if ($request->hasFile('image')) {
            if ($category->image && file_exists(public_path('uploads/categories/' . $category->image))) {
                unlink(public_path('uploads/categories/' . $category->image));
            }
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $imagePath = $this->storeCategoryImage($request->file('image'), $imageName);
            if (!$imagePath) {
                return redirect()->back()->withErrors(['image' => 'Error uploading image']);
            }
            $category->image = $imagePath;
        }
        $category->parent_id = $request->parent_id;
        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category updated successfully.');
    }
    public function category_destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            if ($category->image && file_exists(public_path('uploads/categories/' . $category->image))) {
                unlink(public_path('uploads/categories/' . $category->image));
            }
            $category->delete();
            return redirect()->route('admin.categories')->with('status', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting category with ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('admin.categories')->with('error', 'Error deleting category. Please try again.');
        }
    }
    private function storeCategoryImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::make($image->path());
        $img->fit(124, 124, function ($constraint) {
            $constraint->upsize();
        });
        $img->save($destinationPath . '/' . $imageName);
        return $imageName;
    } //end Category Controller
    //Start Product Cintroller 
    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function products_add()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.products-add', compact('categories', 'brands'));
    }

    public function products_store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'required|string|unique:products,SKU',
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        // Create a new product instance
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured ?? false;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        // Handle the main product image upload
        if ($request->hasFile('image')) {
            $imageName = time() . '-' . $request->file('image')->getClientOriginalName();
            $this->products_storeImage($request->file('image'), $imageName);
            $product->image = $imageName;
        }

        // Handle multiple thumbnail images upload
        if ($request->hasFile('images')) {
            $thumbnailPaths = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '-' . $image->getClientOriginalName();
                $this->products_storeImage($image, $imageName);
                $thumbnailPaths[] = $imageName;
            }
            $product->images = json_encode($thumbnailPaths);
        }

        // Save the product
        $product->save();

        // Redirect with a success message
        return redirect()->route('admin.products')->with('status', 'Product added successfully.');
    }

    public function products_edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.products-edit', compact('product', 'categories', 'brands'));
    }

    public function products_update(Request $request, $id)
    {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug,' . $product->id,
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'SKU' => 'required|string|unique:products,SKU,' . $product->id,
            'stock_status' => 'required|in:instock,outofstock',
            'featured' => 'boolean',
            'quantity' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        // Update product attributes
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured ?? false;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        // Handle the main product image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
                unlink(public_path('uploads/products/' . $product->image));
            }
            // Store the new image
            $imageName = time() . '-' . $request->file('image')->getClientOriginalName();
            $this->products_storeImage($request->file('image'), $imageName);
            $product->image = $imageName; // Update the product image path
        }
        if ($request->hasFile('images')) {
            // Delete the old gallery images if they exist
            if ($product->images) {
                $oldImages = json_decode($product->images, true);
                foreach ($oldImages as $img) {
                    if (file_exists(public_path('uploads/products/thumbnails/' . $img))) {
                        unlink(public_path('uploads/products/thumbnails/' . $img));
                    }
                }
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '-' . $image->getClientOriginalName();
                $this->products_storeImage($image, $imageName);
                $imagePaths[] = $imageName; // Store the image names
            }
            $product->images = json_encode($imagePaths);
        }
        // Save the updated product
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product updated successfully.');
    }

    public function products_destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && file_exists(public_path('uploads/products/' . $product->image))) {
            unlink(public_path('uploads/products/' . $product->image));
        }

        if ($product->images) {
            $oldImages = json_decode($product->images, true);
            foreach ($oldImages as $img) {
                if (file_exists(public_path('uploads/products/thumbnails/' . $img))) {
                    unlink(public_path('uploads/products/thumbnails/' . $img));
                }
            }
        }

        // Delete the product record
        $product->delete();

        return redirect()->route('admin.products')->with('status', 'Product deleted successfully.');
    }

    private function products_storeImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');

        // Ensure the directories exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        if (!file_exists($destinationPathThumbnail)) {
            mkdir($destinationPathThumbnail, 0755, true);
        }

        $img = Image::make($image->path());

        // Save main image
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);

        // Save thumbnail image
        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail . '/' . $imageName);
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }

    public function coupon_add()
    {
        return view('admin.coupons-add');
    }

    public function coupons_store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        Coupon::create($request->all());

        return redirect()->route('coupon.index')->with('success', 'Coupon added successfully!');
    }

    public function coupon_edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.coupons-edit', compact('coupon'));
    }

    public function coupons_update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code,' . $id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date',
        ]);

        $coupon = Coupon::findOrFail($id);
        $coupon->update($request->all());

        return redirect()->route('coupon.index')->with('success', 'Coupon updated successfully!');
    }

    public function coupons_destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('coupon.index')->with('success', 'Coupon deleted successfully!');
    }
    public function orders()
    {
        $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
        return view('admin.orders', compact('orders'));
    }
    public function order_details($order_id)
    {
        $order = Order::findOrFail($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();

        return view('admin.order-details', compact('order', 'orderItems', 'transaction'));
    }
    public function updateOrderStatus(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if ($request->order_status == "delivered") {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == "canceled") {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if ($request->order_status == "delivered") {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        return back()->with("status", "Status changed successfully!");
    }
    public function slide()
    {
        $slides = Slide::orderBy('id', 'desc')->paginate(12);
        return view('admin.slides', compact('slides'));
    }
    public function slide_add()
    {
        return view('admin.slide-add');
    }
    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;
        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->GenerateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slide')->with("status", "Slide added successfully!");
    }

    public function GenerateSlideThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');

        // Create an instance of the image
        $img = Image::make($image);

        // Apply the cover effect and resize
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }
    public function slide_edit($id)
    {
        $slide = Slide::findOrFail($id); // Find the slide by ID or fail
        return view('admin.slide-edit', compact('slide'));
    }
    public function slide_update(Request $request, $id)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        ]);

        $slide = Slide::findOrFail($id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        // Check if a new image is being uploaded
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($slide->image && file_exists(public_path('uploads/slides/' . $slide->image))) {
                unlink(public_path('uploads/slides/' . $slide->image));
            }

            // Handle the new image upload
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->GenerateSlideThumbnailsImage($image, $file_name);
            $slide->image = $file_name;
        }

        $slide->save();
        return redirect()->route('admin.slide')->with("status", "Slide updated successfully!");
    }
    public function slide_destoy($id)
    {
        $slide = Slide::findOrFail($id);
        if ($slide->image && file_exists(public_path('uploads/slides/' . $slide->image))) {
            unlink(public_path('uploads/slides/' . $slide->image));
        }
        $slide->delete();
        return redirect()->route('admin.slide')->with('status', 'Slide deleted successfully!');
    }
    public function contact()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }
    public function contact_destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return redirect()->route('admin.contact')->with('status', 'Contact deleted successfully!');
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }
    public function about()
    {
        $aboutUs = AboutUs::first(); // Assuming there's only one record
        return view('admin.about', compact('aboutUs'));
    }
}
