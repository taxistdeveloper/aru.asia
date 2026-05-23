<?php
/**
 * МОДЕЛЬ КАТЕГОРИЙ СВИДАНИЙ
 * 
 * Работает с таблицей date_categories - хранит категории для объявлений о свиданиях
 */

class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Получает все активные категории
     */
    public function getAllActive() {
        $sql = "SELECT * FROM date_categories WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Получает все категории (включая неактивные)
     */
    public function getAll() {
        $sql = "SELECT * FROM date_categories ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Получает категорию по ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM date_categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Создает новую категорию
     */
    public function create($data) {
        $sql = "INSERT INTO date_categories (name, description, is_active) 
                VALUES (:name, :description, :is_active)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Обновляет категорию
     */
    public function update($id, $data) {
        $sql = "UPDATE date_categories 
                SET name = :name, description = :description, is_active = :is_active 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null,
            ':is_active' => $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Удаляет категорию
     */
    public function delete($id) {
        $sql = "DELETE FROM date_categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}

