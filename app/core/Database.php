<?php
/**
 * КЛАСС ДЛЯ РАБОТЫ С БАЗОЙ ДАННЫХ
 *
 * Этот класс создает подключение к MySQL базе данных
 * и предоставляет методы для выполнения SQL запросов.
 * Используем паттерн Singleton - только одно подключение на всё приложение.
 */

class Database {
    private static $instance = null; // Единственный экземпляр класса
    private $connection;              // Подключение к БД

    /**
     * Приватный конструктор - нельзя создать объект напрямую
     */
    private function __construct() {
        try {
            // Создаем подключение к MySQL используя PDO
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Выбрасывать исключения при ошибках
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Возвращать ассоциативные массивы
                PDO::ATTR_EMULATE_PREPARES => false, // Использовать настоящие prepared statements
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Пробрасываем исключение — index.php покажет страницу «Технические работы»
            throw $e;
        }
    }

    /**
     * Получить единственный экземпляр класса (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Получить объект подключения PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Предотвращаем клонирование объекта
     */
    private function __clone() {}

    /**
     * Предотвращаем unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

