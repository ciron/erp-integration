<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - ERP System</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
            color: #333;
        }

        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .clear-link {
            margin-left: 10px;
            color: #6c757d;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .amount {
            text-align: right;
            font-family: monospace;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .info-bar {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #004085;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Orders Dashboard</h1>

        <div class="info-bar">
            Showing latest 50 orders from production database
        </div>

        <div class="filters">
            <form method="GET" action="{{ url()->current() }}">
                <div class="filter-group">
                    <label for="status">Filter by status:</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ $currentStatus == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit">Apply Filter</button>
                    @if ($currentStatus)
                        <a href="{{ url()->current() }}" class="clear-link">Clear Filter</a>
                    @endif
                </div>
            </form>
        </div>

        @if ($orders->isEmpty())
            <div class="empty-state">
                <p>No orders found matching your criteria.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            // Handle both Eloquent model and stdClass from raw SQL
                            $orderId = $order->order_id ?? $order->order_id;
                            $customer = $order->customer_name ?? $order->customer_name;
                            $amount = $order->total_amount ?? $order->total_amount;
                            $status = $order->status ?? $order->status;
                            $createdAt = isset($order->created_at)
                                ? (is_string($order->created_at)
                                    ? \Carbon\Carbon::parse($order->created_at)
                                    : $order->created_at)
                                : \Carbon\Carbon::parse($order->created_at);
                        @endphp
                        <tr>
                            <td><strong>#{{ $orderId }}</strong></td>
                            <td>{{ $customer }}</td>
                            <td class="amount">${{ number_format($amount, 2) }}</td>
                            <td>
                                <span class="status status-{{ $status }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td>{{ $createdAt->format('M d, Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 20px; color: #6c757d; font-size: 14px;">
                Showing {{ $orders->count() }} most recent orders
                @if ($currentStatus)
                    with status: <strong>{{ $currentStatus }}</strong>
                @endif
            </div>
        @endif
    </div>

    <script>
        // Simple client-side enhancement
        document.getElementById('status').addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    </script>
</body>

</html>
