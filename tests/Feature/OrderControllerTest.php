<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * OrderController Feature Tests
 * 
 * Note: These tests demonstrate the testing approach.
 * In production, you would NOT use RefreshDatabase with a legacy table.
 * Instead, use database transactions or a separate test database.
 */
class OrderControllerTest extends TestCase
{
    /**
     * Test order listing page loads successfully.
     */
    public function test_order_index_page_loads(): void
    {
        $response = $this->get('/orders');
        
        $response->assertStatus(200);
        $response->assertViewIs('orders.index');
        $response->assertViewHas('orders');
        $response->assertViewHas('statuses');
    }

    /**
     * Test order filtering by status.
     */
    public function test_order_filtering_by_status(): void
    {
        $response = $this->get('/orders?status=pending');
        
        $response->assertStatus(200);
        $response->assertViewHas('currentStatus', 'pending');
    }

    /**
     * Test API endpoint returns JSON.
     */
    public function test_api_orders_returns_json(): void
    {
        $response = $this->getJson('/api/orders');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'meta' => [
                'count',
                'filter',
            ],
        ]);
    }

    /**
     * Test API filtering by status.
     */
    public function test_api_orders_filters_by_status(): void
    {
        $response = $this->getJson('/api/orders?status=paid');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'meta' => [
                'filter' => 'paid',
            ],
        ]);
    }

    /**
     * Test status update with valid transition.
     * 
     * Note: This test would require a test database with sample data.
     * Commented out to avoid modifying production data.
     */
    public function test_status_update_with_valid_transition(): void
    {
        $this->markTestSkipped('Requires test database setup');
        
        // Create test order
        // $order = Order::create([...]);
        
        // $response = $this->postJson("/api/orders/{$order->order_id}/status", [
        //     'status' => 'paid',
        // ]);
        
        // $response->assertStatus(200);
        // $response->assertJson(['success' => true]);
    }

    /**
     * Test status update with invalid transition.
     */
    public function test_status_update_with_invalid_transition(): void
    {
        $this->markTestSkipped('Requires test database setup');
        
        // Test that completed → pending is rejected
    }

    /**
     * Test status update with invalid status value.
     */
    public function test_status_update_with_invalid_status(): void
    {
        $response = $this->postJson('/api/orders/1/status', [
            'status' => 'invalid_status',
        ]);
        
        $response->assertStatus(422); // Validation error
    }
}
