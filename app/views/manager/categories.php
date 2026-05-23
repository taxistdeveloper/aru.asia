<?php
/**
 * УПРАВЛЕНИЕ КАТЕГОРИЯМИ СВИДАНИЙ
 */

ob_start();
?>

<div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags"></i> Управление категориями свиданий</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle"></i> Добавить категорию
        </button>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?= Helper::escape($success) ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-3">Категории еще не добавлены</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><strong><?= Helper::escape($category['name']) ?></strong></td>
                                    <td><?= Helper::escape($category['description'] ?? '—') ?></td>
                                    <td>
                                        <?php if ($category['is_active']): ?>
                                            <span class="badge bg-success">Активна</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Неактивна</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($category['created_at'])) ?></td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editCategoryModal<?= $category['id'] ?>">
                                            <i class="bi bi-pencil"></i> Редактировать
                                        </button>
                                        <a href="<?= BASE_URL ?>manager/categories/delete?id=<?= $category['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту категорию?')">
                                            <i class="bi bi-trash"></i> Удалить
                                        </a>
                                    </td>
                                </tr>
                                
                                <!-- Модальное окно редактирования -->
                                <div class="modal fade" id="editCategoryModal<?= $category['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Редактировать категорию</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="<?= BASE_URL ?>manager/categories/update?id=<?= $category['id'] ?>">
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="edit_name_<?= $category['id'] ?>" class="form-label">Название *</label>
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="edit_name_<?= $category['id'] ?>" 
                                                               name="name" 
                                                               value="<?= Helper::escape($category['name']) ?>" 
                                                               required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="edit_description_<?= $category['id'] ?>" class="form-label">Описание</label>
                                                        <textarea class="form-control" 
                                                                  id="edit_description_<?= $category['id'] ?>" 
                                                                  name="description" 
                                                                  rows="3"><?= Helper::escape($category['description'] ?? '') ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   id="edit_is_active_<?= $category['id'] ?>" 
                                                                   name="is_active" 
                                                                   <?= $category['is_active'] ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="edit_is_active_<?= $category['id'] ?>">
                                                                Активна
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                    <button type="submit" class="btn btn-primary">Сохранить</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно добавления -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить категорию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>manager/categories/store">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Название *</label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               required 
                               placeholder="Например: Прогулка">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="3" 
                                  placeholder="Краткое описание категории"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   checked>
                            <label class="form-check-label" for="is_active">
                                Активна
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Управление категориями';
include __DIR__ . '/../admin_layout.php';
?>

