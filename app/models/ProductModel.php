<?php
class ProductModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // -- CUSTOMER METHODS --
    public function getFilteredProducts($keyword = '', $category_id = '', $min_price = '', $max_price = '', $limit = 9, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.status = 'Available'";
        
        $binds = [];
        if (!empty($keyword)) { $query .= " AND p.name LIKE :keyword"; $binds['keyword'] = "%$keyword%"; }
        if (!empty($category_id)) { $query .= " AND p.category_id = :category_id"; $binds['category_id'] = $category_id; }
        if (!empty($min_price) && is_numeric($min_price)) { $query .= " AND p.price >= :min_price"; $binds['min_price'] = $min_price; }
        if (!empty($max_price) && is_numeric($max_price)) { $query .= " AND p.price <= :max_price"; $binds['max_price'] = $max_price; }

        $query .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $this->db->query($query);
        foreach ($binds as $key => $value) { $this->db->bind($key, $value); }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalFilteredProducts($keyword = '', $category_id = '', $min_price = '', $max_price = '') {
        $query = "SELECT COUNT(id) as total FROM products WHERE status = 'Available'";
        $binds = [];
        if (!empty($keyword)) { $query .= " AND name LIKE :keyword"; $binds['keyword'] = "%$keyword%"; }
        if (!empty($category_id)) { $query .= " AND category_id = :category_id"; $binds['category_id'] = $category_id; }
        if (!empty($min_price) && is_numeric($min_price)) { $query .= " AND price >= :min_price"; $binds['min_price'] = $min_price; }
        if (!empty($max_price) && is_numeric($max_price)) { $query .= " AND price <= :max_price"; $binds['max_price'] = $max_price; }
        $this->db->query($query);
        foreach ($binds as $key => $value) { $this->db->bind($key, $value); }
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getProductById($id) {
        $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id AND p.status = 'Available'");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    // -- ADMIN & OPERATOR METHODS --
    public function getAllProductsAdmin($keyword = '', $category_id = '', $status = '', $limit = 10, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $binds = [];
        if (!empty($keyword)) {
            $query .= " AND p.name LIKE :keyword";
            $binds['keyword'] = "%$keyword%";
        }
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $binds['category_id'] = $category_id;
        }
        if (!empty($status)) {
            if ($status === 'low_stock') {
                $query .= " AND p.stock <= 5 AND p.stock > 0";
            } elseif ($status === 'out_of_stock') {
                $query .= " AND p.stock = 0";
            } else {
                $query .= " AND p.status = :status";
                $binds['status'] = $status;
            }
        }
        $query .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalProductsAdmin($keyword = '', $category_id = '', $status = '') {
        $query = "SELECT COUNT(p.id) as total FROM products p WHERE 1=1";
        $binds = [];
        if (!empty($keyword)) {
            $query .= " AND p.name LIKE :keyword";
            $binds['keyword'] = "%$keyword%";
        }
        if (!empty($category_id)) {
            $query .= " AND p.category_id = :category_id";
            $binds['category_id'] = $category_id;
        }
        if (!empty($status)) {
            if ($status === 'low_stock') {
                $query .= " AND p.stock <= 5 AND p.stock > 0";
            } elseif ($status === 'out_of_stock') {
                $query .= " AND p.stock = 0";
            } else {
                $query .= " AND p.status = :status";
                $binds['status'] = $status;
            }
        }
        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getProductByIdAdmin($id) {
        $this->db->query("SELECT * FROM products WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function insertProduct($data) {
        $query = "INSERT INTO products (category_id, name, description, price, stock, image, status) VALUES (:category_id, :name, :description, :price, :stock, :image, :status)";
        $this->db->query($query);
        $this->db->bind('category_id', empty($data['category_id']) ? null : $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('image', $data['image']);
        $this->db->bind('status', $data['status']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateProduct($id, $data) {
        $query = "UPDATE products SET category_id = :category_id, name = :name, description = :description, price = :price, stock = :stock, status = :status";
        if (isset($data['image'])) { $query .= ", image = :image"; }
        $query .= " WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('category_id', empty($data['category_id']) ? null : $data['category_id']);
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('price', $data['price']);
        $this->db->bind('stock', $data['stock']);
        $this->db->bind('status', $data['status']);
        $this->db->bind('id', $id);
        if (isset($data['image'])) { $this->db->bind('image', $data['image']); }
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteProduct($id) {
        $this->db->query("DELETE FROM products WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // -- OPERATOR METHODS --
    public function getLowStockProductsAdmin($threshold = 5, $keyword = '', $limit = 10, $offset = 0) {
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= :threshold";
        if (!empty($keyword)) { $query .= " AND p.name LIKE :keyword"; }
        $query .= " ORDER BY p.stock ASC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        $this->db->bind('threshold', $threshold);
        if (!empty($keyword)) { $this->db->bind('keyword', "%$keyword%"); }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function countLowStockProducts($threshold = 5, $keyword = '') {
        $query = "SELECT COUNT(id) as total FROM products WHERE stock <= :threshold";
        if (!empty($keyword)) { $query .= " AND name LIKE :keyword"; }
        $this->db->query($query);
        $this->db->bind('threshold', $threshold);
        if (!empty($keyword)) { $this->db->bind('keyword', "%$keyword%"); }
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function updateProductStockStatus($id, $stock, $status) {
        $this->db->query("UPDATE products SET stock = :stock, status = :status WHERE id = :id");
        $this->db->bind('stock', $stock);
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    // -- AI ASSISTANT METHODS --
    public function getCatalogForAI() {
        // Limit the data sent to AI (only necessary fields of available products)
        $this->db->query("SELECT id, name, price, description FROM products WHERE status = 'Available'");
        return $this->db->resultSet();
    }

    public function getProductsByIds(array $ids) {
        if (empty($ids)) return [];
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT id, name, price, image FROM products WHERE status = 'Available' AND id IN ($placeholders)";
        $this->db->query($query);
        
        foreach ($ids as $index => $id) {
            $this->db->bind($index + 1, $id);
        }
        
        return $this->db->resultSet();
    }
}
