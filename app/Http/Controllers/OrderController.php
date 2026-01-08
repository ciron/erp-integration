<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

/**
 * OrderController - Legacy ERP Integration
 *
 * Handles read-only views and controlled updates for the legacy orders table.
 * Designed to work safely alongside existing ColdFusion application.
 */
class OrderController extends Controller
{
    protected OrderStatusService $statusService;

    public function __construct(OrderStatusService $statusService)
    {
        $this->statusService = $statusService;
    }


    /* Display latest 50 orders with optional status filtering.

        Part 2 Requirement: Read-only ERP view (Blade)
        - Lists latest 50 orders
        - Allows filtering by status
        - Sorts by created_at DESC */

    public function index(Request $request)
    {

        $query = Order::query();

        if ($request->filled('status')) {
            $status = $request->status;

            // Validate status before applying filter
            if (in_array($status, Order::STATUSES)) {
                $query->where('status', $status);
            }
        }

        // Get latest 50 orders sorted by created_at DESC
        $orders = $query->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'currentStatus' => $request->status,
        ]);
    }


    //  API endpoint for orders (JSON response).

    public function apiIndex(Request $request): JsonResponse
    {
        // Start with base query
        $query = Order::query();

        // Apply status filter if provided
        if ($request->filled('status')) {
            $status = $request->status;

            if (in_array($status, Order::STATUSES)) {
                $query->where('status', $status);
            }
        }

        // Get latest 50 orders
        $orders = $query->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'meta' => [
                'count' => $orders->count(),
                'filter' => $request->status ?? 'all',
            ],
        ]);
    }

    /*
      Update order status with validation.

      Part 3 Requirement: Safe write operation
      - Updates status field only
      - Enforces valid transitions
      - Prevents race conditions
      - Safe for ColdFusion coexistence

     */
    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        // Validate input
        $request->validate([
            'status' => 'required|string|in:' . implode(',', Order::STATUSES),
        ]);

        try {
            // Use service to handle status update with proper locking
            $order = $this->statusService->updateStatus($orderId, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //  Alternative implementation using raw SQL.

    public function indexRaw(Request $request)
    {
        $status = $request->filled('status') ? $request->status : null;

        // Raw SQL with parameter binding (safe from SQL injection)
        // Note: This assumes indexes exist on 'status' and 'created_at' columns
        $sql = "
            SELECT order_id, customer_name, total_amount, status, created_at
            FROM orders
            WHERE 1=1
        ";

        $params = [];

        if ($status && in_array($status, Order::STATUSES)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 50";

        // Execute raw query with parameter binding
        $orders = DB::select($sql, $params);

        return view('orders.index', [
            'orders' => collect($orders), // Convert to collection for Blade compatibility
            'statuses' => Order::STATUSES,
            'currentStatus' => $status,
        ]);
    }


}

