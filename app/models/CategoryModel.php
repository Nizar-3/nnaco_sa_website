<?php
class CategoryModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllCategories() {
        $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function searchCategories($keyword = '') {
        if (empty(trim($keyword))) {
            return $this->getAllCategories();
        }
        $this->db->query('SELECT * FROM categories WHERE name LIKE :keyword OR description LIKE :keyword ORDER BY name ASC');
        $this->db->bind('keyword', "%$keyword%");
        return $this->db->resultSet();
    }

    public function getCategoryById($id) {
        $this->db->query('SELECT * FROM categories WHERE id = :id');
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function insertCategory($data) {
        $this->db->query("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateCategory($id, $data) {
        $this->db->query("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        $this->db->bind('name', $data['name']);
        $this->db->bind('description', $data['description']);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteCategory($id) {
        $this->db->query("DELETE FROM categories WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
