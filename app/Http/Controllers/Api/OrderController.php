<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Cart;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ApiBaseController
{
    /**
     * Display user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with('items.product.primaryImage')
            ->byUser($request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->success([
            'orders' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::with(['items.product.primaryImage', 'statusHistory.user', 'paymentTransaction'])
            ->where('order_number', $orderNumber)
            ->byUser($request->user()->id)
            ->firstOrFail();

        return $this->success([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Create a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'shipping_method' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $cart = Cart::with('items.product.vendor', 'items.product.primaryImage')
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return $this->error('Cart is empty', 422);
        }

        // Check stock availability
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return $this->error("Insufficient stock for {$item->product->name}", 422);
            }
        }

        DB::beginTransaction();
        try {
            // Get address
            $address = $user->addresses()->findOrFail($request->address_id);

            // Calculate totals
            $subtotal = $cart->total;
            $shippingCost = 0; // TODO: Implement shipping calculation
            $discount = 0;
            $total = $subtotal + $shippingCost - $discount;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $request->notes,
                'shipping_name' => $address->name,
                'shipping_phone' => $address->phone,
                'shipping_address' => $address->street_address,
                'shipping_province' => $address->province->name ?? '',
                'shipping_city' => $address->city->name ?? '',
                'shipping_district' => $address->district->name ?? '',
                'shipping_postal_code' => $address->postal_code,
                'shipping_method' => $request->shipping_method,
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_slug' => $item->product->slug,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity,
                ]);

                // Decrement product stock
                $item->product->decrementStock($item->quantity);
                $item->product->incrementSoldCount($item->quantity);

                // Update vendor stats
                $item->product->vendor->increment('total_sales', $item->quantity);
            }

            // Clear cart
            $cart->clear();

            DB::commit();

            return $this->success([
                'order' => $order->load('items.product.primaryImage'),
            ], 'Order created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cancel order.
     */
    public function cancel(Request $request, int $order): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($order);

        if (!$order->canBeCancelled()) {
            return $this->error('Order cannot be cancelled', 422);
        }

        DB::beginTransaction();
        try {
            $order->updateStatus('cancelled', 'Cancelled by customer', $request->user());

            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
                $item->product->decrement('sold_count', $item->quantity);
            }

            DB::commit();

            return $this->success(null, 'Order cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to cancel order: ' . $e->getMessage(), 500);
        }
    }
}
