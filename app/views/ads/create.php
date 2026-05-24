<?php

/**
 * ФОРМА РАЗМЕЩЕНИЯ РЕКЛАМЫ
 * Для рекламодателей
 */

ob_start();
?>

<!-- Cropper.js для обрезки изображений -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<style>
    .ad-create-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .ad-create-form .card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .ad-create-form .form-label {
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .ad-create-form .form-control,
    .ad-create-form .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 12px 16px;
    }

    .ad-create-form .form-control:focus,
    .ad-create-form .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .ad-create-form .btn-primary {
        background: #667eea;
        border: none;
        padding: 14px 32px;
        font-weight: 600;
        border-radius: 8px;
    }

    .ad-create-form .btn-primary:hover {
        background: #5568d3;
    }

    .tariff-box {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 55%, #eef2ff 100%);
        border: 1px solid #c7d2fe;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 22px;
        box-shadow: 0 10px 24px rgba(79, 70, 229, 0.12);
    }

    .tariff-box h6 {
        margin-bottom: 14px;
        color: #1f2937;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tariff-box .form-label {
        color: #312e81;
        font-weight: 700;
    }

    .tariff-box .form-select {
        border: 1px solid #a5b4fc;
        background-color: #fff;
    }

    .tariff-box .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.14);
    }

    .tariff-summary {
        margin-top: 14px;
        padding: 14px 16px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px dashed #818cf8;
        color: #1f2937;
        font-size: 14px;
        line-height: 1.5;
    }

    .tariff-summary strong {
        color: #4338ca;
        font-size: 18px;
        font-weight: 800;
    }

    .price-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 6px;
    }

    .price-card {
        border: 1px solid #c7d2fe;
        border-radius: 12px;
        background: #ffffff;
        padding: 14px 12px 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .price-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(79, 70, 229, 0.15);
        border-color: #818cf8;
    }

    .price-card.active {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        background: linear-gradient(135deg, #ffffff 0%, #eef2ff 100%);
    }

    .price-card.recommended {
        border-color: #22c55e;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
        background: linear-gradient(160deg, #ffffff 0%, #ecfdf5 100%);
    }

    .price-card.active::after {
        content: "Выбрано";
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 10px;
        color: #4338ca;
        background: #e0e7ff;
        border-radius: 999px;
        padding: 2px 8px;
        font-weight: 700;
    }

    .price-card-recommend {
        position: absolute;
        top: -9px;
        left: 12px;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #16a34a;
        color: #fff;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.35px;
    }

    .price-card-period {
        font-size: 13px;
        color: #374151;
        font-weight: 700;
    }

    .price-card-amount {
        margin-top: 6px;
        font-size: 20px;
        line-height: 1.1;
        color: #111827;
        font-weight: 800;
    }

    .price-card-note {
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .price-card-old {
        margin-top: 3px;
        font-size: 12px;
        color: #9ca3af;
        text-decoration: line-through;
        min-height: 18px;
    }

    .price-card-save {
        margin-top: 3px;
        font-size: 12px;
        color: #047857;
        font-weight: 700;
        min-height: 18px;
    }

    .price-card-monthly {
        margin-top: 2px;
        font-size: 11px;
        color: #6b7280;
    }

    .price-card.reprice {
        animation: repriceFlash 0.45s ease;
    }

    @keyframes repriceFlash {
        0% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.35);
            transform: translateY(0);
        }

        45% {
            box-shadow: 0 0 0 6px rgba(79, 70, 229, 0.18);
            transform: translateY(-1px);
        }

        100% {
            box-shadow: inherit;
            transform: translateY(0);
        }
    }

    .currency-switch {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 8px;
    }

    .currency-world-select {
        min-width: 0;
        flex: 1 1 160px;
        max-width: 280px;
        font-size: 13px;
        border-radius: 8px;
        border: 1px solid #c7d2fe;
    }

    .currency-btn {
        padding: 3px 11px;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        background: #fff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s ease;
        line-height: 1.6;
        flex: 0 0 auto;
    }

    .currency-btn:hover {
        border-color: #818cf8;
        background: #eef2ff;
    }

    .currency-btn.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }

    .currency-rate-hint {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 4px;
        min-height: 16px;
    }

    .cta-free-submit {
        background: linear-gradient(135deg, #10b981 0%, #22c55e 50%, #16a34a 100%) !important;
        border: none !important;
        font-weight: 700;
        letter-spacing: 0.2px;
        box-shadow: 0 10px 24px rgba(22, 163, 74, 0.28);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }

    .cta-free-submit:hover {
        transform: translateY(-2px);
        filter: brightness(1.03);
        box-shadow: 0 14px 28px rgba(22, 163, 74, 0.32);
    }

    @media (max-width: 767px) {
        .price-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .ad-create-container {
            padding: 20px 16px;
        }
    }

    /* Стили для модального окна обрезки */
    .cropper-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .cropper-modal-content {
        background: #fff;
        border-radius: 12px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .cropper-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .cropper-modal-header h5 {
        margin: 0;
        font-weight: 600;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cropper-modal-header h5 i {
        color: #667eea;
    }

    .cropper-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        line-height: 1;
    }

    .cropper-modal-close:hover {
        color: #1a1a1a;
    }

    .cropper-modal-body {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        max-height: 70vh;
    }

    .cropper-container-wrapper {
        width: 100%;
        min-height: 300px;
        max-height: 50vh;
        background: #f3f4f6;
        border-radius: 8px;
    }

    .cropper-container-wrapper img {
        max-width: 100%;
        max-height: 50vh;
        display: block;
    }

    .cropper-info {
        padding: 8px 14px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-radius: 20px;
        color: #059669;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 1px solid #a7f3d0;
    }

    .cropper-info i {
        font-size: 14px;
    }

    /* Превью баннера - реальный размер как на главной */
    .cropper-preview-section {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        width: 100%;
    }

    .cropper-preview-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }

    .cropper-preview-banner {
        width: 100%;
        height: 200px;
        /* Реальный размер как на главной */
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        border: 3px solid #667eea;
        /* Фиолетовая рамка как у кропа */
    }

    .cropper-preview-banner #cropperPreview {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .cropper-preview-banner #cropperPreview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cropper-preview-size {
        margin-top: 10px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
    }

    .cropper-controls {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .cropper-controls .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cropper-controls .btn-outline-secondary {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
    }

    .cropper-controls .btn-outline-secondary:hover {
        background: #f3f4f6;
    }

    .cropper-modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .cropper-modal-footer .btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
    }

    .btn-cancel {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        color: #374151;
    }

    .btn-cancel:hover {
        background: #e5e7eb;
    }

    .btn-apply {
        background: #667eea;
        border: none;
        color: #fff;
    }

    .btn-apply:hover {
        background: #5568d3;
    }

    /* Превью как на главной странице */
    .preview-container {
        position: relative;
        width: 100%;
    }

    .preview-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .preview-label i {
        color: #22c55e;
    }

    .preview-banner-frame {
        position: relative;
        width: 100%;
        height: 200px;
        border-radius: 12px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 3px dashed #cbd5e1;
        transition: border-color 0.2s ease, background 0.2s ease;
    }

    #image-preview.has-image .preview-banner-frame {
        border: 3px solid #22c55e;
    }

    #image-preview-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }

    #image-preview.has-image #image-preview-img {
        display: block;
    }

    .preview-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 16px 20px;
        color: #6b7280;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        pointer-events: none;
    }

    #image-preview.has-image .preview-placeholder {
        display: none;
    }

    .preview-placeholder i {
        font-size: 36px;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    .preview-placeholder-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .preview-placeholder-sub {
        font-size: 12px;
        color: #6b7280;
        max-width: 360px;
    }

    .preview-icon-ok {
        color: #22c55e;
        display: none;
    }

    .preview-icon-empty {
        color: #9ca3af;
    }

    #image-preview.has-image .preview-icon-ok {
        display: inline-block;
    }

    #image-preview.has-image .preview-icon-empty {
        display: none;
    }

    .btn-edit-image {
        position: absolute;
        bottom: 12px;
        right: 12px;
        background: rgba(102, 126, 234, 0.95);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        display: none;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    #image-preview.has-image .btn-edit-image {
        display: flex;
    }

    .btn-edit-image:hover {
        background: rgba(86, 104, 211, 0.95);
        transform: translateY(-2px);
    }

    .btn-edit-image i {
        font-size: 14px;
    }

    .preview-hint {
        margin-top: 10px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
    }

    /* Презентация: как баннер выглядит на главной (платформа) */
    .ad-live-demo {
        margin-top: 20px;
        padding: 16px 14px 18px;
        background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 45%, #eceff1 100%);
        border-radius: 16px;
        border: 1px solid rgba(102, 126, 234, 0.2);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
    }

    .ad-live-demo-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ad-live-demo-title i {
        color: #6366f1;
    }

    .ad-live-demo-sub {
        font-size: 12px;
        color: #64748b;
        margin: 0 0 14px 0;
        line-height: 1.45;
    }

    .ad-live-demo-chrome {
        height: 38px;
        border-radius: 12px 12px 0 0;
        background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        color: #fff;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.02em;
    }

    .ad-live-demo-chrome span:last-child {
        opacity: 0.9;
        font-weight: 500;
        font-size: 18px;
    }

    .ad-live-demo-body {
        background: #f0f2f5;
        padding: 10px 8px 12px;
        border-radius: 0 0 14px 14px;
        border: 1px solid rgba(11, 20, 26, 0.06);
        border-top: none;
    }

    .ad-live-demo-carousel {
        background: #f0f2f5;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12), 0 1px 2px rgba(11, 20, 26, 0.08);
        border: 1px solid rgba(11, 20, 26, 0.06);
        position: relative;
    }

    .ad-live-demo-carousel .carousel-inner-demo {
        border-radius: 8px;
        overflow: hidden;
        margin: 6px;
        background: #fff;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.08);
        position: relative;
        min-height: 168px;
    }

    .ad-live-demo-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.5s ease;
        pointer-events: none;
        z-index: 0;
    }

    .ad-live-demo-slide.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 1;
    }

    .ad-live-demo-slide a {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
        cursor: default;
    }

    .ad-live-demo-slide a.ad-live-demo-link-ready {
        cursor: pointer;
    }

    .ad-live-demo-banner-wrap {
        position: relative;
        width: 100%;
        height: 168px;
        background: #eceff1;
        padding: 4px;
        box-sizing: border-box;
    }

    .ad-live-demo-banner-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 7.5px;
        display: block;
    }

    .ad-live-demo-placeholder {
        position: absolute;
        inset: 4px;
        border-radius: 7.5px;
        background: #dfe5e8;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        padding: 12px;
    }

    .ad-live-demo-placeholder i {
        font-size: 28px;
        margin-bottom: 6px;
        opacity: 0.85;
    }

    .ad-live-demo-filler {
        height: 100%;
        min-height: 168px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 16px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        border-radius: 7.5px;
        margin: 4px;
    }

    .ad-live-demo-dots {
        display: flex;
        justify-content: center;
        gap: 6px;
        padding: 6px 0 2px;
    }

    .ad-live-demo-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #cbd5e1;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .ad-live-demo-dot.on {
        background: #6366f1;
        transform: scale(1.15);
    }

    .ad-live-demo-user-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }

    .ad-live-demo-user-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(11, 20, 26, 0.05);
    }

    .ad-live-demo-user-ph {
        height: 100px;
        background: linear-gradient(135deg, #dbeafe 0%, #e9d5ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ad-live-demo-user-ph>i {
        font-size: 3.25rem;
        line-height: 1;
        color: #3b5bdb;
        opacity: 0.92;
    }

    .ad-live-demo-user-ph.alt {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
    }

    .ad-live-demo-user-ph.alt>i {
        color: #db2777;
    }

    .ad-live-demo-user-cap {
        padding: 10px 10px 12px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 2px;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
        background: linear-gradient(to bottom, rgba(255, 255, 255, 0.98) 0%, #ffffff 100%);
    }

    .ad-live-demo-user-name {
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: -0.02em;
        margin: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .ad-live-demo-user-meta {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
    }

    .ad-live-demo-note {
        margin-top: 10px;
        font-size: 11px;
        color: #64748b;
        text-align: center;
    }

    @media (min-width: 768px) {
        .ad-live-demo-carousel .carousel-inner-demo {
            min-height: 200px;
        }

        .ad-live-demo-banner-wrap {
            height: 200px;
        }

        .ad-live-demo-filler {
            min-height: 200px;
        }
    }
</style>

<div class="ad-create-container">
    <h2 class="mb-4">Разместить рекламу</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= Helper::escape($_SESSION['success_message']) ?>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= Helper::escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($hasPending) && $hasPending && $pendingAd): ?>
        <div class="alert alert-warning">
            <!-- <h5 class="mb-3"><i class="bi bi-exclamation-triangle-fill"></i> У вас уже есть заявка на модерации</h5> -->
            <p class="mb-2"><strong>Ваша заявка на размещение рекламы отправлена на модерацию. Мы свяжемся с вами в ближайшее время.</strong></p>
            <div class="mt-3">
                <p class="mb-1"><strong>Информация о заявке:</strong></p>
                <ul class="mb-0">
                    <li>Название: <?= Helper::escape($pendingAd['advertiser_name']) ?></li>
                    <li>Страна: <?= Helper::escape($pendingAd['country']) ?></li>
                    <?php if (!empty($pendingAd['city'])): ?>
                        <li>Город: <?= Helper::escape($pendingAd['city']) ?></li>
                    <?php endif; ?>
                    <li>Дата подачи: <?= date('d.m.Y H:i', strtotime($pendingAd['created_at'])) ?></li>
                    <li>Статус: <span class="badge bg-warning">На модерации</span></li>
                </ul>
            </div>
            <p class="mb-0 mt-3">
                <a href="<?= BASE_URL ?>profile" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Вернуться в профиль
                </a>
            </p>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>ads/store" enctype="multipart/form-data" class="ad-create-form">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="advertiser_name" class="form-label">Название компании или название рекламы *</label>
                        <input type="text"
                            class="form-control"
                            id="advertiser_name"
                            name="advertiser_name"
                            value="<?= isset($old['advertiser_name']) ? Helper::escape($old['advertiser_name']) : '' ?>"
                            required
                            placeholder="Например: ООО Компания">
                    </div>

                    <div class="mb-3">
                        <label for="advertiser_email" class="form-label">Email для связи *</label>
                        <input type="email"
                            class="form-control"
                            id="advertiser_email"
                            name="advertiser_email"
                            value="<?= isset($old['advertiser_email']) ? Helper::escape($old['advertiser_email']) : (isset($userEmail) ? Helper::escape($userEmail) : '') ?>"
                            <?= (!empty($userEmail)) ? 'readonly' : '' ?>
                            required
                            placeholder="example@mail.com">
                        <?php if (!empty($userEmail)): ?>
                            <!-- <div class="form-text">
                                Реклама привязывается к email вашего аккаунта, чтобы она отображалась в «Моя реклама» в профиле.
                            </div> -->
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Страна *</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="">Выберите страну...</option>
                                <option value="Казахстан" <?= (isset($old['country']) && $old['country'] === 'Казахстан') ? 'selected' : '' ?>>Казахстан</option>
                                <option value="Россия" <?= (isset($old['country']) && $old['country'] === 'Россия') ? 'selected' : '' ?>>Россия</option>
                                <option value="Узбекистан" <?= (isset($old['country']) && $old['country'] === 'Узбекистан') ? 'selected' : '' ?>>Узбекистан</option>
                                <option value="Кыргызстан" <?= (isset($old['country']) && $old['country'] === 'Кыргызстан') ? 'selected' : '' ?>>Кыргызстан</option>
                                <option value="Азербайджан" <?= (isset($old['country']) && $old['country'] === 'Азербайджан') ? 'selected' : '' ?>>Азербайджан</option>
                                <option value="Армения" <?= (isset($old['country']) && $old['country'] === 'Армения') ? 'selected' : '' ?>>Армения</option>
                                <option value="Белоруссия" <?= (isset($old['country']) && $old['country'] === 'Белоруссия') ? 'selected' : '' ?>>Белоруссия</option>
                                <option value="Таджикистан" <?= (isset($old['country']) && $old['country'] === 'Таджикистан') ? 'selected' : '' ?>>Таджикистан</option>

                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" id="cityFieldLabel">Город *</label>
                            <div id="citySelectorsContainer">
                                <!-- Селекты городов рендерятся динамически в зависимости от city_count -->
                            </div>
                            <div class="alert alert-info py-2 mb-0 mt-1" id="cityAllText" style="display:none;">
                                <i class="bi bi-globe2"></i> Реклама будет показываться <strong>во всех городах</strong>
                            </div>
                            <div class="form-text" id="cityHint">
                                Реклама будет показываться только пользователям из выбранного города
                            </div>
                        </div>
                    </div>

                    <div class="tariff-box">
                        <h6><i class="bi bi-cash-stack"></i> Тариф размещения рекламы</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city_count" class="form-label">Количество городов показа *</label>
                                <select class="form-select" id="city_count" name="city_count">
                                    <option value="1" <?= (isset($old['city_count']) && (string)$old['city_count'] === '1') ? 'selected' : '' ?>>1 город</option>
                                    <option value="2" <?= (isset($old['city_count']) && (string)$old['city_count'] === '2') ? 'selected' : '' ?>>2 города</option>
                                    <option value="3" <?= (isset($old['city_count']) && (string)$old['city_count'] === '3') ? 'selected' : '' ?>>3 города</option>
                                    <option value="4" <?= (isset($old['city_count']) && (string)$old['city_count'] === '4') ? 'selected' : '' ?>>4 города</option>
                                    <option value="5" <?= (isset($old['city_count']) && (string)$old['city_count'] === '5') ? 'selected' : '' ?>>5 городов</option>
                                    <option value="all" <?= (isset($old['city_count']) && (string)$old['city_count'] === 'all') ? 'selected' : '' ?>>Все города</option>
                                </select>
                                <div class="form-text">Сейчас оформление бесплатное. Тарифы отображаются для запуска оплаты.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tariff_period" class="form-label">Период размещения *</label>
                                <select class="form-select d-none" id="tariff_period" name="tariff_period">
                                    <option value="1" <?= (isset($old['tariff_period']) && (string)$old['tariff_period'] === '1') ? 'selected' : '' ?>>1 месяц</option>
                                    <option value="3" <?= (isset($old['tariff_period']) && (string)$old['tariff_period'] === '3') ? 'selected' : '' ?>>3 месяца (экономия 20%)</option>
                                    <option value="6" <?= (isset($old['tariff_period']) && (string)$old['tariff_period'] === '6') ? 'selected' : '' ?>>6 месяцев (экономия 30%)</option>
                                </select>
                                <div class="currency-switch d-none" id="currencySwitch" aria-hidden="true">
                                    <button type="button" class="currency-btn active" data-currency="KZT" id="currencyKztBtn">₸ KZT</button>
                                    <label for="currencyWorldSelect" class="visually-hidden">Валюта отображения тарифа</label>
                                    <select class="form-select form-select-sm currency-world-select" id="currencyWorldSelect" title="Курс к тенге (подстраивается под выбранную страну, можно сменить)"></select>
                                </div>
                                <div class="currency-rate-hint d-none" id="currencyRateHint" aria-hidden="true"></div>
                                <div class="price-grid" id="priceGrid">
                                    <div class="price-card active" data-period="1">
                                        <div class="price-card-period">1 месяц</div>
                                        <div class="price-card-amount" data-amount-period="1">5 000 тг</div>
                                        <div class="price-card-old" data-old-period="1"></div>
                                        <div class="price-card-save" data-save-period="1"></div>
                                        <div class="price-card-monthly" data-monthly-period="1">5 000 тг / мес</div>
                                        <div class="price-card-note">Стартовый тариф</div>
                                    </div>
                                    <div class="price-card recommended" data-period="3">
                                        <div class="price-card-recommend">Рекомендуем</div>
                                        <div class="price-card-period">3 месяца</div>
                                        <div class="price-card-amount" data-amount-period="3">12 000 тг</div>
                                        <div class="price-card-old" data-old-period="3">15 000 тг</div>
                                        <div class="price-card-save" data-save-period="3">Экономия 3 000 тг</div>
                                        <div class="price-card-monthly" data-monthly-period="3">4 000 тг / мес</div>
                                        <div class="price-card-note">Экономия 20%</div>
                                    </div>
                                    <div class="price-card" data-period="6">
                                        <div class="price-card-period">6 месяцев</div>
                                        <div class="price-card-amount" data-amount-period="6">21 000 тг</div>
                                        <div class="price-card-old" data-old-period="6">30 000 тг</div>
                                        <div class="price-card-save" data-save-period="6">Экономия 9 000 тг</div>
                                        <div class="price-card-monthly" data-monthly-period="6">3 500 тг / мес</div>
                                        <div class="price-card-note">Экономия 30%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="tariffSummary" class="tariff-summary  d-none">
                            Итоговый тариф: <strong>5 000 тг</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="click_url" class="form-label">URL для перехода *</label>
                        <input type="url"
                            class="form-control"
                            id="click_url"
                            name="click_url"
                            value="<?= isset($old['click_url']) ? Helper::escape($old['click_url']) : '' ?>"
                            required
                            placeholder="example.com (https:// добавится автоматически)">
                        <div class="form-text">Ссылка, на которую будет переходить пользователь при клике на рекламу.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Дата начала показа *</label>
                            <input type="date"
                                class="form-control"
                                id="start_date"
                                name="start_date"
                                max="<?= date('Y') ?>-12-31"
                                value="<?= isset($old['start_date']) ? Helper::escape($old['start_date']) : '' ?>"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Дата окончания показа *</label>
                            <input type="date"
                                class="form-control"
                                id="end_date"
                                name="end_date"
                                readonly
                                max="<?= date('Y') ?>-12-31"
                                value="<?= isset($old['end_date']) ? Helper::escape($old['end_date']) : '' ?>"
                                required>
                            <div class="form-text">Автоматически считается по выбранному периоду (1/3/6 мес).</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Рекламный баннер *</label>
                        <input type="file"
                            class="form-control"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/jpg,image/png,image/gif">
                        <input type="hidden" name="image_cropped_base64" id="image_cropped_base64" value="">
                        <div id="image-preview" class="mt-3">
                            <div class="preview-container">
                                <div class="preview-label">
                                    <i class="bi bi-check-circle-fill preview-icon-ok"></i>
                                    <i class="bi bi-image preview-icon-empty"></i>
                                    <span class="preview-label-text">Так будет выглядеть ваш баннер:</span>
                                </div>
                                <div class="preview-banner-frame">
                                    <img id="image-preview-img" src="" alt="Предпросмотр">
                                    <div class="preview-placeholder">
                                        <i class="bi bi-image"></i>
                                        <div class="preview-placeholder-title">Здесь появится ваш баннер</div>
                                        <div class="preview-placeholder-sub">Загрузите изображение ниже — и оно отобразится так, как увидят пользователи на сайте</div>
                                    </div>
                                    <button type="button" class="btn-edit-image" id="editImageBtn">
                                        <i class="bi bi-pencil-square"></i> Редактировать
                                    </button>
                                </div>
                                <div class="preview-hint">
                                    Ниже — презентация: так баннер и карусель выглядят в ленте на главной
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ad-live-demo" id="adLiveDemo" aria-label="Превью размещения на главной">
                        <div class="ad-live-demo-title">
                            <i class="bi bi-phone"></i>
                            Так увидят пользователи
                        </div>

                        <div class="ad-live-demo-chrome">
                            <span>Aru</span>
                            <span aria-hidden="true"><i class="bi bi-person-circle"></i></span>
                        </div>
                        <div class="ad-live-demo-body">
                            <div class="ad-live-demo-carousel" id="adLiveDemoCarousel">
                                <div class="carousel-inner-demo">
                                    <div class="ad-live-demo-slide active" data-live-slide="0">
                                        <a id="adLiveDemoLink" href="#" rel="noopener noreferrer" title="">
                                            <div class="ad-live-demo-banner-wrap">
                                                <img id="adLiveDemoImg" src="" alt="Ваш баннер в ленте">
                                                <div class="ad-live-demo-placeholder" id="adLiveDemoPlaceholder">
                                                    <i class="bi bi-megaphone"></i>
                                                    <div>Ваш баннер — здесь</div>
                                                    <div style="font-size:11px;font-weight:500;margin-top:4px;opacity:.9">Загрузите изображение выше</div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="ad-live-demo-slide" data-live-slide="1">
                                        <div class="ad-live-demo-filler">
                                            <span>Реклама на главной странице</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ad-live-demo-dots" aria-hidden="true">
                                    <span class="ad-live-demo-dot on" data-live-dot="0"></span>
                                    <span class="ad-live-demo-dot" data-live-dot="1"></span>
                                </div>
                            </div>
                            <div class="ad-live-demo-user-grid">
                                <div class="ad-live-demo-user-card">
                                    <div class="ad-live-demo-user-ph" role="img" aria-label="Профиль мужчины">
                                        <i class="bi bi-person" aria-hidden="true"></i>
                                    </div>
                                    <div class="ad-live-demo-user-cap">
                                        <strong class="ad-live-demo-user-name">Алексей</strong>
                                        <small class="ad-live-demo-user-meta">29 лет</small>
                                    </div>
                                </div>
                                <div class="ad-live-demo-user-card">
                                    <div class="ad-live-demo-user-ph alt" role="img" aria-label="Профиль женщины">
                                        <i class="bi bi-person-standing-dress" aria-hidden="true"></i>
                                    </div>
                                    <div class="ad-live-demo-user-cap">
                                        <strong class="ad-live-demo-user-name">Мария</strong>
                                        <small class="ad-live-demo-user-meta">27 лет</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary cta-free-submit">
                            <i class="bi bi-gift"></i> Разместить БЕСПЛАТНО (с модерацией)
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- Модальное окно для обрезки изображения -->
<div class="cropper-modal-overlay" id="cropperModal">
    <div class="cropper-modal-content">
        <div class="cropper-modal-header">
            <h5><i class="bi bi-crop"></i> Редактирование изображения</h5>
            <div class="cropper-info">
                <i class="bi bi-arrows-move"></i> Перетащите рамку для выбора области
            </div>
            <button type="button" class="cropper-modal-close" id="cropperClose">&times;</button>
        </div>
        <div class="cropper-modal-body">
            <div class="cropper-container-wrapper">
                <img id="cropperImage" src="" alt="Изображение для обрезки">
            </div>


            <!-- Превью как будет выглядеть баннер -->
            <div class="cropper-preview-section">
                <div class="cropper-preview-label">Предпросмотр баннера:</div>
                <div class="cropper-preview-banner">
                    <div id="cropperPreview"></div>
                </div>
                <div class="cropper-preview-size" id="cropperSize">Размер: -- x -- px</div>
            </div>

            <div class="cropper-controls">
                <button type="button" class="btn btn-outline-secondary" id="cropperRotateLeft">
                    <i class="bi bi-arrow-counterclockwise"></i> Повернуть влево
                </button>
                <button type="button" class="btn btn-outline-secondary" id="cropperRotateRight">
                    <i class="bi bi-arrow-clockwise"></i> Повернуть вправо
                </button>
                <button type="button" class="btn btn-outline-secondary" id="cropperFlipH">
                    <i class="bi bi-symmetry-vertical"></i> Отразить
                </button>
                <button type="button" class="btn btn-outline-secondary" id="cropperReset">
                    <i class="bi bi-arrow-repeat"></i> Сбросить
                </button>
            </div>
        </div>
        <div class="cropper-modal-footer">
            <button type="button" class="btn btn-cancel" id="cropperCancel">Отмена</button>
            <button type="button" class="btn btn-apply" id="cropperApply">
                <i class="bi bi-check-lg"></i> Применить
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('image');
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('image-preview-img');

        // Целевые размеры из PHP констант
        const TARGET_WIDTH = <?= AD_IMAGE_WIDTH ?>;
        const TARGET_HEIGHT = <?= AD_IMAGE_HEIGHT ?>;

        // Элементы модального окна
        const cropperModal = document.getElementById('cropperModal');
        const cropperImage = document.getElementById('cropperImage');
        const cropperClose = document.getElementById('cropperClose');
        const cropperCancel = document.getElementById('cropperCancel');
        const cropperApply = document.getElementById('cropperApply');
        const cropperRotateLeft = document.getElementById('cropperRotateLeft');
        const cropperRotateRight = document.getElementById('cropperRotateRight');
        const cropperFlipH = document.getElementById('cropperFlipH');
        const cropperReset = document.getElementById('cropperReset');

        let cropper = null;
        let currentFile = null;
        let originalFile = null; // Сохраняем оригинальный файл для повторного редактирования

        const editImageBtn = document.getElementById('editImageBtn');

        /**
         * Открывает модальное окно с кроппером
         */
        function openCropperModal(file) {
            currentFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                cropperImage.src = e.target.result;
                cropperModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Инициализируем Cropper после загрузки изображения с небольшой задержкой
                cropperImage.onload = function() {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }

                    // Задержка для корректного расчёта размеров
                    setTimeout(function() {
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: NaN, // Свободная обрезка - любые пропорции
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1, // Выделяет максимальную область (всё изображение)
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            responsive: true,
                            background: true,
                            preview: '#cropperPreview', // Показывать превью в реальном времени
                            crop: function(event) {
                                // Обновляем размер при изменении области
                                const width = Math.round(event.detail.width);
                                const height = Math.round(event.detail.height);
                                document.getElementById('cropperSize').textContent =
                                    'Размер: ' + width + ' x ' + height + ' px';
                            }
                        });
                    }, 100); // Задержка 100мс
                };
            };
            reader.readAsDataURL(file);
        }

        /**
         * Закрывает модальное окно
         */
        function closeCropperModal() {
            cropperModal.style.display = 'none';
            document.body.style.overflow = '';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            // Очищаем input и обрезанные данные если пользователь отменил
            imageInput.value = '';
            const base64Input = document.getElementById('image_cropped_base64');
            if (base64Input) base64Input.value = '';
            previewImg.src = '';
            preview.classList.remove('has-image');
            if (typeof syncAdLiveDemo === 'function') syncAdLiveDemo();
        }

        /**
         * Применяет обрезку и сохраняет результат
         */
        function applyCrop() {
            if (!cropper) return;

            // Получаем обрезанное изображение с максимальным размером 1200px по большей стороне
            const cropData = cropper.getData();
            let canvasWidth = cropData.width;
            let canvasHeight = cropData.height;

            // Ограничиваем максимальный размер для оптимизации
            const maxSize = 1200;
            if (canvasWidth > maxSize || canvasHeight > maxSize) {
                const ratio = Math.min(maxSize / canvasWidth, maxSize / canvasHeight);
                canvasWidth = Math.round(canvasWidth * ratio);
                canvasHeight = Math.round(canvasHeight * ratio);
            }

            const canvas = cropper.getCroppedCanvas({
                width: canvasWidth,
                height: canvasHeight,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) {
                alert('Не удалось обработать изображение');
                return;
            }

            // Определяем формат файла
            let mimeType = currentFile.type;
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(mimeType)) {
                mimeType = 'image/jpeg';
            }

            canvas.toBlob(function(blob) {
                if (!blob) {
                    alert('Не удалось создать изображение');
                    return;
                }

                // Отправляем модератору именно обрезанное изображение: сохраняем в скрытое поле base64
                const dataUrl = canvas.toDataURL(mimeType, 0.92);
                const base64Input = document.getElementById('image_cropped_base64');
                if (base64Input) base64Input.value = dataUrl;

                // Создаем новый File объект для превью и запасной отправки
                const croppedFile = new File([blob], currentFile.name, {
                    type: mimeType,
                    lastModified: Date.now()
                });

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                imageInput.files = dataTransfer.files;

                // Показываем превью
                previewImg.src = dataUrl;
                preview.classList.add('has-image');
                if (typeof syncAdLiveDemo === 'function') syncAdLiveDemo();

                // Показываем информацию о результате
                showResultInfo(canvasWidth, canvasHeight);

                // Закрываем модальное окно
                cropperModal.style.display = 'none';
                document.body.style.overflow = '';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }, mimeType, 0.92);
        }

        /**
         * Показывает информацию о результате
         */
        function showResultInfo(width, height) {
            const oldNotice = document.getElementById('crop-result-notice');
            if (oldNotice) oldNotice.remove();

            const notice = document.createElement('div');
            notice.id = 'crop-result-notice';
            notice.className = 'alert alert-success mt-2';
            notice.innerHTML = '<i class="bi bi-check-circle"></i> Изображение обрезано до ' + width + 'x' + height + ' пикселей';
            preview.parentNode.insertBefore(notice, preview.nextSibling);
        }

        // Обработчик выбора файла
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Проверяем тип файла
                    if (!file.type.match(/image\/(jpeg|jpg|png|gif)/)) {
                        alert('Пожалуйста, выберите изображение в формате JPG, PNG или GIF');
                        imageInput.value = '';
                        return;
                    }

                    // Сохраняем оригинальный файл для возможности повторного редактирования
                    originalFile = file;

                    // Открываем модальное окно для обрезки
                    openCropperModal(file);
                } else {
                    previewImg.src = '';
                    preview.classList.remove('has-image');
                    if (typeof syncAdLiveDemo === 'function') syncAdLiveDemo();
                }
            });
        }

        // Обработчик кнопки "Редактировать"
        if (editImageBtn) {
            editImageBtn.addEventListener('click', function() {
                if (originalFile) {
                    openCropperModal(originalFile);
                }
            });
        }

        // Обработчики кнопок модального окна
        if (cropperClose) cropperClose.addEventListener('click', closeCropperModal);
        if (cropperCancel) cropperCancel.addEventListener('click', closeCropperModal);
        if (cropperApply) cropperApply.addEventListener('click', applyCrop);

        // Кнопки управления
        if (cropperRotateLeft) {
            cropperRotateLeft.addEventListener('click', function() {
                if (cropper) cropper.rotate(-90);
            });
        }

        if (cropperRotateRight) {
            cropperRotateRight.addEventListener('click', function() {
                if (cropper) cropper.rotate(90);
            });
        }

        if (cropperFlipH) {
            cropperFlipH.addEventListener('click', function() {
                if (cropper) {
                    const data = cropper.getData();
                    cropper.scaleX(data.scaleX === -1 ? 1 : -1);
                }
            });
        }

        if (cropperReset) {
            cropperReset.addEventListener('click', function() {
                if (cropper) cropper.reset();
            });
        }

        // Закрытие по клику на оверлей
        cropperModal.addEventListener('click', function(e) {
            if (e.target === cropperModal) {
                closeCropperModal();
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && cropperModal.style.display === 'flex') {
                closeCropperModal();
            }
        });

        // Данные городов по странам
        const citiesByCountry = {
            'Казахстан': [
                'ВСЕ ГОРОДА',
                'Алматы', 'Нур-Султан', 'Шымкент', 'Караганда', 'Актобе', 'Тараз', 'Павлодар',
                'Усть-Каменогорск', 'Семей', 'Кызылорда', 'Балхаш', 'Рудный', 'Жезказган',
                'Сатпаев', 'Экибастуз', 'Петропавловск', 'Костанай', 'Атырау', 'Актау',
                'Уральск', 'Темиртау', 'Туркестан', 'Талдыкорган', 'Кокшетау', 'Астана',
                'Атырау', 'Жанаозен', 'Кентау', 'Риддер', 'Сарыагаш', 'Текели', 'Ленгер',
                'Капшагай', 'Каскелен', 'Талгар', 'Есик', 'Байсерке', 'Уштобе', 'Жаркент',
                'Панфилов', 'Алаколь', 'Сарканд', 'Аксу', 'Шу', 'Аягуз', 'Зайсан', 'Курчатов',
                'Семипалатинск', 'Шемонаиха', 'Бородулиха', 'Урджар', 'Тарбагатай', 'Катон-Карагай',
                'Зыряновск', 'Алтай', 'Шемонаиха', 'Глубокое', 'Усть-Каменогорск', 'Бухтарма',
                'Зыряновск', 'Алтай', 'Шемонаиха', 'Глубокое', 'Лениногорск', 'Серебрянск',
                'Каражал', 'Успенка', 'Белоусовка', 'Шар', 'Уваровка', 'Бородулиха', 'Урджар',
                'Тарбагатай', 'Катон-Карагай', 'Зыряновск', 'Алтай', 'Шемонаиха', 'Глубокое',
                'Лениногорск', 'Серебрянск', 'Каражал', 'Успенка', 'Белоусовка', 'Шар', 'Уваровка',
                'Бородулиха', 'Урджар', 'Тарбагатай', 'Катон-Карагай', 'Зыряновск', 'Алтай',
                'Шемонаиха', 'Глубокое', 'Лениногорск', 'Серебрянск', 'Каражал', 'Успенка',
                'Белоусовка', 'Шар', 'Уваровка', 'Бородулиха', 'Урджар', 'Тарбагатай', 'Катон-Карагай'
            ],
            'Россия': [
                'ВСЕ ГОРОДА',
                'Москва', 'Санкт-Петербург', 'Екатеринбург', 'Новосибирск', 'Казань',
                'Нижний Новгород', 'Челябинск', 'Самара', 'Омск', 'Ростов-на-Дону',
                'Уфа', 'Красноярск', 'Воронеж', 'Пермь', 'Волгоград', 'Краснодар',
                'Саратов', 'Тюмень', 'Тольятти', 'Ижевск', 'Барнаул', 'Ульяновск',
                'Иркутск', 'Хабаровск', 'Ярославль', 'Владивосток', 'Оренбург', 'Томск',
                'Кемерово', 'Новокузнецк', 'Рязань', 'Астрахань', 'Пенза', 'Киров',
                'Липецк', 'Чебоксары', 'Калининград', 'Брянск', 'Курск', 'Иваново',
                'Магнитогорск', 'Тверь', 'Ставрополь', 'Улан-Удэ', 'Белгород', 'Архангельск',
                'Владимир', 'Сочи', 'Курган', 'Смоленск', 'Калуга', 'Чита', 'Орёл',
                'Вологда', 'Череповец', 'Владикавказ', 'Мурманск', 'Сургут', 'Тамбов',
                'Стерлитамак', 'Грозный', 'Якутск', 'Кострома', 'Петрозаводск', 'Йошкар-Ола',
                'Новороссийск', 'Таганрог', 'Комсомольск-на-Амуре', 'Сыктывкар', 'Нижневартовск',
                'Братск', 'Дзержинск', 'Орск', 'Нальчик', 'Ангарск', 'Благовещенск', 'Королёв',
                'Мытищи', 'Люберцы', 'Коломна', 'Одинцово', 'Долгопрудный', 'Химки', 'Балашиха',
                'Подольск', 'Королёв', 'Мытищи', 'Люберцы', 'Коломна', 'Одинцово', 'Долгопрудный',
                'Химки', 'Балашиха', 'Подольск', 'Щёлково', 'Серпухов', 'Ногинск', 'Электросталь',
                'Жуковский', 'Реутов', 'Раменское', 'Орехово-Зуево', 'Лобня', 'Красногорск',
                'Дмитров', 'Видное', 'Пушкино', 'Ивантеевка', 'Фрязино', 'Лыткарино', 'Дзержинский'
            ],
            'Узбекистан': [
                'ВСЕ ГОРОДА',
                'Ташкент', 'Самарканд', 'Бухара', 'Андижан', 'Фергана', 'Наманган',
                'Карши', 'Термез', 'Ургенч', 'Навои', 'Джизак', 'Коканд', 'Нукус',
                'Зарафшан', 'Гулистан', 'Ангрен', 'Чирчик', 'Алмалык', 'Бекабад',
                'Шахрисабз', 'Маргилан', 'Ургут', 'Кувасай', 'Булунгур', 'Пайарык',
                'Ташкентская область', 'Самаркандская область', 'Бухарская область',
                'Андижанская область', 'Ферганская область', 'Наманганская область',
                'Каршинская область', 'Термизская область', 'Ургенчская область',
                'Навоийская область', 'Джизакская область', 'Кокандская область',
                'Нукусская область', 'Зарафшанская область', 'Гулистанская область',
                'Ангренская область', 'Чирчикская область', 'Алмалыкская область'
            ],
            'Кыргызстан': [
                'ВСЕ ГОРОДА',
                'Бишкек', 'Ош', 'Джалал-Абад', 'Каракол', 'Токмок', 'Балакчи',
                'Нарын', 'Талас', 'Баткен', 'Кант', 'Кара-Балта', 'Токтогул',
                'Исфана', 'Кадамжай', 'Сулюкта', 'Рыбачье', 'Чолпон-Ата', 'Бостери',
                'Кара-Суу', 'Кызыл-Кия', 'Ат-Баши', 'Кочкор-Ата', 'Ак-Талаа',
                'Кара-Куль', 'Жалал-Абадская область', 'Иссык-Кульская область',
                'Нарынская область', 'Ошская область', 'Таласская область',
                'Чуйская область', 'Баткенская область'
            ],
            'Азербайджан': [
                'ВСЕ ГОРОДА',
                'Баку', 'Гянджа', 'Сумгайыт', 'Мингечевир', 'Хырдалан', 'Абшерон',
                'Сиазань', 'Шамкир', 'Евлах', 'Нафталан', 'Гёйчай', 'Агдам', 'Барда',
                'Бейлаган', 'Агджабеди', 'Физули', 'Агсу', 'Гаджигабула', 'Шаки',
                'Исмаиллы', 'Агстафа', 'Газах', 'Товуз', 'Кюрдамир', 'Уджар', 'Зердаб',
                'Билясувар', 'Сабирабад', 'Имишли', 'Саатлы', 'Ширван', 'Горадиз',
                'Ахсу', 'Нефтчала', 'Джалилабад', 'Масаллы', 'Ленкорань', 'Астарин',
                'Ярдымлы', 'Лерик', 'Бабек', 'Кубатлы', 'Ходжалы', 'Шуша', 'Кельбаджар',
                'Губадлы', 'Зангилан', 'Кабала', 'Огуз', 'Габала', 'Балакен', 'Загатала',
                'Гах', 'Шеки', 'Горанбой', 'Дашкесан', 'Гедебей', 'Тертер', 'Геранбой',
                'Хачмаз', 'Губа', 'Сиазань', 'Шабран', 'Худат', 'Кусары', 'Дивечи',
                'Казмах', 'Акстафа', 'Самух', 'Геранбой', 'Гёйгёль', 'Дашкесан', 'Кельбаджар'
            ],
            'Армения': [
                'ВСЕ ГОРОДА',
                'Ереван', 'Гюмри', 'Ванадзор', 'Ехегнадзор', 'Армавир', 'Арташат',
                'Иджеван', 'Гавар', 'Раздан', 'Абовян', 'Капан', 'Аштарак', 'Севан',
                'Арташат', 'Масис', 'Алаверди', 'Талин', 'Мецамор', 'Берд', 'Апаран',
                'Чаренцаван', 'Мартуни', 'Сисиан', 'Горис', 'Каджаран', 'Айрум',
                'Нор Ачин', 'Ахтала', 'Туманян', 'Джермук', 'Цахкадзор', 'Дилижан',
                'Мартуни', 'Егвард', 'Вайк', 'Мегри', 'Агарцин', 'Ноемберян', 'Ташин',
                'Карин', 'Арзни', 'Бюракан', 'Айгеван', 'Арташат', 'Масис', 'Абовян',
                'Эчмиадзин', 'Раздан', 'Гавар', 'Иджеван', 'Берд', 'Алаверди', 'Талин',
                'Мецамор', 'Апаран', 'Чаренцаван', 'Мартуни', 'Сисиан', 'Горис', 'Каджаран'
            ],
            'Белоруссия': [
                'ВСЕ ГОРОДА',
                'Минск', 'Гомель', 'Могилёв', 'Витебск', 'Гродно', 'Брест', 'Бобруйск',
                'Барановичи', 'Борисов', 'Пинск', 'Орша', 'Мозырь', 'Солигорск', 'Новополоцк',
                'Лидa', 'Молодечно', 'Полоцк', 'Жлобин', 'Светлогорск', 'Речица', 'Жодино',
                'Слуцк', 'Кобрин', 'Волковыск', 'Сморгонь', 'Рогачёв', 'Калинковичи', 'Несвиж',
                'Берёза', 'Ивацевичи', 'Столин', 'Лунинец', 'Ганцевичи', 'Микашевичи', 'Дрогичин',
                'Иваново', 'Пружаны', 'Коссово', 'Малорита', 'Шерешево', 'Домачево', 'Каменец',
                'Высокое', 'Берестовица', 'Волпа', 'Поречье', 'Свислочь', 'Островец', 'Ивье',
                'Щучин', 'Ошмяны', 'Сморгонь', 'Кореличи', 'Новогрудок', 'Любча', 'Городея',
                'Копыль', 'Старые Дороги', 'Узда', 'Дзержинск', 'Фаниполь', 'Заславль', 'Логойск',
                'Березино', 'Червень', 'Толочин', 'Крупки', 'Мядель', 'Красная Слобода', 'Климово',
                'Хотимск', 'Чаусы', 'Мстиславль', 'Кричев', 'Климовичи', 'Костюковичи', 'Хиславичи',
                'Шклов', 'Горки', 'Дубровно', 'Лиозно', 'Ветка', 'Чериков', 'Краснополье', 'Быхов',
                'Белыничи', 'Кировск', 'Славгород', 'Кличев', 'Осиповичи', 'Глуск', 'Октябрьский',
                'Стародорожский', 'Любань', 'Старобин', 'Смолевичи', 'Червень', 'Березинский',
                'Вилейка', 'Молодечненский', 'Воложин', 'Радошковичи', 'Ивенец', 'Столбцы',
                'Дзержинск', 'Фаниполь', 'Заславль', 'Логойск', 'Березино', 'Червень', 'Толочин',
                'Крупки', 'Мядель', 'Красная Слобода', 'Климово', 'Хотимск', 'Чаусы', 'Мстиславль',
                'Кричев', 'Климовичи', 'Костюковичи', 'Хиславичи', 'Шклов', 'Горки', 'Дубровно',
                'Лиозно', 'Ветка', 'Чериков', 'Краснополье', 'Быхов', 'Белыничи', 'Кировск',
                'Славгород', 'Кличев', 'Осиповичи', 'Глуск', 'Октябрьский', 'Стародорожский',
                'Любань', 'Старобин', 'Смолевичи', 'Червень', 'Березинский', 'Вилейка',
                'Молодечненский', 'Воложин', 'Радошковичи', 'Ивенец', 'Столбцы'
            ],
            'Таджикистан': [
                'ВСЕ ГОРОДА',
                'Душанбе', 'Худжанд', 'Куляб', 'Курган-Тюбе', 'Истаравшан', 'Исфара',
                'Канибадам', 'Пенджикент', 'Турсунзаде', 'Вахдат', 'Гиссар', 'Рогун',
                'Нурек', 'Файзабад', 'Тавильдара', 'Мургаб', 'Рашт', 'Джалолиддин Руми',
                'Айни', 'Зафарабад', 'Шахринав', 'Гарм', 'Дангара', 'Темурмалик',
                'Яван', 'Пандж', 'Ванч', 'Джиргаталь', 'Лахш', 'Муминабад', 'Бохтар',
                'Джоми', 'Фархор', 'Кабодиён', 'Спитамен', 'Ашт', 'Мастрик', 'Кайраккум',
                'Навкат', 'Зарафшон', 'Панджакент', 'Айни', 'Зафарабад', 'Шахринав',
                'Гарм', 'Дангара', 'Темурмалик', 'Яван', 'Пандж', 'Ванч', 'Джиргаталь',
                'Лахш', 'Муминабад', 'Бохтар', 'Джоми', 'Фархор', 'Кабодиён', 'Спитамен',
                'Ашт', 'Мастрик', 'Кайраккум', 'Навкат', 'Зарафшон', 'Панджакент'
            ]
        };

        const countrySelect = document.getElementById('country');
        const citySelectorsContainer = document.getElementById('citySelectorsContainer');
        const cityAllText = document.getElementById('cityAllText');
        const cityHint = document.getElementById('cityHint');
        const cityFieldLabel = document.getElementById('cityFieldLabel');
        const clickUrlInput = document.getElementById('click_url');
        const cityCountSelect = document.getElementById('city_count');
        const tariffPeriodSelect = document.getElementById('tariff_period');
        const tariffSummary = document.getElementById('tariffSummary');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const priceCards = document.querySelectorAll('.price-card');

        // Конвертация валюты
        const currencySymbols = {
            KZT: 'тг',
            USD: '$',
            EUR: '€',
            RUB: '₽',
            UZS: 'сум',
            KGS: 'сом',
            AZN: '₼',
            AMD: '֏',
            BYN: 'Br',
            TJS: 'смн.'
        };
        /** Курс: сколько единиц валюты за 1 KZT (как в open.er-api для base KZT). */
        const currencyFallback = {
            USD: 0.00213,
            EUR: 0.00196,
            RUB: 0.205,
            UZS: 27.2,
            KGS: 0.16,
            AZN: 0.00385,
            AMD: 0.78,
            BYN: 0.0065,
            TJS: 0.022
        };
        /** Валюта отображения тарифа по выбранной стране размещения */
        const displayCurrencyByCountry = {
            'Казахстан': 'KZT',
            'Россия': 'RUB',
            'Узбекистан': 'UZS',
            'Кыргызстан': 'KGS',
            'Азербайджан': 'AZN',
            'Армения': 'AMD',
            'Белоруссия': 'BYN',
            'Таджикистан': 'TJS'
        };
        let currentCurrency = 'KZT';
        let exchangeRates = {
            ...currencyFallback
        };
        const exchangeRatesApiUrl = <?= json_encode(rtrim(BASE_URL, '/') . '/api/exchange-rates', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

        // Старые значения с сервера (используются только при первом рендере, например после ошибки валидации)
        const oldCityValue = <?= json_encode(isset($old['city']) ? (string)$old['city'] : '') ?>;
        const oldCitiesValue = <?= json_encode(isset($old['cities']) && is_array($old['cities']) ? array_values($old['cities']) : []) ?>;
        let firstCityRender = true;
        // Запоминаем последние выбранные города (не "ВСЕ ГОРОДА"), чтобы вернуть их при переключении режимов
        let lastNonAllCityValues = [];

        const adTariffs = {
            '1': {
                '1': 5000,
                '3': 12000,
                '6': 21000
            },
            '2': {
                '1': 5500,
                '3': 13200,
                '6': 23100
            },
            '3': {
                '1': 6000,
                '3': 14400,
                '6': 25200
            },
            '4': {
                '1': 6500,
                '3': 15600,
                '6': 27300
            },
            '5': {
                '1': 7000,
                '3': 16800,
                '6': 29400
            },
            'all': {
                '1': 10000,
                '3': 24000,
                '6': 42000
            }
        };

        function formatCurrency(value, currency) {
            currency = currency || currentCurrency;
            const raw = Number(value || 0);
            if (currency === 'KZT') {
                return raw.toLocaleString('ru-RU') + ' тг';
            }
            const rate = exchangeRates[currency] || currencyFallback[currency];
            if (!rate) {
                return raw.toLocaleString('ru-RU') + ' тг';
            }
            const converted = raw * rate;
            try {
                return new Intl.NumberFormat(navigator.language || 'ru-RU', {
                    style: 'currency',
                    currency: currency,
                    maximumFractionDigits: 2
                }).format(converted);
            } catch (e) {
                const symbol = currencySymbols[currency] || currency;
                if (currency === 'USD' || currency === 'EUR') {
                    return symbol + ' ' + converted.toFixed(2);
                }
                return Math.round(converted).toLocaleString('ru-RU') + ' ' + symbol;
            }
        }

        function formatTenge(value) {
            return formatCurrency(value);
        }

        function updateRateHint() {
            const hint = document.getElementById('currencyRateHint');
            if (!hint) return;
            if (currentCurrency === 'KZT') {
                hint.textContent = 'Тарифы в тенге. Ниже можно выбрать любую валюту для ориентира по курсу.';
                return;
            }
            const rate = exchangeRates[currentCurrency] || currencyFallback[currentCurrency];
            if (!rate) {
                hint.textContent = '';
                return;
            }
            const kztPer1 = Math.round(1 / rate);
            hint.textContent = '1 ' + currentCurrency + ' ≈ ' + kztPer1.toLocaleString('ru-RU') + ' тг';
        }

        function guessDefaultDisplayCurrency() {
            const lang = (navigator.languages && navigator.languages[0]) || navigator.language || 'en-US';
            if (/kk|kz/i.test(lang)) return 'KZT';
            if (/^ru/i.test(lang)) return 'RUB';
            try {
                const loc = new Intl.Locale(lang);
                const region = loc.maximize().region;
                const map = {
                    US: 'USD',
                    GB: 'GBP',
                    DE: 'EUR',
                    FR: 'EUR',
                    IT: 'EUR',
                    ES: 'EUR',
                    NL: 'EUR',
                    BE: 'EUR',
                    AT: 'EUR',
                    PL: 'PLN',
                    TR: 'TRY',
                    JP: 'JPY',
                    CN: 'CNY',
                    IN: 'INR',
                    BR: 'BRL',
                    KR: 'KRW',
                    KZ: 'KZT',
                    RU: 'RUB',
                    UA: 'UAH',
                    UZ: 'UZS',
                    BY: 'BYN'
                };
                if (region && map[region]) return map[region];
            } catch (e) {
                /* noop */
            }
            return 'USD';
        }

        function resolveTariffDisplayCurrencyPreference(rates) {
            if (countrySelect && countrySelect.value) {
                const mapped = displayCurrencyByCountry[countrySelect.value];
                if (mapped && (mapped === 'KZT' || (rates && rates[mapped]))) {
                    return mapped;
                }
            }
            return guessDefaultDisplayCurrency();
        }

        function populateWorldCurrencies(rates) {
            const sel = document.getElementById('currencyWorldSelect');
            if (!sel || !rates) return;
            const codes = Object.keys(rates).filter(function(c) {
                return /^[A-Z]{3}$/.test(c) && c !== 'KZT';
            }).sort();
            let dn = null;
            try {
                dn = new Intl.DisplayNames(navigator.language || 'ru', {
                    type: 'currency'
                });
            } catch (e) {
                dn = null;
            }
            const prev = sel.value;
            sel.innerHTML = '';
            const kztOpt = document.createElement('option');
            kztOpt.value = 'KZT';
            kztOpt.textContent = 'KZT — тенге';
            sel.appendChild(kztOpt);
            codes.forEach(function(code) {
                const opt = document.createElement('option');
                opt.value = code;
                const label = dn ? (dn.of(code) || code) : code;
                opt.textContent = code + ' — ' + label;
                sel.appendChild(opt);
            });
            const pref = resolveTariffDisplayCurrencyPreference(rates);
            if (prev && rates[prev]) {
                sel.value = prev;
            } else if (rates[pref] || pref === 'KZT') {
                sel.value = pref;
            } else {
                sel.value = rates.USD ? 'USD' : 'KZT';
            }
            currentCurrency = sel.value || 'KZT';
            const kztBtn = document.getElementById('currencyKztBtn');
            if (kztBtn) kztBtn.classList.toggle('active', currentCurrency === 'KZT');
        }

        function setCurrentCurrency(code) {
            currentCurrency = code || 'KZT';
            const kztBtn = document.getElementById('currencyKztBtn');
            const sel = document.getElementById('currencyWorldSelect');
            if (sel && sel.value !== currentCurrency) {
                sel.value = currentCurrency;
            }
            if (kztBtn) kztBtn.classList.toggle('active', currentCurrency === 'KZT');
            updateRateHint();
            updateTariffSummary();
        }

        function fetchExchangeRates() {
            const cacheKey = 'currency_rates_kzt_v2';
            const cacheTTL = 3 * 60 * 60 * 1000;
            try {
                const cached = localStorage.getItem(cacheKey);
                if (cached) {
                    const parsed = JSON.parse(cached);
                    if (Date.now() - parsed.ts < cacheTTL && parsed.rates) {
                        exchangeRates = {
                            ...currencyFallback,
                            ...parsed.rates
                        };
                        populateWorldCurrencies(exchangeRates);
                        updateRateHint();
                        if (currentCurrency !== 'KZT') updateTariffSummary();
                        return;
                    }
                }
            } catch (e) {
                /* noop */
            }

            fetch(exchangeRatesApiUrl)
                .then(function(r) {
                    return r.json();
                })
                .then(function(data) {
                    if (data && data.rates) {
                        exchangeRates = {
                            ...currencyFallback,
                            ...data.rates
                        };
                        try {
                            localStorage.setItem(cacheKey, JSON.stringify({
                                ts: Date.now(),
                                rates: data.rates
                            }));
                        } catch (e) {
                            /* noop */
                        }
                        populateWorldCurrencies(exchangeRates);
                        updateRateHint();
                        if (currentCurrency !== 'KZT') updateTariffSummary();
                        return;
                    }
                    throw new Error('no_rates');
                })
                .catch(function() {
                    return fetch('https://open.er-api.com/v6/latest/KZT')
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            if (data && data.rates) {
                                exchangeRates = {
                                    ...currencyFallback,
                                    ...data.rates
                                };
                                try {
                                    localStorage.setItem(cacheKey, JSON.stringify({
                                        ts: Date.now(),
                                        rates: data.rates
                                    }));
                                } catch (e) {
                                    /* noop */
                                }
                                populateWorldCurrencies(exchangeRates);
                                updateRateHint();
                                if (currentCurrency !== 'KZT') updateTariffSummary();
                            }
                        });
                })
                .catch(function() {
                    /* noop */
                });
        }

        function animateNumber(el, targetValue, renderFn, duration = 360) {
            if (!el) return;

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (reducedMotion) {
                el.textContent = renderFn(targetValue);
                el.dataset.value = String(targetValue);
                return;
            }

            const startValue = Number(el.dataset.value || 0);
            const endValue = Number(targetValue || 0);

            if (startValue === endValue) {
                el.textContent = renderFn(endValue);
                return;
            }

            const startTime = performance.now();

            function easeOutCubic(t) {
                return 1 - Math.pow(1 - t, 3);
            }

            function frame(now) {
                const elapsed = now - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const eased = easeOutCubic(progress);
                const currentValue = Math.round(startValue + (endValue - startValue) * eased);
                el.textContent = renderFn(currentValue);

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    el.dataset.value = String(endValue);
                }
            }

            requestAnimationFrame(frame);
        }

        function pluralGorodaAfter(n) {
            const x = Math.abs(Number(n)) % 100;
            const y = x % 10;
            if (x >= 11 && x <= 14) return 'городов';
            if (y === 1) return 'город';
            if (y >= 2 && y <= 4) return 'города';
            return 'городов';
        }

        function getCityLabel(cityCount) {
            if (cityCount === 'all') return 'все города';
            if (cityCount === '1') return '1 город';
            if (cityCount === '2') return '2 города';
            if (cityCount === '3') return '3 города';
            if (cityCount === '4') return '4 города';
            if (cityCount === '5') return '5 городов';
            return cityCount + ' город(а)';
        }

        function getPeriodLabel(period) {
            if (period === '1') return '1 месяц';
            if (period === '3') return '3 месяца';
            if (period === '6') return '6 месяцев';
            return period + ' мес.';
        }

        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function toISODateLocal(d) {
            return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
        }

        // Добавляет месяцы, учитывая “31 число” и месяцы без такого дня
        function addMonthsClamped(date, months) {
            const d = new Date(date);
            const originalDay = d.getDate();
            d.setDate(1);
            d.setMonth(d.getMonth() + months);
            const lastDay = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
            d.setDate(Math.min(originalDay, lastDay));
            return d;
        }

        // end_date считается как: (start_date + период) - 1 день (включительно по времени показа)
        function computeEndDate() {
            if (!startDateInput || !endDateInput || !tariffPeriodSelect) return;
            const startVal = startDateInput.value;
            if (!startVal) return;

            const months = Number(tariffPeriodSelect.value || 1);
            if (!months || months < 1) return;

            const startDate = new Date(startVal + 'T00:00:00');
            if (isNaN(startDate.getTime())) return;

            const endDate = addMonthsClamped(startDate, months);
            endDate.setDate(endDate.getDate() - 1);

            endDateInput.value = toISODateLocal(endDate);

            // Не даем end_date выйти за допустимую границу (31 декабря текущего года)
            const maxVal = endDateInput.getAttribute('max');
            if (maxVal && endDateInput.value > maxVal) {
                endDateInput.value = maxVal;
            }
        }

        function getCurrentCityList() {
            if (!countrySelect) return [];
            return citiesByCountry[countrySelect.value] || [];
        }

        // Текущие выбранные значения в селектах городов (без пустых)
        function getSelectedCityValues() {
            if (!citySelectorsContainer) return [];
            return Array.from(citySelectorsContainer.querySelectorAll('select.js-city-select'))
                .map((s) => s.value)
                .filter((v) => v && v !== '');
        }

        function buildCitySelect(name, selectedValue, index) {
            const cities = getCurrentCityList();
            const select = document.createElement('select');
            select.className = 'form-select js-city-select' + (index > 0 ? ' mt-2' : '');
            select.name = name;
            select.required = true;
            select.dataset.cityIndex = String(index);

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = (index === 0) ?
                'Выберите город...' :
                'Выберите ' + (index + 1) + '-й город...';
            select.appendChild(placeholder);

            cities.forEach((city) => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                if (city === selectedValue) option.selected = true;
                select.appendChild(option);
            });

            return select;
        }

        // Перерисовать список селектов городов в зависимости от city_count и country
        function renderCitySelectors() {
            if (!citySelectorsContainer || !cityCountSelect) return;

            const cityCount = cityCountSelect.value;

            // Сохраняем уже выбранные значения, чтобы не сбросить пользовательский ввод при перерисовке
            let previousValues;
            if (firstCityRender) {
                const fromOldCities = Array.isArray(oldCitiesValue) ? oldCitiesValue.slice() : [];
                const fromOldCity = (oldCityValue && oldCityValue !== 'ВСЕ ГОРОДА') ? [oldCityValue] : [];
                previousValues = fromOldCities.length > 0 ? fromOldCities : fromOldCity;
                firstCityRender = false;
            } else {
                const liveValues = getSelectedCityValues();
                // Если в DOM сейчас режим "all" (живых селектов нет) — берём последние сохранённые
                previousValues = liveValues.length > 0 ? liveValues : lastNonAllCityValues.slice();
            }

            // Запоминаем последние "нормальные" города, чтобы не потерять их при переключении в "Все города" и обратно
            if (previousValues.length > 0) {
                lastNonAllCityValues = previousValues.slice();
            }

            citySelectorsContainer.innerHTML = '';

            if (cityCount === 'all') {
                if (cityAllText) cityAllText.style.display = '';
                if (cityHint) cityHint.style.display = 'none';
                if (cityFieldLabel) cityFieldLabel.textContent = 'Города';

                // Скрытое поле, чтобы при отправке всегда было city=ВСЕ ГОРОДА
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'city';
                hidden.value = 'ВСЕ ГОРОДА';
                citySelectorsContainer.appendChild(hidden);
                return;
            }

            if (cityAllText) cityAllText.style.display = 'none';
            if (cityHint) {
                cityHint.style.display = '';
                if (cityCount === '1') {
                    cityHint.textContent = 'Реклама будет показываться только пользователям из выбранного города';
                } else {
                    cityHint.textContent = 'Выберите ' + cityCount + ' разных ' + pluralGorodaAfter(cityCount) + ' для показа рекламы';
                }
            }

            if (cityCount === '1') {
                if (cityFieldLabel) cityFieldLabel.textContent = 'Город *';
                const select = buildCitySelect('city', previousValues[0] || '', 0);
                citySelectorsContainer.appendChild(select);
                return;
            }

            if (cityCount === '2' || cityCount === '3' || cityCount === '4' || cityCount === '5') {
                const count = Number(cityCount);
                if (cityFieldLabel) cityFieldLabel.textContent = 'Города (' + count + ') *';
                for (let i = 0; i < count; i++) {
                    const select = buildCitySelect('cities[]', previousValues[i] || '', i);
                    citySelectorsContainer.appendChild(select);
                }
            }
        }

        function updateTariffSummary() {
            if (!cityCountSelect || !tariffPeriodSelect || !tariffSummary) return;
            const cityCount = cityCountSelect.value;
            const period = tariffPeriodSelect.value;
            const amount = adTariffs[cityCount] ? adTariffs[cityCount][period] : 0;
            const baseOneMonth = adTariffs[cityCount] ? adTariffs[cityCount]['1'] : 0;

            document.querySelectorAll('[data-amount-period]').forEach((el) => {
                const cardPeriod = el.getAttribute('data-amount-period');
                const cardAmount = adTariffs[cityCount] ? adTariffs[cityCount][cardPeriod] : 0;
                animateNumber(el, cardAmount, formatTenge);
            });

            document.querySelectorAll('[data-old-period]').forEach((el) => {
                const cardPeriod = Number(el.getAttribute('data-old-period'));
                if (cardPeriod <= 1) {
                    el.textContent = '';
                    el.dataset.value = '0';
                    return;
                }
                const oldAmount = baseOneMonth * cardPeriod;
                animateNumber(el, oldAmount, formatTenge);
            });

            document.querySelectorAll('[data-save-period]').forEach((el) => {
                const cardPeriod = Number(el.getAttribute('data-save-period'));
                if (cardPeriod <= 1) {
                    el.textContent = '';
                    el.dataset.value = '0';
                    return;
                }
                const cardAmount = adTariffs[cityCount] ? adTariffs[cityCount][String(cardPeriod)] : 0;
                const oldAmount = baseOneMonth * cardPeriod;
                const saveAmount = Math.max(0, oldAmount - cardAmount);
                animateNumber(el, saveAmount, (value) => 'Экономия ' + formatTenge(value));
            });

            document.querySelectorAll('[data-monthly-period]').forEach((el) => {
                const cardPeriod = Number(el.getAttribute('data-monthly-period'));
                const cardAmount = adTariffs[cityCount] ? adTariffs[cityCount][String(cardPeriod)] : 0;
                const monthlyAmount = cardPeriod > 0 ? Math.round(cardAmount / cardPeriod) : cardAmount;
                animateNumber(el, monthlyAmount, (value) => formatTenge(value) + ' / мес');
            });

            const currentPeriod = Number(period) || 1;
            const regularAmount = baseOneMonth * currentPeriod;
            const currentSave = Math.max(0, regularAmount - amount);

            let summaryHtml =
                'Тариф: <strong>' + formatTenge(amount) + '</strong> ' +
                'за ' + getPeriodLabel(period) + ', охват: ' + getCityLabel(cityCount) + '.';
            if (currentSave > 0) {
                summaryHtml +=
                    '<br><span class="text-muted">Обычная стоимость: ' + formatTenge(regularAmount) + '</span>' +
                    '<br><span class="text-success fw-semibold">Выгода: ' + formatTenge(currentSave) + '</span>';
            }
            tariffSummary.innerHTML = summaryHtml;

            const summaryMainValueEl = tariffSummary.querySelector('strong');
            const summaryRegularEl = tariffSummary.querySelector('.text-muted');
            const summarySaveEl = tariffSummary.querySelector('.text-success.fw-semibold');

            animateNumber(summaryMainValueEl, amount, formatTenge);
            if (summaryRegularEl) {
                animateNumber(summaryRegularEl, regularAmount, (value) => 'Обычная стоимость: ' + formatTenge(value));
            }
            if (summarySaveEl) {
                animateNumber(summarySaveEl, currentSave, (value) => 'Выгода: ' + formatTenge(value));
            }

            // Короткая подсветка карточек, чтобы визуально показать пересчёт цены
            priceCards.forEach((card) => {
                card.classList.remove('reprice');
                void card.offsetWidth;
                card.classList.add('reprice');
            });

            // Поддерживаем “связку”: end_date зависит от периода показа
            computeEndDate();

            // Перерисовываем селекты городов под актуальный режим (1/2/3/все)
            renderCitySelectors();
        }

        // Функция для автоматического добавления https:// к URL
        function ensureHttps(url) {
            if (!url) return url;
            url = url.trim();
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                return 'https://' + url;
            }
            return url;
        }

        function syncAdLiveDemo() {
            const img = document.getElementById('adLiveDemoImg');
            const ph = document.getElementById('adLiveDemoPlaceholder');
            const link = document.getElementById('adLiveDemoLink');
            const prev = document.getElementById('image-preview');
            const prevImg = document.getElementById('image-preview-img');
            const urlEl = document.getElementById('click_url');
            if (!img || !link) return;

            const has = prev && prev.classList.contains('has-image') && prevImg && prevImg.src && prevImg.src.length > 16;
            if (has) {
                img.src = prevImg.src;
                img.style.display = 'block';
                if (ph) ph.style.display = 'none';
            } else {
                img.removeAttribute('src');
                img.style.display = 'none';
                if (ph) ph.style.display = 'flex';
            }

            const raw = urlEl && urlEl.value ? urlEl.value.trim() : '';
            if (raw) {
                const href = ensureHttps(raw);
                link.href = href;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.title = 'Открыть: ' + href;
                link.classList.add('ad-live-demo-link-ready');
            } else {
                link.href = '#';
                link.removeAttribute('target');
                link.rel = 'noopener noreferrer';
                link.title = 'Сначала укажите URL для перехода выше';
                link.classList.remove('ad-live-demo-link-ready');
            }
        }

        (function initAdLiveDemoCarousel() {
            const slides = document.querySelectorAll('[data-live-slide]');
            const dots = document.querySelectorAll('[data-live-dot]');
            if (slides.length < 2) return;
            let idx = 0;

            function go(i) {
                idx = ((i % slides.length) + slides.length) % slides.length;
                slides.forEach(function(s, k) {
                    s.classList.toggle('active', k === idx);
                });
                dots.forEach(function(d, k) {
                    d.classList.toggle('on', k === idx);
                });
            }

            setInterval(function() {
                go(idx + 1);
            }, 3200);
            go(0);
        })();

        const adLiveDemoLink = document.getElementById('adLiveDemoLink');
        if (adLiveDemoLink) {
            adLiveDemoLink.addEventListener('click', function(e) {
                const urlEl = document.getElementById('click_url');
                const raw = urlEl && urlEl.value ? urlEl.value.trim() : '';
                if (!raw) {
                    e.preventDefault();
                }
            });
        }

        // При смене страны — список городов и валюта отображения тарифа (например Россия → ₽)
        if (countrySelect) {
            countrySelect.addEventListener('change', function() {
                renderCitySelectors();
                const mapped = countrySelect.value ? displayCurrencyByCountry[countrySelect.value] : null;
                if (mapped && (mapped === 'KZT' || exchangeRates[mapped])) {
                    setCurrentCurrency(mapped);
                }
            });
        }

        // Первичный рендер списка городов при загрузке страницы
        renderCitySelectors();

        // Автоматически добавляем https:// к URL
        if (clickUrlInput) {
            clickUrlInput.addEventListener('blur', function() {
                const currentValue = this.value.trim();
                if (currentValue && !currentValue.startsWith('http://') && !currentValue.startsWith('https://')) {
                    this.value = 'https://' + currentValue;
                }
                syncAdLiveDemo();
            });

            // Также обрабатываем событие input для более плавного опыта
            clickUrlInput.addEventListener('input', function() {
                // Убираем лишние пробелы в начале
                this.value = this.value.trimStart();
                syncAdLiveDemo();
            });
        }

        syncAdLiveDemo();

        if (cityCountSelect && tariffPeriodSelect) {
            cityCountSelect.addEventListener('change', updateTariffSummary);
            tariffPeriodSelect.addEventListener('change', updateTariffSummary);
            updateTariffSummary();
        }

        if (startDateInput) {
            startDateInput.addEventListener('change', computeEndDate);
        }

        // Один раз посчитаем сразу при открытии страницы
        computeEndDate();

        if (priceCards.length && tariffPeriodSelect) {
            const syncActivePriceCard = function(selectedPeriod) {
                priceCards.forEach((item) => {
                    item.classList.toggle('active', item.getAttribute('data-period') === selectedPeriod);
                });
            };

            syncActivePriceCard(tariffPeriodSelect.value || '1');

            priceCards.forEach((card) => {
                card.addEventListener('click', function() {
                    const selectedPeriod = this.getAttribute('data-period');
                    tariffPeriodSelect.value = selectedPeriod;
                    syncActivePriceCard(selectedPeriod);
                    updateTariffSummary();
                });
            });
        }

        const currencyKztBtn = document.getElementById('currencyKztBtn');
        const currencyWorldSelect = document.getElementById('currencyWorldSelect');
        if (currencyKztBtn) {
            currencyKztBtn.addEventListener('click', function() {
                if (currencyWorldSelect) currencyWorldSelect.value = 'KZT';
                setCurrentCurrency('KZT');
            });
        }
        if (currencyWorldSelect) {
            currencyWorldSelect.addEventListener('change', function() {
                setCurrentCurrency(this.value || 'KZT');
            });
        }

        populateWorldCurrencies(Object.assign({
            KZT: 1
        }, currencyFallback));
        updateRateHint();
        fetchExchangeRates();

        // Перед отправкой проверяем, что пользователь обрезал баннер (модератор увидит именно его)
        const form = document.querySelector('.ad-create-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const base64Input = document.getElementById('image_cropped_base64');
                if (!base64Input || !base64Input.value || !base64Input.value.startsWith('data:image/')) {
                    e.preventDefault();
                    alert('Пожалуйста, выберите изображение и нажмите «Применить» в редакторе обрезки. Так модератор увидит баннер таким, каким он будет на сайте.');
                    if (imageInput) imageInput.focus();
                    return false;
                }

                // Валидация выбора городов в зависимости от city_count
                const cityCount = cityCountSelect ? cityCountSelect.value : '1';
                if (cityCount !== 'all' && citySelectorsContainer) {
                    const selects = Array.from(citySelectorsContainer.querySelectorAll('select.js-city-select'));
                    const values = selects.map((s) => s.value);

                    // Все ли селекты заполнены
                    if (values.some((v) => !v)) {
                        e.preventDefault();
                        const firstEmpty = selects.find((s) => !s.value);
                        if (firstEmpty) firstEmpty.focus();
                        alert(cityCount === '1' ?
                            'Пожалуйста, выберите город' :
                            'Пожалуйста, выберите все ' + cityCount + ' города');
                        return false;
                    }

                    // Проверка на дубликаты для режимов 2–5 городов
                    if (cityCount === '2' || cityCount === '3' || cityCount === '4' || cityCount === '5') {
                        const unique = Array.from(new Set(values));
                        if (unique.length !== values.length) {
                            e.preventDefault();
                            alert('Не повторяйте город — нужно ' + cityCount + ' разных ' + pluralGorodaAfter(cityCount));
                            return false;
                        }
                    }
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = $title ?? 'Разместить рекламу';
include __DIR__ . '/../layout.php';
?>