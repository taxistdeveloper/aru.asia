<?php
/**
 * АДМИН-ПАНЕЛЬ - МОДЕРАЦИЯ МЕРОПРИЯТИЙ
 */

ob_start();
?>

<div class="mt-4">
    <h2 class="mb-4">Модерация мероприятий</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (empty($pendingEvents)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Нет мероприятий ожидающих модерации
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($pendingEvents as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><?= Helper::escape($event['title']) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Описание:</strong><br>
                                <?= Helper::escape($event['description']) ?>
                            </p>
                            
                            <div class="mb-2">
                                <strong><i class="bi bi-calendar"></i> Дата проведения:</strong><br>
                                <?= date('d.m.Y H:i', strtotime($event['event_date'])) ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong><i class="bi bi-geo-alt"></i> Место:</strong><br>
                                <?= Helper::escape($event['location']) ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong><i class="bi bi-currency-exchange"></i> Цена:</strong><br>
                                <?= number_format($event['price'], 0) ?> ₸
                            </div>
                            
                            <div class="mb-2">
                                <strong><i class="bi bi-person"></i> Автор:</strong><br>
                                <?= Helper::escape($event['user_email']) ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong><i class="bi bi-clock"></i> Создано:</strong><br>
                                <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form method="POST" action="<?= BASE_URL ?>admin/events/approve" class="d-inline mb-2">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-check-circle"></i> Одобрить
                                </button>
                            </form>
                            
                            <button type="button" class="btn btn-danger w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#rejectModal<?= $event['id'] ?>">
                                <i class="bi bi-x-circle"></i> Отклонить
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Modal для отклонения -->
                <div class="modal fade" id="rejectModal<?= $event['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Отклонить мероприятие</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>admin/events/reject">
                                <div class="modal-body">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <div class="mb-3">
                                        <label for="rejection_reason<?= $event['id'] ?>" class="form-label">
                                            Причина отклонения *
                                        </label>
                                        <textarea class="form-control" 
                                                  id="rejection_reason<?= $event['id'] ?>" 
                                                  name="rejection_reason" 
                                                  rows="4" 
                                                  required 
                                                  placeholder="Укажите причину отклонения и что нужно исправить..."></textarea>
                                        <small class="form-text text-muted">
                                            Укажите конкретные ошибки или что нужно исправить в мероприятии
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                    <button type="submit" class="btn btn-danger">Отклонить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Модерация мероприятий - Админ-панель';
include __DIR__ . '/../admin_layout.php';
?>

