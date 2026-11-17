<?php
class OrderModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Eksekusi checkout dengan DB Transaction
    public function placeOrder($user_id, $cart_items, $total) {
        try {
            $this->db->beginTransaction();

            // 1. Insert ke tabel orders
            $this->db->query("INSERT INTO orders (user_id, total, status) VALUES (:user_id, :total, 'Menunggu Pembayaran')");
            $this->db->bind('user_id', $user_id);
            $this->db->bind('total', $total);
            $this->db->execute();
            
            $order_id = $this->db->lastInsertId();

            // 2. Loop cart_items untuk insert order_details dan kurangi stok
            foreach ($cart_items as $item) {
                $p_id = $item['product']['id'];
                $qty = $item['quantity'];
                $price = $item['product']['price'];
                $subtotal = $item['subtotal'];

                // Double check stock at exact moment
                $this->db->query("SELECT stock FROM products WHERE id = :id FOR UPDATE");
                $this->db->bind('id', $p_id);
                $prod = $this->db->single();
                
                if ($prod['stock'] < $qty) {
                    throw new Exception("Stok untuk produk ID {$p_id} tidak mencukupi (Sisa: {$prod['stock']}).");
                }

                // Insert details
                $this->db->query("INSERT INTO order_details (order_id, product_id, quantity, price, subtotal) VALUES (:oid, :pid, :qty, :price, :sub)");
                $this->db->bind('oid', $order_id);
                $this->db->bind('pid', $p_id);
                $this->db->bind('qty', $qty);
                $this->db->bind('price', $price);
                $this->db->bind('sub', $subtotal);
                $this->db->execute();

                // Kurangi stok
                $this->db->query("UPDATE products SET stock = stock - :qty WHERE id = :id");
                $this->db->bind('qty', $qty);
                $this->db->bind('id', $p_id);
                $this->db->execute();
            }

            $this->db->commit();
            return $order_id;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getOrdersByUserId($user_id, $keyword = '', $status = '') {
        $query = "SELECT * FROM orders WHERE user_id = :uid";
        $binds = ['uid' => $user_id];
        if (!empty($keyword)) {
            $query .= " AND id LIKE :keyword";
            $binds['keyword'] = "%$keyword%";
        }
        if (!empty($status)) {
            $query .= " AND status = :status";
            $binds['status'] = $status;
        }
        $query .= " ORDER BY created_at DESC";
        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    public function getOrderByIdAndUser($order_id, $user_id) {
        $this->db->query("SELECT * FROM orders WHERE id = :oid AND user_id = :uid");
        $this->db->bind('oid', $order_id);
        $this->db->bind('uid', $user_id);
        return $this->db->single();
    }

    public function getOrderDetails($order_id) {
        $this->db->query("SELECT od.*, p.name as product_name 
                          FROM order_details od 
                          LEFT JOIN products p ON od.product_id = p.id 
                          WHERE od.order_id = :oid");
        $this->db->bind('oid', $order_id);
        return $this->db->resultSet();
    }

    // -- ADMIN METHODS --
    public function getAllOrdersAdmin($keyword = '', $statusFilter = '', $limit = 10, $offset = 0) {
        $query = "SELECT o.*, u.name as customer_name 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE 1=1";
        
        if (!empty($keyword)) {
            $query .= " AND (u.name LIKE :keyword OR o.id LIKE :keyword)";
        }
        if (!empty($statusFilter)) {
            $query .= " AND o.status = :status";
        }
        
        $query .= " ORDER BY o.created_at DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        if (!empty($statusFilter)) {
            $this->db->bind('status', $statusFilter);
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalOrdersAdmin($keyword = '', $statusFilter = '') {
        $query = "SELECT COUNT(o.id) as total 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  WHERE 1=1";
                  
        if (!empty($keyword)) {
            $query .= " AND (u.name LIKE :keyword OR o.id LIKE :keyword)";
        }
        if (!empty($statusFilter)) {
            $query .= " AND o.status = :status";
        }

        $this->db->query($query);

        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        if (!empty($statusFilter)) {
            $this->db->bind('status', $statusFilter);
        }

        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getOrderByIdAdmin($id) {
        $this->db->query("SELECT o.*, u.name as customer_name, u.email as customer_email 
                          FROM orders o 
                          LEFT JOIN users u ON o.user_id = u.id 
                          WHERE o.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function updateOrderStatus($id, $status) {
        $this->db->query("UPDATE orders SET status = :status WHERE id = :id");
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function countOrdersByStatuses(array $statuses) {
        if (empty($statuses)) return 0;
        
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $query = "SELECT COUNT(id) as total FROM orders WHERE status IN ($placeholders)";
        
        $this->db->query($query);
        
        foreach ($statuses as $index => $status) {
            $this->db->bind($index + 1, $status);
        }
        
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function deleteOrder($id) {
        $this->db->query("DELETE FROM orders WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
