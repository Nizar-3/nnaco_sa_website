<?php
class ReportModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // 1. Dashboard Stats
    public function getDashboardStats() {
        $stats = [
            'total_revenue' => 0,
            'total_orders' => 0,
            'total_customers' => 0,
            'total_products' => 0,
            'today_sales' => 0,
            'month_sales' => 0
        ];

        // Total Revenue (Only completed orders)
        $this->db->query("SELECT SUM(total) as rev FROM orders WHERE status = 'Selesai'");
        $res = $this->db->single();
        $stats['total_revenue'] = $res['rev'] ?? 0;

        // Total Orders (Completed orders)
        $this->db->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Selesai'");
        $res = $this->db->single();
        $stats['total_orders'] = $res['count'] ?? 0;

        // Total Customers
        $this->db->query("SELECT COUNT(id) as count FROM users WHERE role = 'Customer'");
        $res = $this->db->single();
        $stats['total_customers'] = $res['count'] ?? 0;

        // Total Products
        $this->db->query("SELECT COUNT(id) as count FROM products WHERE status = 'Available'");
        $res = $this->db->single();
        $stats['total_products'] = $res['count'] ?? 0;

        // Today Sales (Revenue from completed orders today)
        // Using date functions compatible with typical MySQL setups
        $this->db->query("SELECT SUM(total) as rev FROM orders WHERE status = 'Selesai' AND DATE(created_at) = CURDATE()");
        $res = $this->db->single();
        $stats['today_sales'] = $res['rev'] ?? 0;

        // This Month Sales
        $this->db->query("SELECT SUM(total) as rev FROM orders WHERE status = 'Selesai' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        $res = $this->db->single();
        $stats['month_sales'] = $res['rev'] ?? 0;

        return $stats;
    }

    // 2. Top Selling Products
    public function getTopSellingProducts($limit = 5, $startDate = null, $endDate = null) {
        $query = "SELECT p.id, p.name, p.image, p.price, p.stock, p.status, SUM(od.quantity) as total_sold, SUM(od.subtotal) as total_revenue
                  FROM order_details od
                  JOIN orders o ON od.order_id = o.id
                  JOIN products p ON od.product_id = p.id
                  WHERE o.status = 'Selesai'";
        
        if ($startDate && $endDate) {
            $query .= " AND DATE(o.created_at) >= :start AND DATE(o.created_at) <= :end";
        }
        
        $query .= " GROUP BY p.id ORDER BY total_sold DESC LIMIT :limit";

        $this->db->query($query);

        if ($startDate && $endDate) {
            $this->db->bind('start', $startDate);
            $this->db->bind('end', $endDate);
        }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    // 3. Low Stock Products (Reuse from ProductModel logic, but let's make it simple here)
    public function getLowStockProducts($threshold = 5) {
        $this->db->query("SELECT id, name, price, stock, status, image FROM products WHERE stock <= :threshold ORDER BY stock ASC");
        $this->db->bind('threshold', $threshold);
        return $this->db->resultSet();
    }

    // 4. Sales Chart Data
    // Grouping by Date
    public function getSalesChartDataDaily($startDate, $endDate) {
        $query = "SELECT DATE(created_at) as sales_date, SUM(total) as revenue, COUNT(id) as total_transactions
                  FROM orders 
                  WHERE status = 'Selesai' 
                  AND DATE(created_at) >= :start 
                  AND DATE(created_at) <= :end
                  GROUP BY DATE(created_at)
                  ORDER BY DATE(created_at) ASC";
        
        $this->db->query($query);
        $this->db->bind('start', $startDate);
        $this->db->bind('end', $endDate);
        return $this->db->resultSet();
    }

    // Report Summary for a specific period
    public function getReportSummary($startDate, $endDate) {
        $query = "SELECT SUM(total) as revenue, COUNT(id) as total_transactions
                  FROM orders 
                  WHERE status = 'Selesai' 
                  AND DATE(created_at) >= :start 
                  AND DATE(created_at) <= :end";
        
        $this->db->query($query);
        $this->db->bind('start', $startDate);
        $this->db->bind('end', $endDate);
        $res = $this->db->single();

        return [
            'revenue' => $res['revenue'] ?? 0,
            'transactions' => $res['total_transactions'] ?? 0
        ];
    }

    // 5. Last Month Sales (For Comparison)
    public function getLastMonthSales() {
        // Last month's revenue and transactions
        $this->db->query("SELECT SUM(total) as rev, COUNT(id) as transactions 
                          FROM orders 
                          WHERE status = 'Selesai' 
                          AND MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
                          AND YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
        $res = $this->db->single();
        return [
            'revenue' => $res['rev'] ?? 0,
            'transactions' => $res['transactions'] ?? 0
        ];
    }

    // 6. Complete Business Context for AI
    public function getBusinessContextForAI() {
        return [
            'current_date' => date('Y-m-d H:i:s'),
            'general_stats' => $this->getDashboardStats(),
            'last_month_sales' => $this->getLastMonthSales(),
            'top_selling_products' => $this->getTopSellingProducts(5),
            'low_stock_products' => $this->getLowStockProducts(10)
        ];
    }
}
