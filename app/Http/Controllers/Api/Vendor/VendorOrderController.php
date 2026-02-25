<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorOrderController extends ApiBaseController
{
    /**
     * Display vendor's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $orders = Order::with(['items.product', 'user'])
            ->whereHas('items', function ($query) use ($vendor) {
                $query->whereHas('product', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                });
            })
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
    public function show(Request $request, int $order): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $order = Order::with(['items.product', 'user', 'statusHistory'])
            ->whereHas('items', function ($query) use ($vendor) {
                $query->whereHas('product', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                });
            })
            ->findOrFail($order);

        return $this->success([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return $this->error('Vendor profile not found', 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:processing,shipped,delivered',
            'notes' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ]);

        $order = Order::with(['items.product'])
            ->whereHas('items', function ($query) use ($vendor) {
                $query->whereHas('product', function ($q) use ($vendor) {
                    $q->where('vendor_id', $vendor->id);
                });
            })
            ->findOrFail($order);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (isset($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }

        $order->update($updateData);

        // Create status history
        $order->statusHistory()->create([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return $this->success([
            'order' => $order->fresh(['statusHistory']),
        ], 'Order status updated successfully');
    }
}
