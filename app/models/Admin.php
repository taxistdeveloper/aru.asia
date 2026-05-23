<?php
/**
 * МОДЕЛЬ АДМИНИСТРАТОРА
 * 
 * Этот класс работает с таблицей admins в базе данных.
 * Методы для авторизации администраторов.
 */

class Admin {
    private $db; // Подключение к базе данных
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Находит администратора по email
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM admins WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Находит администратора по ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM admins WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Проверяет пароль администратора
     */
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Обновляет пароль администратора
     */
    public function updatePassword($adminId, $newHashedPassword) {
        $sql = "UPDATE admins SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $newHashedPassword,
            ':id' => $adminId
        ]);
    }
}

