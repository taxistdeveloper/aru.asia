<?php

/**
 * ПРОСМОТР ПРОФИЛЯ ДРУГОГО ПОЛЬЗОВАТЕЛЯ
 */

ob_start();
?>

<div class="mobile-page-container">
    <?php if (isset($isAdmin) && $isAdmin && isset($isProfileBlocked) && $isProfileBlocked): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> Профиль заблокирован
            </h5>
            <?php if (!empty($adminRemark)): ?>
                <?php
                $fieldNames = [
                    'full_name' => 'ФИО (Полное имя)',
                    'about' => 'О себе',
                    'photo' => 'Фотография'
                ];
                $fieldName = isset($remarkType) && isset($fieldNames[$remarkType]) ? $fieldNames[$remarkType] : null;
                ?>
                <?php if ($fieldName): ?>
                    <p class="mb-2"><strong>Замечание по полю: <span class="text-danger"><?= Helper::escape($fieldName) ?></span></strong></p>
                <?php else: ?>
                    <p class="mb-2"><strong>Замечание:</strong></p>
                <?php endif; ?>
                <p class="mb-3"><?= nl2br(Helper::escape($adminRemark)) ?></p>
            <?php endif; ?>
            <hr>
            <div class="mb-0">
                <a href="<?= BASE_URL ?>admin/users" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-left"></i> Вернуться к управлению пользователями
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-4">
        <h2 class="mb-3">Профиль пользователя</h2>
        <div class="d-flex flex-column flex-md-row gap-2">
            <?php if (isset($isAdmin) && $isAdmin): ?>
                <a href="<?= BASE_URL ?>admin/users" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к пользователям
                </a>
            <?php elseif ($currentUserId): ?>
                <a href="<?= BASE_URL ?>messages?user_id=<?= $user['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-envelope"></i> Написать сообщение
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>auth/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Войти для общения
                </a>
            <?php endif; ?>
            <?php if (!isset($isAdmin) || !$isAdmin): ?>
                <button type="button" onclick="history.back()" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Назад
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Фотографии -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Фотографии (<?= count($photos) ?>)</h5>
            <?php if (empty($photos)): ?>
                <p class="text-muted">У пользователя пока нет фотографий</p>
            <?php else: ?>
                <style>
                    .photo-gallery {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                        gap: 15px;
                        margin-top: 20px;
                    }
                    
                    @media (min-width: 576px) {
                        .photo-gallery {
                            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                        }
                    }
                    
                    @media (min-width: 768px) {
                        .photo-gallery {
                            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                            gap: 20px;
                        }
                    }
                    
                    .photo-item {
                        position: relative;
                        overflow: hidden;
                        border-radius: 12px;
                        aspect-ratio: 1;
                        cursor: pointer;
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                        background: #f8f9fa;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                    }
                    
                    .photo-item:hover {
                        transform: translateY(-5px) scale(1.02);
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                    }
                    
                    .photo-item img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        transition: transform 0.3s ease;
                    }
                    
                    .photo-item:hover img {
                        transform: scale(1.1);
                    }
                    
                    .photo-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(0, 0, 0, 0);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.3s ease;
                        border-radius: 12px;
                    }
                    
                    .photo-item:hover .photo-overlay {
                        background: rgba(0, 0, 0, 0.4);
                    }
                    
                    .photo-overlay-icon {
                        color: white;
                        font-size: 2.5rem;
                        opacity: 0;
                        transform: scale(0.8);
                        transition: opacity 0.3s ease, transform 0.3s ease;
                    }
                    
                    .photo-item:hover .photo-overlay-icon {
                        opacity: 1;
                        transform: scale(1);
                    }
                    
                    .photo-loading {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        height: 100%;
                        background: #f0f0f0;
                        color: #999;
                    }
                </style>
                <div class="photo-gallery">
                    <?php foreach ($photos as $index => $photo): ?>
                        <div class="photo-item" onclick="openPhotoModal(<?= $index ?>)">
                            <img 
                                src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $photo['photo'] ?>"
                                alt="Фото <?= $index + 1 ?>"
                                loading="lazy"
                                onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\'%3E%3Crect fill=\'%23ddd\' width=\'200\' height=\'200\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'14\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3EОшибка загрузки%3C/text%3E%3C/svg%3E';">
                            <div class="photo-overlay">
                                <i class="bi bi-zoom-in photo-overlay-icon"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Информация о пользователе -->
    <div class="card mb-4">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <?php if (!empty($user['full_name'])): ?>
                        <p><strong>ФИО:</strong> <?= Helper::escape($user['full_name']) ?></p>
                    <?php endif; ?>
                    <p><strong>Пол:</strong> <?= $user['gender'] === 'male' ? 'Мужской' : ($user['gender'] === 'female' ? 'Женский' : 'Не указан') ?></p>
                    <p><strong>Возраст:</strong> <?= $user['age'] ?? 'Не указан' ?></p>
                    <?php
                    $maritalStatusText = 'Не указан';
                    if (!empty($user['marital_status'])) {
                        $isMale = ($user['gender'] ?? '') === 'male';
                        $maritalStatusMap = [
                            'single' => $isMale ? 'Холост' : 'Не замужем',
                            'married' => $isMale ? 'Женат' : 'Замужем',
                            'divorced' => $isMale ? 'Разведен' : 'Разведена',
                            'widowed' => $isMale ? 'Вдовец' : 'Вдова'
                        ];
                        $maritalStatusText = $maritalStatusMap[$user['marital_status']] ?? 'Не указан';
                    }
                    ?>
                    <p><strong>Семейный статус:</strong> <?= $maritalStatusText ?></p>
                    <p><strong>Страна:</strong> <?= Helper::escape($user['country'] ?? 'Не указана') ?></p>
                    <?php
                    $city = $user['city'] ?? 'Не указан';
                    if ($city !== 'Не указан') {
                        // Универсальная обработка названий типа "Название городская администрация"
                        if (preg_match('/^(.+?)(?:ская|ая)\s+городская\s+администрация$/iu', $city, $matches)) {
                            $cityName = trim($matches[1]);

                            // Специальные случаи для известных городов
                            $specialCases = [
                                'Карагандин' => 'Караганда',
                                'Алматин' => 'Алматы',
                                'Астанин' => 'Астана',
                                'Шымкент' => 'Шымкент',
                            ];

                            // Проверяем специальные случаи
                            if (isset($specialCases[$cityName])) {
                                $city = $specialCases[$cityName];
                            } else {
                                // Универсальное преобразование: убираем "ин" в конце, если есть
                                $city = preg_replace('/ин$/', '', $cityName);
                                // Если после преобразования получилась пустая строка, оставляем оригинал
                                if (empty($city)) {
                                    $city = $user['city'];
                                }
                            }
                        }
                    }
                    ?>
                    <p><strong>Город:</strong> <?= Helper::escape($city) ?></p>
                </div>
                <?php if (!empty($user['about'])): ?>
                    <div class="col-md-6">
                        <p><strong>О себе:</strong></p>
                        <p><?= nl2br(Helper::escape($user['about'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Объявления пользователя -->
    
</div>

<!-- Модальное окно для просмотра фотографии -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="photoCounter">Фотография 1 из <?= count($photos) ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center position-relative" style="min-height: 400px;">
                <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2"
                    id="prevPhoto"
                    style="z-index: 10; border-radius: 50%; width: 50px; height: 50px; padding: 0;"
                    onclick="changePhoto(-1)">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <img id="modalPhoto" src="" class="img-fluid" alt="Фото" style="max-height: 70vh;">
                <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2"
                    id="nextPhoto"
                    style="z-index: 10; border-radius: 50%; width: 50px; height: 50px; padding: 0;"
                    onclick="changePhoto(1)">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Массив всех фотографий
    const photos = [
        <?php foreach ($photos as $photo): ?> '<?= BASE_URL . UPLOAD_DIR . 'photos/' . $photo['photo'] ?>',
        <?php endforeach; ?>
    ];

    let currentPhotoIndex = 0;

    function openPhotoModal(index) {
        currentPhotoIndex = index;
        updatePhotoDisplay();
        var modal = new bootstrap.Modal(document.getElementById('photoModal'));
        modal.show();
    }

    function changePhoto(direction) {
        currentPhotoIndex += direction;

        // Циклическая навигация
        if (currentPhotoIndex < 0) {
            currentPhotoIndex = photos.length - 1;
        } else if (currentPhotoIndex >= photos.length) {
            currentPhotoIndex = 0;
        }

        updatePhotoDisplay();
    }

    function updatePhotoDisplay() {
        const modalPhoto = document.getElementById('modalPhoto');
        const photoCounter = document.getElementById('photoCounter');
        const prevBtn = document.getElementById('prevPhoto');
        const nextBtn = document.getElementById('nextPhoto');

        if (modalPhoto && photos.length > 0) {
            modalPhoto.src = photos[currentPhotoIndex];
        }

        if (photoCounter) {
            photoCounter.textContent = 'Фотография ' + (currentPhotoIndex + 1) + ' из ' + photos.length;
        }

        // Показываем/скрываем кнопки навигации если фотография одна
        if (prevBtn && nextBtn) {
            if (photos.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'block';
                nextBtn.style.display = 'block';
            }
        }
    }

    // Обработка клавиатуры для навигации
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('photoModal');
        if (modal && modal.classList.contains('show')) {
            if (e.key === 'ArrowLeft') {
                changePhoto(-1);
            } else if (e.key === 'ArrowRight') {
                changePhoto(1);
            } else if (e.key === 'Escape') {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        }
    });

    function toggleDescription(id) {
        var shortText = document.getElementById(id + '-short');
        var fullText = document.getElementById(id + '-full');
        var link = document.getElementById(id + '-link');

        if (shortText.classList.contains('d-none')) {
            // Сворачиваем
            shortText.classList.remove('d-none');
            fullText.classList.add('d-none');
            link.textContent = 'подробнее';
        } else {
            // Разворачиваем
            shortText.classList.add('d-none');
            fullText.classList.remove('d-none');
            link.textContent = 'свернуть';
        }
    }

    function updateCountdown() {
        const timers = document.querySelectorAll('.countdown-timer');

        timers.forEach(function(timer) {
            const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
            const now = new Date().getTime();
            const distance = deadline - now;

            if (distance < 0) {
                timer.innerHTML = '<span class="badge bg-danger">Истекло</span>';
                // Автоматически перезагружаем страницу через 2 секунды после истечения
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) {
                timeString += days + ' дн. ';
            }
            if (hours > 0 || days > 0) {
                timeString += hours + ' ч. ';
            }
            if (minutes > 0 || hours > 0 || days > 0) {
                timeString += minutes + ' мин. ';
            }
            timeString += seconds + ' сек.';

            let badgeClass = 'bg-success';
            if (distance < 3600000) { // Меньше часа
                badgeClass = 'bg-danger';
            } else if (distance < 86400000) { // Меньше суток
                badgeClass = 'bg-warning';
            }

            timer.innerHTML = '<span class="badge ' + badgeClass + '">' + timeString + '</span>';
        });
    }

    // Обновляем счетчик каждую секунду
    updateCountdown();
    setInterval(updateCountdown, 1000);
</script>

<?php
$content = ob_get_clean();
$title = 'Профиль пользователя';
include __DIR__ . '/../layout.php';
?>
