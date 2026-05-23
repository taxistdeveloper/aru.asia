<?php
/**
 * АВТОЗАГРУЗЧИК КЛАССОВ
 * 
 * Эта функция автоматически загружает классы когда они нужны.
 * Вместо того чтобы писать require для каждого класса,
 * PHP сам найдет и загрузит нужный файл.
 */

spl_autoload_register(function ($class) {
    // Преобразуем имя класса в путь к файлу
    // Например: Router -> app/core/Router.php
    // Например: User -> app/models/User.php
    // Например: HomeController -> app/controllers/HomeController.php
    
    // Убираем префикс App\ если есть
    $class = str_replace('App\\', '', $class);
    
    // Заменяем обратные слеши на прямые (для Windows)
    $class = str_replace('\\', '/', $class);
    
    // Определяем где искать класс
    $basePath = __DIR__ . '/../';
    
    // Список папок для поиска классов
    $directories = [
        'core',
        'models',
        'controllers'
    ];
    
    // Ищем класс в каждой папке
    foreach ($directories as $dir) {
        $file = $basePath . $dir . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

