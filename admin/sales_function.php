<?php
// File: admin/sales_functions.php

/**
 * Recalculates all sales metrics and updates both session and database
 *
 * @param mysqli $conn Database connection
 * @param string $filter Optional filter for status
 * @param string $date_range Optional date range filter
 * @return array The calculated sales metrics
 */
if (!function_exists('recalculateSalesMetrics')) {
    function recalculateSalesMetrics($conn, $filter = 'all', $date_range = '30days') {
        // Get sales data based on filters
        $salesReport = getSalesReport($conn, $filter, $date_range);

        // Initialize basic metrics
        $totalSales = 0;
        $totalOrders = count($salesReport);
        $completedOrders = 0;

        // Process orders data
        foreach ($salesReport as $sale) {
            if ($sale['order_status'] === 'Completed') {
                $totalSales += $sale['total_amount'];
                $completedOrders++;
            }
        }

        // Calculate average order value
        $averageOrderValue = $completedOrders > 0 ? $totalSales / $completedOrders : 0;

        // Get total distinct customers
        $customers_sql = "SELECT COUNT(DISTINCT user_id) as total_customers FROM orders WHERE order_status = 'Completed'";
        $customers_result = $conn->query($customers_sql);
        $totalCustomers = ($customers_result && $customers_row = $customers_result->fetch_assoc())
            ? $customers_row['total_customers'] : 0;

        // Count total products sold
        $products_sql = "SELECT SUM(quantity) as total_products FROM order_items
                         JOIN orders ON order_items.order_id = orders.id
                         WHERE orders.order_status = 'Completed'";
        $products_result = $conn->query($products_sql);
        $productsSold = ($products_result && $products_row = $products_result->fetch_assoc())
            ? $products_row['total_products'] : 0;

        // Get top selling products
        $topSellingProducts = [];
        $top_products_sql = "SELECT p.id, p.name, p.image, SUM(oi.quantity) as total_sold
                             FROM products p
                             JOIN order_items oi ON p.id = oi.product_id
                             JOIN orders o ON oi.order_id = o.id
                             WHERE o.order_status = 'Completed'
                             GROUP BY p.id
                             ORDER BY total_sold DESC
                             LIMIT 5";
        $top_products_result = $conn->query($top_products_sql);

        if ($top_products_result) {
            while ($product = $top_products_result->fetch_assoc()) {
                $topSellingProducts[] = $product;
            }
        }

        // Get recent orders
        $recentOrders = [];
        $recent_orders_sql = "SELECT o.id, o.created_at as order_date, o.total_amount, u.first_name, u.last_name
                              FROM orders o
                              JOIN users u ON o.user_id = u.id
                              WHERE o.order_status = 'Completed'
                              ORDER BY o.created_at DESC
                              LIMIT 5";
        $recent_orders_result = $conn->query($recent_orders_sql);

        if ($recent_orders_result) {
            while ($order = $recent_orders_result->fetch_assoc()) {
                $recentOrders[] = $order;
            }
        }

        // Create complete sales metrics array
        $salesMetrics = [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'average_order_value' => $averageOrderValue,
            'total_customers' => $totalCustomers,
            'products_sold' => $productsSold,
            'top_selling_products' => $topSellingProducts,
            'recent_orders' => $recentOrders
        ];

        // Update session with sales metrics
        $_SESSION['salesMetrics'] = $salesMetrics;

        // Store metrics in database for persistence
        $metrics_json = json_encode($salesMetrics);
        $current_date = date('Y-m-d');

        // Check if metrics for today already exist
        $check_metrics_sql = "SELECT id FROM sales_metrics WHERE date = ?";
        $check_metrics_stmt = $conn->prepare($check_metrics_sql);
        $check_metrics_stmt->bind_param("s", $current_date);
        $check_metrics_stmt->execute();
        $check_metrics_result = $check_metrics_stmt->get_result();

        if ($check_metrics_result->num_rows > 0) {
            // Update existing metrics
            $metrics_row = $check_metrics_result->fetch_assoc();
            $update_metrics_sql = "UPDATE sales_metrics SET metrics_data = ? WHERE id = ?";
            $update_metrics_stmt = $conn->prepare($update_metrics_sql);
            $update_metrics_stmt->bind_param("si", $metrics_json, $metrics_row['id']);
            $update_metrics_stmt->execute();
        } else {
            // Insert new metrics
            $insert_metrics_sql = "INSERT INTO sales_metrics (date, metrics_data) VALUES (?, ?)";
            $insert_metrics_stmt = $conn->prepare($insert_metrics_sql);
            $insert_metrics_stmt->bind_param("ss", $current_date, $metrics_json);
            $insert_metrics_stmt->execute();
        }

        return $salesMetrics;
    }
}

/**
 * Get sales report data based on filters
 *
 * @param mysqli $conn Database connection
 * @param string $filter Status filter
 * @param string $date_range Date range filter
 * @return array Sales data
 */
if (!function_exists('getSalesReport')) {
    function getSalesReport($conn, $filter = 'all', $date_range = '30days') {
        // Start with the base query
        $sql = "SELECT
                    o.id AS order_id,
                    o.total_amount,
                    o.order_status,
                    o.created_at AS order_date,
                    u.first_name,
                    u.last_name
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE 1=1"; // This allows us to conditionally add filters

        // Apply status filter
        if ($filter !== 'all') {
            $sql .= " AND o.order_status = ?";
        }

        // Apply date range filter
        $date_condition = '';
        switch ($date_range) {
            case '7days':
                $date_condition = " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $date_condition = " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case '90days':
                $date_condition = " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
                break;
            case 'year':
                $date_condition = " AND YEAR(o.created_at) = YEAR(CURRENT_DATE())";
                break;
            case 'all':
                // No date filter
                break;
        }

        $sql .= $date_condition;
        $sql .= " ORDER BY o.created_at DESC";

        try {
            $stmt = $conn->prepare($sql);

            // Bind parameters if we have filters
            if ($filter !== 'all') {
                $stmt->bind_param("s", $filter);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            $sales = [];
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $sales[] = $row;
                }
            }
            return $sales;
        } catch (Exception $e) {
            // Log the error and return an empty array
            error_log("Error fetching sales report: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Update order status and recalculate metrics if needed
 *
 * @param mysqli $conn Database connection
 * @param int $order_id Order ID to update
 * @param string $new_status New status for the order
 * @return bool Success or failure
 */
if (!function_exists('updateOrderStatus')) {
    function updateOrderStatus($conn, $order_id, $new_status) {
        $sql = "UPDATE orders SET order_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $order_id);

        if ($stmt->execute()) {
            // If the order status is updated to "Completed," recalculate sales metrics
            if ($new_status === 'Completed') {
                recalculateSalesMetrics($conn);
            }
            return true;
        } else {
            return false;
        }
    }
}
?>
