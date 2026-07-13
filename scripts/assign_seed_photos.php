<?php
/**
 * Генерирует фото для тестовых мероприятий и восстанавливает отсутствующие файлы user_photos.
 * Запуск: php scripts/assign_seed_photos.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

if (!extension_loaded('gd')) {
    fwrite(STDERR, "Требуется расширение PHP GD.\n");
    exit(1);
}

$db = Database::getInstance()->getConnection();
$projectRoot = dirname(__DIR__);
$photosDir = $projectRoot . '/' . rtrim(UPLOAD_DIR, '/') . '/photos/';

if (!is_dir($photosDir) && !mkdir($photosDir, 0775, true)) {
    fwrite(STDERR, "Не удалось создать {$photosDir}\n");
    exit(1);
}

function colorFromSeed(int $seed, int $offset = 0): int
{
    $r = 80 + (($seed * 37 + $offset * 53) % 120);
    $g = 60 + (($seed * 61 + $offset * 71) % 130);
    $b = 90 + (($seed * 43 + $offset * 89) % 110);
    return ($r << 16) | ($g << 8) | $b;
}

function drawGradient($img, int $w, int $h, int $c1, int $c2): void
{
    for ($y = 0; $y < $h; $y++) {
        $ratio = $h > 1 ? $y / ($h - 1) : 0;
        $r = (int)(((($c1 >> 16) & 0xFF) * (1 - $ratio)) + ((($c2 >> 16) & 0xFF) * $ratio));
        $g = (int)(((($c1 >> 8) & 0xFF) * (1 - $ratio)) + ((($c2 >> 8) & 0xFF) * $ratio));
        $b = (int)((($c1 & 0xFF) * (1 - $ratio)) + (($c2 & 0xFF) * $ratio));
        $lineColor = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $w, $y, $lineColor);
    }
}

function wrapText(string $text, int $maxChars = 28): array
{
    $words = preg_split('/\s+/u', trim($text)) ?: [];
    $lines = [];
    $line = '';
    foreach ($words as $word) {
        $candidate = $line === '' ? $word : $line . ' ' . $word;
        if (mb_strlen($candidate) > $maxChars && $line !== '') {
            $lines[] = $line;
            $line = $word;
        } else {
            $line = $candidate;
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return array_slice($lines, 0, 3);
}

function createEventBanner(string $path, string $title, int $seed): bool
{
    $w = 800;
    $h = 400;
    $img = imagecreatetruecolor($w, $h);
    if (!$img) {
        return false;
    }

    drawGradient($img, $w, $h, colorFromSeed($seed, 0), colorFromSeed($seed, 3));

    $white = imagecolorallocate($img, 255, 255, 255);
    $shadow = imagecolorallocatealpha($img, 0, 0, 0, 60);
    $accent = imagecolorallocate($img, 255, 230, 120);

    imagefilledrectangle($img, 0, $h - 8, $w, $h, $accent);

    $lines = wrapText($title);
    $y = 120;
    foreach ($lines as $line) {
        imagestring($img, 5, 42, $y + 2, $line, $shadow);
        imagestring($img, 5, 40, $y, $line, $white);
        $y += 28;
    }

    imagestring($img, 3, 40, $h - 36, 'Karaganda Event', $white);

    $ok = imagejpeg($img, $path, 88);
    imagedestroy($img);
    return $ok;
}

function createUserPhoto(string $path, int $seed): bool
{
    $w = 480;
    $h = 640;
    $img = imagecreatetruecolor($w, $h);
    if (!$img) {
        return false;
    }

    drawGradient($img, $w, $h, colorFromSeed($seed, 1), colorFromSeed($seed, 5));

    $white = imagecolorallocate($img, 255, 255, 255);
    $circle = imagecolorallocatealpha($img, 255, 255, 255, 30);
    imagefilledellipse($img, (int)($w / 2), (int)($h * 0.38), 220, 220, $circle);
    imagefilledellipse($img, (int)($w / 2), (int)($h * 0.38), 160, 160, $white);

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, ['png'], true)) {
        $ok = imagepng($img, $path, 8);
    } elseif (in_array($ext, ['webp'], true) && function_exists('imagewebp')) {
        $ok = imagewebp($img, $path, 85);
    } else {
        $ok = imagejpeg($img, $path, 88);
    }

    imagedestroy($img);
    return $ok;
}

// --- Мероприятия: баннер + запись в БД ---
$events = $db->query("SELECT id, title, photo FROM events ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$updateEvent = $db->prepare("UPDATE events SET photo = :photo WHERE id = :id");
$eventsDone = 0;

foreach ($events as $event) {
    $filename = !empty($event['photo']) ? $event['photo'] : ('seed_event_' . $event['id'] . '.jpg');
    $filepath = $photosDir . $filename;

    if (!file_exists($filepath)) {
        if (!createEventBanner($filepath, (string)$event['title'], (int)$event['id'])) {
            echo "[ошибка] баннер мероприятия #{$event['id']}\n";
            continue;
        }
    }

    if (empty($event['photo'])) {
        $updateEvent->execute([':photo' => $filename, ':id' => $event['id']]);
    }

    $eventsDone++;
    echo "[мероприятие] #{$event['id']} → {$filename}\n";
}

// --- Свидания: файлы user_photos для авторов ---
$dateUsers = $db->query("
    SELECT DISTINCT d.user_id,
           (SELECT photo FROM user_photos
            WHERE user_id = d.user_id AND photo IS NOT NULL AND TRIM(photo) <> ''
            ORDER BY created_at ASC LIMIT 1) AS photo
    FROM dates d
    WHERE d.date_time >= NOW()
")->fetchAll(PDO::FETCH_ASSOC);

$usersDone = 0;
foreach ($dateUsers as $row) {
    $photo = trim((string)($row['photo'] ?? ''));
    if ($photo === '') {
        echo "[свидание] user #{$row['user_id']} — нет фото в профиле, пропуск\n";
        continue;
    }

    $filepath = $photosDir . $photo;
    if (!file_exists($filepath)) {
        if (!createUserPhoto($filepath, (int)$row['user_id'])) {
            echo "[ошибка] фото user #{$row['user_id']}\n";
            continue;
        }
        echo "[свидание] user #{$row['user_id']} → создан {$photo}\n";
    } else {
        echo "[свидание] user #{$row['user_id']} → {$photo} уже есть\n";
    }
    $usersDone++;
}

echo "\nГотово: баннеров мероприятий — {$eventsDone}, фото для свиданий — {$usersDone}.\n";
echo "Папка: {$photosDir}\n";
