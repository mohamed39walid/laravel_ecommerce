<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::with('categories')->get());
    }    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|boolean',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $uploadedImages[] = asset('storage/' . $path);
            }
        }

        $data['images'] = $uploadedImages;

        $product = Product::create($data);
        $product->categories()->sync($data['category_ids']);

        return new ProductResource($product->load('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('categories')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name_en' => 'sometimes|required|string',
            'name_ar' => 'sometimes|required|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'sometimes|required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'status' => 'required|boolean',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'exists:categories,id'
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        // Upload new images if provided
        if ($request->hasFile('images')) {
            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $uploadedImages[] = asset('storage/' . $path);
            }

            $data['images'] = $uploadedImages;
        }

        $product->update($data);

        if (isset($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }

        return new ProductResource($product->load('categories'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
