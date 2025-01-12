<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);

        // Store the brand data
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = $request->slug;

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('brands', 'public');
            $brand->image = $filePath;
        }

        $brand->save();

        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id,
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = Brand::find($id);

        if (!$brand) {
            return redirect()->back()->with('error', 'Brand not found.');
        }

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        if ($request->hasFile('image')) {
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }

            $image = $request->file('image');
            $fileExtension = $image->extension();
            $fileName = Carbon::now()->timestamp . '.' . $fileExtension;

            $this->generateBrandAndThumbnailImage($image, $fileName);

            $brand->image = $fileName;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully!');
    }

    public function generateBrandAndThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $thumbnailPath = public_path('uploads/brands/thumbnails');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($thumbnailPath)) {
            File::makeDirectory($thumbnailPath, 0755, true);
        }

        $img = Image::make($image);
        $img->save($destinationPath . '/' . $imageName);

        $thumbnail = $img->resize(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        });
        $thumbnail->save($thumbnailPath . '/' . $imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully!');
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048',
        ]);


        // Store the brand data
        $category = new Category();
        $category->name = $request->name;
        $category->slug = $request->slug;


        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('category', 'public');
            $category->image = $filePath;
        }


        $category->save();


        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function GenerateCategoryAndThumbnailsImage($image, $imageName)
    {
        // Define destination paths
        $destinationPath = public_path('uploads/categories');
        $thumbnailPath = public_path('uploads/categories/thumbnails');


        // Ensure the directories exist
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($thumbnailPath)) {
            File::makeDirectory($thumbnailPath, 0755, true);
        }


        // Save the original image
        $img = Image::make($image);  // Correct method to load image
        $img->save($destinationPath . '/' . $imageName);


        // Generate and save the thumbnail
        $thumbnail = $img->resize(150, 150, function ($constraint) {
            $constraint->aspectRatio();
        });
        $thumbnail->save($thumbnailPath . '/' . $imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }


    public function category_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id, // Fix unique rule for editing
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048', // Make image optional during update
        ]);


        $category = Category::find($id);


        if (!$category) {
            return redirect()->back()->with('error', 'Category not found.');
        }


        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);


        // Check if a new image is uploaded
        if ($request->hasFile('image')) {
            // Delete the existing image if it exists
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }


            // Store the new image
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;


            $this->GenerateCategoryAndThumbnailsImage($image, $file_name);


            $category->image = $file_name;
        }


        $category->save();


        return redirect()->route('admin.categories')->with('status', 'Category has been updated successfully!');
    }


    public function category_delete($id){
        $category = Category ::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image)){
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Category has been deleted successfully!');


    }

    public function products()
    {
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }


    public function product_add()
    {
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('categories','brands'));
    }


    public function product_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'required|mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required',
    ]);

    $product = new Product();
    $product->name = $request->name;
    $product->slug = Str::slug($request->name);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    // Handle single image upload
    if ($request->hasFile('image')) {
        $imageName = Carbon::now()->timestamp . '.' . $request->image->extension();
        $filePath = $request->file('image')->storeAs('products', $imageName, 'public');
        $product->image = $filePath;
    }

    // Handle multiple images upload
    if ($request->hasFile('images')) {
        $galleryImages = [];
        foreach($request->file('images') as $key => $file) {
            $imageName = Carbon::now()->timestamp . '-' . ($key + 1) . '.' . $file->extension();
            $filePath = $file->storeAs('products/gallery', $imageName, 'public');
            $galleryImages[] = $filePath;
        }
        $product->images = implode(',', $galleryImages);
    }

    $product->save();
    return redirect()->route('admin.products')->with('status', 'Product has been added successfully');
}
    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/products');
        $thumbnailPath = public_path('uploads/products/thumbnails');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        if (!File::exists($thumbnailPath)) {
            File::makeDirectory($thumbnailPath, 0755, true);
        }

        // $img = Image::make($image);
        // $img->save($destinationPath . '/' . $imageName);

        // $thumbnail = $img->resize(150, 150, function ($constraint) {
        //     $constraint->aspectRatio();
        // });
        // $thumbnail->save($thumbnailPath . '/' . $imageName);

    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit', compact('product', 'categories', 'brands'));

    }

    public function product_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug,' . $request->id,
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required',
        'sale_price' => 'required',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required',
        'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        'category_id' => 'required',
        'brand_id' => 'required',
    ]);

    $product = Product::find($request->id);
    $product->name = $request->name;
    $product->slug = Str::slug($request->name);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    // Handle single image upload
    if ($request->hasFile('image')) {
        // Delete old image
        if($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $imageName = Carbon::now()->timestamp . '.' . $request->image->extension();
        $filePath = $request->file('image')->storeAs('products', $imageName, 'public');
        $product->image = $filePath;
    }

    // Handle multiple images upload
    if ($request->hasFile('images')) {
        // Delete old gallery images
        if($product->images) {
            $oldImages = explode(',', $product->images);
            foreach($oldImages as $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $galleryImages = [];
        foreach($request->file('images') as $key => $file) {
            $imageName = Carbon::now()->timestamp . '-' . ($key + 1) . '.' . $file->extension();
            $filePath = $file->storeAs('products/gallery', $imageName, 'public');
            $galleryImages[] = $filePath;
        }
        $product->images = implode(',', $galleryImages);
    }

    $product->save();
    return redirect()->route('admin.products')->with('status', 'Product has been updated successfully');
}

public function product_delete($id)
{
    $product = Product::find($id);

    // Delete main image
    if($product->image) {
        Storage::disk('public')->delete($product->image);
    }

    // Delete gallery images
    if($product->images) {
        $galleryImages = explode(',', $product->images);
        foreach($galleryImages as $image) {
            Storage::disk('public')->delete($image);
        }
    }

    $product->delete();
    return redirect()->route('admin.products')->with('status', 'Product has been deleted successfully!');
}
public function orders()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admin.orders',compact('orders'));
    }

    public function order_items($order_id){
        $order = Order::find($order_id);
          $orderitems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
          $transaction = $order->transaction;
          return view("admin.order-details",compact('order','orderitems','transaction'));
    }

    public function update_order_status (Request $request)
    {
        $order = Order::find($request->order_id);
    $order->status = $request->order_status;
    if($request->order_status=='delivered')
    {
        $order->delivered_date = Carbon::now();
    }
    else if($request->order_status=='canceled')
    {
        $order->canceled_date = Carbon::now();
    }
    $order->save();
    if($request->order_status=='delivered')
    {
        $transaction = Transaction::where('order_id',$request->order_id)->first();
        $transaction->status = "approved";
        $transaction->save();
    }
    return back()->with("status", "Status changed successfully!");
    }
}

