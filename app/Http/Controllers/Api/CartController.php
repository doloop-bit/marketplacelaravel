<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Product;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends ApiBaseController
{
    /**
     * Display user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::with('items.product.vendor', 'items.product.primaryImage')
            ->where('user_id', $request->user()->id)
            ->active()
            ->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $request->user()->id]);
        }

        return $this->success([
            'cart' => new CartResource($cart),
        ]);
    }

    /**
     * Add item to cart.
     */
    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::active()->findOrFail($request->product_id);

        // Check stock
        if ($product->stock < $request->quantity) {
            return $this->error('Insufficient stock', 422);
        }

        // Get or create cart
        $cart = Cart::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['is_active' => true]
        );

        $cartItem = $cart->addItem($product, $request->quantity);

        return $this->success([
            'cart_item' => $cartItem->load('product.primaryImage'),
            'cart_total' => $cart->total_quantity,
        ], 'Item added to cart');
    }

    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request, int $cartItem): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Cart::where('user_id', $request->user()->id)
            ->first()
            ?->items()
            ->findOrFail($cartItem);

        if (!$item) {
            return $this->error('Cart item not found', 404);
        }

        $product = $item->product;

        // Check stock
        if ($product->stock < $request->quantity) {
            return $this->error('Insufficient stock', 422);
        }

        $item->update(['quantity' => $request->quantity]);

        return $this->success([
            'cart_item' => $item->fresh('product.primaryImage'),
        ], 'Cart updated');
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Request $request, int $cartItem): JsonResponse
    {
        $item = Cart::where('user_id', $request->user()->id)
            ->first()
            ?->items()
            ->findOrFail($cartItem);

        if (!$item) {
            return $this->error('Cart item not found', 404);
        }

        $item->delete();

        return $this->success(null, 'Item removed from cart');
    }

    /**
     * Clear cart.
     */
    public function clearCart(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->clear();
        }

        return $this->success(null, 'Cart cleared');
    }
}
