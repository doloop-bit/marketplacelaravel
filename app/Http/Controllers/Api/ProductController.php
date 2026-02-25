<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiBaseController
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->available();

        // Filter by category
        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by vendor
        if ($request->filled('vendor_id')) {
            $query->byVendor($request->vendor_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->priceRange($request->min_price, null);
        }
        if ($request->filled('max_price')) {
            $query->priceRange(null, $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('sold_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating_average', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate($request->get('per_page', 15));

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
     * Display featured products.
     */
    public function featured(): JsonResponse
    {
        $products = Product::with(['vendor', 'category', 'primaryImage'])
            ->featured()
            ->active()
            ->available()
            ->limit(10)
            ->get();

        return $this->success([
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $query = Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->search($request->get('q', ''));

        $products = $query->limit(20)->get();

        return $this->success([
            'products' => ProductResource::collection($products),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['vendor', 'category', 'images', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success([
            'product' => new ProductResource($product),
        ]);
    }
}
