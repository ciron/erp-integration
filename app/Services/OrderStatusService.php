<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * OrderStatusService - Safe Status Update Handler
 * 
 * Part 3 Requirement: Safe write operation for legacy ERP integration
 * 
 * This service handles status updates with:
 * 1. State machine validation (enforces valid transitions)
 * 2. Race condition prevention (database locking)
 * 3. ColdFusion coexistence safety (transaction isolation)
 * 
 * Race Condition Prevention Strategy:
 * - Uses SELECT FOR UPDATE to lock the row during transaction
 * - Prevents concurrent updates from Laravel or ColdFusion
 * - Transaction ensures atomic read-validate-update operation
 * 
 * ColdFusion Conflict Avoidance:
 * - Database-level locking works across all applications
 * - Short transaction duration minimizes lock contention
 * - Read Committed isolation level prevents dirty reads
 * - No application-level caching that could cause stale data
 */
class OrderStatusService
{
    /**
     * Update order status with validation and locking.
     * 
     * @param int $orderId
     * @param string $newStatus
     * @return Order
     * @throws InvalidArgumentException if transition is invalid
     * @throws \Exception if update fails
     */
    public function updateStatus(int $orderId, string $newStatus): Order
    {
        // Start database transaction
        return DB::transaction(function () use ($orderId, $newStatus) {
            
            // Lock the row for update (prevents race conditions)
            // This works across Laravel AND ColdFusion applications
            $order = Order::where('order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                throw new InvalidArgumentException("Order #{$orderId} not found");
            }

            // Validate status transition using state machine
            if (!$order->canTransitionTo($newStatus)) {
                throw new InvalidArgumentException(
                    "Invalid status transition from '{$order->status}' to '{$newStatus}'"
                );
            }

            // Perform the update
            $order->status = $newStatus;
            $order->save();

            return $order;
        });
    }

    /**
     * Alternative implementation using optimistic locking.
     * 
     * This approach uses a version column to detect concurrent modifications.
     * Requires adding a 'version' column to the orders table.
     * 
     * Note: Not implemented here as we cannot modify the legacy schema,
     * but this is the preferred approach if schema changes were allowed.
     * 
     * @param int $orderId
     * @param string $newStatus
     * @param int $expectedVersion
     * @return Order
     */
    public function updateStatusOptimistic(int $orderId, string $newStatus, int $expectedVersion): Order
    {
        return DB::transaction(function () use ($orderId, $newStatus, $expectedVersion) {
            
            $order = Order::findOrFail($orderId);

            // Check if version matches (no concurrent modification)
            if ($order->version !== $expectedVersion) {
                throw new InvalidArgumentException(
                    "Order has been modified by another process. Please refresh and try again."
                );
            }

            // Validate transition
            if (!$order->canTransitionTo($newStatus)) {
                throw new InvalidArgumentException(
                    "Invalid status transition from '{$order->status}' to '{$newStatus}'"
                );
            }

            // Update with version increment
            $updated = DB::table('orders')
                ->where('order_id', $orderId)
                ->where('version', $expectedVersion)
                ->update([
                    'status' => $newStatus,
                    'version' => $expectedVersion + 1,
                ]);

            if ($updated === 0) {
                throw new InvalidArgumentException(
                    "Concurrent modification detected. Please retry."
                );
            }

            return Order::findOrFail($orderId);
        });
    }

    /**
     * Batch update statuses (use raw SQL for performance).
     * 
     * When to use this instead of Eloquent:
     * - Updating 100+ orders at once
     * - Performance is critical
     * - Simple status updates without complex logic
     * 
     * @param array $orderIds
     * @param string $newStatus
     * @param string $fromStatus
     * @return int Number of updated records
     */
    public function batchUpdateStatus(array $orderIds, string $newStatus, string $fromStatus): int
    {
        // Validate status transition is valid
        if (!in_array($newStatus, Order::VALID_TRANSITIONS[$fromStatus] ?? [])) {
            throw new InvalidArgumentException(
                "Invalid batch status transition from '{$fromStatus}' to '{$newStatus}'"
            );
        }

        // Use raw SQL for performance on bulk operations
        return DB::table('orders')
            ->whereIn('order_id', $orderIds)
            ->where('status', $fromStatus)
            ->update(['status' => $newStatus]);
    }
}
