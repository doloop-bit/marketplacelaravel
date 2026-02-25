<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends ApiBaseController
{
    /**
     * Get admin dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_users' => User::count(),
            'total_vendors' => Vendor::count(),
            'pending_vendors' => Vendor::pending()->count(),
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
        ];

        return $this->success([
            'stats' => $stats,
        ]);
    }

    /**
     * Get all users.
     */
    public function users(): JsonResponse
    {
        $users = User::with('roles')->latest()->paginate(20);

        return $this->success([
            'users' => $users,
        ]);
    }

    /**
     * Get all vendors.
     */
    public function vendors(): JsonResponse
    {
        $vendors = Vendor::with(['user', 'approver'])->latest()->paginate(20);

        return $this->success([
            'vendors' => $vendors,
        ]);
    }

    /**
     * Get all products.
     */
    public function products(): JsonResponse
    {
        $products = Product::with(['vendor.user', 'category'])->latest()->paginate(20);

        return $this->success([
            'products' => $products,
        ]);
    }

    /**
     * Get all orders.
     */
    public function orders(): JsonResponse
    {
        $orders = Order::with(['user', 'items.product'])->latest()->paginate(20);

        return $this->success([
            'orders' => $orders,
        ]);
    }

    /**
     * Approve vendor.
     */
    public function approveVendor(Request $request, int $vendor): JsonResponse
    {
        $vendor = Vendor::findOrFail($vendor);

        $vendor->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        // Ensure user has vendor role
        $vendor->user->syncRoles(['vendor']);

        return $this->success([
            'vendor' => $vendor->fresh(['user']),
        ], 'Vendor approved successfully');
    }

    /**
     * Reject vendor.
     */
    public function rejectVendor(Request $request, int $vendor): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $vendor = Vendor::findOrFail($vendor);

        $vendor->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        // Remove vendor role
        $vendor->user->syncRoles(['customer']);

        return $this->success([
            'vendor' => $vendor->fresh(['user']),
        ], 'Vendor rejected successfully');
    }
}
