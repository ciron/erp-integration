<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Order Model - Legacy ERP Table Integration
 * 
 * This model integrates with an existing ColdFusion ERP system's MySQL database.
 * The table schema is mature and cannot be modified.
 * 
 * Key Customizations:
 * - Table name: 'orders' (legacy convention, not Laravel's default)
 * - Primary key: 'order_id' (not 'id')
 * - Timestamps: Only 'created_at' exists (no 'updated_at')
 * - Auto-increment: Set to false as legacy system may use custom ID generation
 * 
 * When NOT to use Eloquent for this table:
 * 1. Complex reporting queries with multiple joins and aggregations
 * 2. Bulk operations (10,000+ records) - use raw SQL with chunking
 * 3. Performance-critical queries requiring specific index hints
 * 4. Legacy stored procedures or database-specific features
 * 5. Complex analytical queries better suited for raw SQL optimization
 */
class Order extends Model
{
    /**
     * The table associated with the model.
     * Legacy table name does not follow Laravel conventions.
     */
    protected $table = 'orders';

    /**
     * The primary key for the model.
     * Legacy system uses 'order_id' instead of 'id'.
     */
    protected $primaryKey = 'order_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Set to true if legacy system uses auto-increment, false if custom generation.
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     * Legacy table only has 'created_at', no 'updated_at'.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * Protecting against mass assignment vulnerabilities.
     */
    protected $fillable = [
        'customer_name',
        'total_amount',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Valid order statuses.
     * These represent the allowed states in the legacy system.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    /**
     * Array of all valid statuses.
     */
    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_CANCELLED,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
    ];

    /**
     * Valid status transitions.
     * Defines which status changes are allowed.
     */
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PAID, self::STATUS_CANCELLED, self::STATUS_PROCESSING],
        self::STATUS_PROCESSING => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_PAID => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [], // Terminal state
        self::STATUS_CANCELLED => [], // Terminal state
    ];

    /**
     * Scope a query to only include orders with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get latest orders.
     */
    public function scopeLatest($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Check if a status transition is valid.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if (!in_array($newStatus, self::STATUSES)) {
            return false;
        }

        $allowedTransitions = self::VALID_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $allowedTransitions);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }
}

