<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VendorProductController extends ApiBaseController
{
    /**
     * Display vendor's products.
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $products = Product::with(['category', 'primaryImage'])
            ->byVendor($vendor->id)
            ->latest()
            ->paginate(15);

        return $this->success([
            'products' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'max_order' => 'nullable|integer|min:1',
            'weight' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'specifications' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['vendor_id'] = $vendor->id;

        if ($request->has('dimensions')) {
            $validated['length'] = $request->dimensions['length'] ?? null;
            $validated['width'] = $request->dimensions['width'] ?? null;
            $validated['height'] = $request->dimensions['height'] ?? null;
        }

        $product = Product::create($validated);

        return $this->success([
            'product' => new ProductResource($product->load('category', 'images')),
        ], 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Request $request, int $product): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $product = Product::with(['category', 'images'])
            ->byVendor($vendor->id)
            ->findOrFail($product);

        return $this->success([
            'product' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, int $product): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $product = Product::byVendor($vendor->id)->findOrFail($product);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'max_order' => 'nullable|integer|min:1',
            'weight' => 'nullable|string',
            'dimensions' => 'nullable|array',
            'specifications' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->has('dimensions')) {
            $validated['length'] = $request->dimensions['length'] ?? null;
            $validated['width'] = $request->dimensions['width'] ?? null;
            $validated['height'] = $request->dimensions['height'] ?? null;
        }

        $product->update($validated);

        return $this->success([
            'product' => new ProductResource($product->fresh('category', 'images')),
        ], 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Request $request, int $product): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $product = Product::byVendor($vendor->id)->findOrFail($product);
        $product->delete();

        return $this->success(null, 'Product deleted successfully');
    }
}
