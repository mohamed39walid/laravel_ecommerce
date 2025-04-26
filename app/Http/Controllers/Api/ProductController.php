<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        try {
            return ProductResource::collection(Product::with('categories')->get());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch products', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with('categories')->find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return new ProductResource($product);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch product', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
                return response()->json(['message' => 'Product not found'], 404);
            }

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
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete product', 'error' => $e->getMessage()], 500);
        }
    }
}
