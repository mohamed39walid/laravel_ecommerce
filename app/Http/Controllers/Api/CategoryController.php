<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return CategoryResource::collection(Category::all());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'description_en' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|boolean',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $validated['image'] = $path;
            }

            $category = Category::create($validated);

            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json(['message' => 'Category not found.'], 404);
            }

            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to load category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json(['message' => 'Category not found.'], 404);
            }

            $validated = $request->validate([
                'name_en' => 'sometimes|required|string|max:255',
                'name_ar' => 'sometimes|required|string|max:255',
                'description_en' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|boolean',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('categories', 'public');
                $validated['image'] = $path;
            }

            $category->update($validated);

            return new CategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json(['message' => 'Category not found.'], 404);
            }

            $category->delete();

            return response()->json(['message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
