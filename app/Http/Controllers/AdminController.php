<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image; // Correct the Facade

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
        $brand = Brand::find($id); // Correct variable naming
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request, $id) // Added $id parameter
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id, // Fix unique rule for editing
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048', // Make image optional during update
        ]);

        $brand = Brand::find($id);

        if (!$brand) {
            return redirect()->back()->with('error', 'Brand not found.');
        }

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);

        // Check if a new image is uploaded
        if ($request->hasFile('image')) {
            // Delete the existing image if it exists
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }

            // Store the new image
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;

            // Generate and save the brand image and thumbnail
            $this->GenerateBrandAndThumbnailsImage($image, $file_name);

            // Update the brand image path
            $brand->image = $file_name;
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully!');
    }

    // Method to generate the main image and thumbnail
    public function GenerateBrandAndThumbnailsImage($image, $imageName)
    {
        // Define destination paths
        $destinationPath = public_path('uploads/brands');
        $thumbnailPath = public_path('uploads/brands/thumbnails');

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

    public function brand_delete($id){
        $brand = Brand ::find($id);
        if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Brand has been deleted successfully!');

    }

    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories',compact('categories'));
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
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'slug' => 'required|string|max:100',
        'category_id' => 'required|exists:categories,id',
        'brand_id' => 'required|exists:brands,id',
        'short_description' => 'required|string|max:100',
        'description' => 'required|string',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'SKU' => 'required|string|max:100',
        'quantity' => 'required|numeric',
        'stock_status' => 'required|in:instock,outofstock',
        'featured' => 'required|in:0,1',
        'image' => 'required|image',
        'images' => 'nullable|array',
        'images.*' => 'nullable|image',
    ]);

    // Handle file uploads
    $imagePath = $request->file('image')->store('uploads/products/thumbnails', 'public');
    $galleryPaths = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $galleryPaths[] = $image->store('uploads/products/gallery', 'public');
        }
    }

    // Create the product
    $product = Product::create([
        'name' => $request->name,
        'slug' => $request->slug,
        'category_id' => $request->category_id,
        'brand_id' => $request->brand_id,
        'short_description' => $request->short_description,
        'description' => $request->description,
        'regular_price' => $request->regular_price,
        'sale_price' => $request->sale_price,
        'SKU' => $request->SKU,
        'quantity' => $request->quantity,
        'stock_status' => $request->stock_status,
        'featured' => $request->featured,
        'image' => basename($imagePath),  // Save the filename, not the full path
        'gallery_images' => json_encode($galleryPaths),  // Save the paths of gallery images as a JSON array
    ]);

    return redirect()->route('admin.products')->with('success', 'Product added successfully');
}


}
