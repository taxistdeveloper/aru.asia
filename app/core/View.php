<?php
/**
 * КЛАСС ДЛЯ РАБОТЫ С ПРЕДСТАВЛЕНИЯМИ (VIEW)
 * 
 * Этот класс загружает и отображает HTML шаблоны.
 * Используем его для вывода страниц пользователю.
 */

class View {
    /**
     * Загружает и отображает представление
     * 
     * @param string $viewName Имя файла представления (без .php)
     * @param array $data Данные для передачи в представление
     */
    public static function render($viewName, $data = []) {
        // Извлекаем переменные из массива $data
        // Теперь в представлении можно использовать $user, $events и т.д.
        extract($data);
        
        // Начинаем буферизацию вывода
        ob_start();
        
        // Подключаем файл представления
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Представление {$viewName} не найдено");
        }
        
        // Получаем содержимое буфера и очищаем его
        $content = ob_get_clean();
        
        // Выводим содержимое
        echo $content;
    }
    
    /**
     * Проверяет является ли запрос мобильным устройством
     */
    public static function isMobile() {
        // Проверяем User-Agent браузера
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
    }
}

