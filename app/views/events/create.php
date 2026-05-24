<?php

/**
 * СОЗДАНИЕ МЕРОПРИЯТИЯ
 */

ob_start();

$eventPublishPrice = defined('EVENT_PUBLISH_PRICE_KZT') ? (int) EVENT_PUBLISH_PRICE_KZT : 500;
$eventPublishPaid = defined('EVENT_PUBLISH_PAYMENT_ENABLED') && EVENT_PUBLISH_PAYMENT_ENABLED;
// Пропорции баннера в ленте (events/index: высота 180px, ширина ≈ карточка минус отступы)
$eventBannerAspectW = 344;
$eventBannerAspectH = 180;
$eventBannerExportW = 1200;
$eventBannerExportH = (int) round($eventBannerExportW * $eventBannerAspectH / $eventBannerAspectW);
?>

<!-- Cropper.js — обрезка баннера как в рекламе -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<style>
    /* Мобильная оптимизация формы создания мероприятия */
    @media (max-width: 767px) {
        .event-create-container {
            margin: 0;
            padding: 16px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .event-create-container h2 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #2d3748;
            text-align: center;
            letter-spacing: -0.5px;
            padding-top: 8px;
        }

        .event-create-form .card {
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border: none;
            overflow: hidden;
            background: #ffffff;
        }

        .event-create-form .card-body {
            padding: 24px 20px;
        }

        .event-create-form .mb-3 {
            margin-bottom: 24px !important;
        }

        .event-create-form .form-label {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2d3748;
            display: block;
            letter-spacing: -0.2px;
        }

        .event-create-form .form-label::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
            margin-top: 4px;
        }

        .event-create-form .form-control,
        .event-create-form .form-select {
            font-size: 16px;
            /* Предотвращает зум на iOS */
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            margin-bottom: 0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
            color: #2d3748;
        }

        .event-create-form textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .event-create-form .form-control:focus,
        .event-create-form .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            background-color: #ffffff;
            outline: none;
        }

        .event-create-form .form-control::placeholder {
            color: #a0aec0;
            font-size: 15px;
        }

        .event-create-form .form-text,
        .event-create-form .text-muted {
            font-size: 13px;
            margin-top: 8px;
            margin-bottom: 0;
            display: block;
            color: #718096;
            line-height: 1.5;
            padding-left: 4px;
        }

        .event-create-form .alert {
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 14px;
            margin-bottom: 20px;
            border: none;
            line-height: 1.6;
        }

        .event-create-form .alert-danger {
            background-color: #fed7d7;
            color: #c53030;
        }

        .event-create-form .alert-warning {
            background-color: #feebc8;
            color: #c05621;
        }

        .event-create-form .alert-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .event-create-form .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .event-create-form .btn {
            font-size: 17px;
            padding: 16px 24px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            margin-top: 12px;
            transition: all 0.3s ease;
            border: none;
            text-transform: none;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .event-create-form .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
        }

        .event-create-form .btn-primary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .event-create-form .btn-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #ffffff;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);
        }

        .event-create-form .btn-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 2px 8px rgba(245, 87, 108, 0.4);
        }

        .event-create-form .btn-outline-secondary {
            width: 100%;
            padding: 14px 20px;
            font-size: 16px;
            margin-top: 12px;
            margin-left: 0 !important;
            background: #ffffff;
            color: #718096;
            border: 2px solid #e2e8f0;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .event-create-form .btn-outline-secondary:active {
            transform: translateY(2px);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
            background-color: #f7fafc;
        }

        .event-create-form .btn-outline-primary {
            border: 2px solid #667eea;
            color: #667eea;
            background: #ffffff;
            font-weight: 600;
            min-width: 50px;
        }

        .event-create-form .btn-outline-primary:hover {
            background: #667eea;
            color: #ffffff;
        }

        .event-create-form .btn-outline-primary:active {
            transform: translateY(2px);
            background: #5568d3;
        }

        .event-create-form .btn-outline-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .event-create-form #locationStatus {
            margin-top: 12px;
        }

        .event-create-form #locationStatus .alert {
            margin-bottom: 0;
        }

        /* Улучшение datetime-local на мобильных */
        .event-create-form input[type="datetime-local"],
        .event-create-form input[type="number"] {
            min-height: 52px;
            cursor: pointer;
        }

        /* Улучшение иконок */
        .event-create-form .bi {
            margin-right: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        /* Разделители между секциями */
        .event-create-form .mb-3:not(:last-child) {
            position: relative;
        }

        .event-create-form .mb-3:not(:last-child)::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* Улучшение для ошибок */
        .event-create-container>.alert {
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 20px;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(197, 48, 48, 0.15);
        }
    }

    /* Десктоп версия */
    @media (min-width: 768px) {
        .event-create-container {
            max-width: 700px;
            margin: 20px auto;
        }

        .event-create-form .card-body {
            padding: 30px;
        }
    }

    .event-publish-tariff {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 55%, #eef2ff 100%);
        border: 1px solid #c7d2fe;
        border-radius: 16px;
        padding: 18px 16px;
        margin-bottom: 4px;
        box-shadow: 0 8px 22px rgba(79, 70, 229, 0.1);
    }

    .event-publish-tariff h6 {
        margin: 0 0 10px 0;
        color: #1f2937;
        font-weight: 800;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-publish-tariff h6 i {
        color: #6366f1;
    }

    .event-publish-tariff-amount {
        font-size: 28px;
        font-weight: 800;
        color: #4338ca;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .event-publish-tariff-note {
        margin-top: 10px;
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
    }

    .event-publish-tariff-free {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 13px;
        font-weight: 700;
    }

    .event-publish-payment-demo {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed rgba(99, 102, 241, 0.35);
    }

    .event-publish-payment-demo-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .event-publish-payment-demo-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .event-publish-payment-demo-buttons .btn {
        flex: 1 1 140px;
        min-width: 120px;
        margin-top: 0 !important;
        font-weight: 700;
    }

    .event-publish-payment-demo .btn-publish-demo-active {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35);
    }

    #publishPaymentModal .publish-payment-dialog {
        max-width: 440px;
    }

    #publishPaymentModal .publish-payment-modal {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18), 0 8px 16px rgba(79, 70, 229, 0.12);
    }

    #publishPaymentModal .publish-payment-modal-header {
        position: relative;
        padding: 28px 24px 22px;
        text-align: center;
        background: linear-gradient(145deg, #4f46e5 0%, #6366f1 45%, #7c3aed 100%);
        color: #fff;
    }

    #publishPaymentModal .publish-payment-modal-header .btn-close {
        position: absolute;
        top: 14px;
        right: 14px;
        filter: brightness(0) invert(1);
        opacity: 0.85;
    }

    #publishPaymentModal .publish-payment-modal-header .btn-close:hover {
        opacity: 1;
    }

    #publishPaymentModal .publish-payment-modal-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        margin-bottom: 12px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(6px);
        font-size: 1.5rem;
    }

    #publishPaymentModal .publish-payment-modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    #publishPaymentModal .publish-payment-modal-subtitle {
        margin: 6px 0 0;
        font-size: 0.8125rem;
        opacity: 0.9;
        line-height: 1.4;
    }

    #publishPaymentModal .publish-payment-modal-body {
        padding: 22px 20px 8px;
        background: #f8fafc;
    }

    #publishPaymentModal .publish-payment-price-card {
        text-align: center;
        padding: 18px 16px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }

    #publishPaymentModal .publish-payment-price-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
        margin-bottom: 4px;
    }

    #publishPaymentModal .publish-payment-price-value {
        font-size: 2rem;
        font-weight: 800;
        color: #4338ca;
        letter-spacing: -0.03em;
        line-height: 1.1;
    }

    #publishPaymentModal .publish-payment-price-value small {
        font-size: 1.125rem;
        font-weight: 700;
        color: #6366f1;
    }

    #publishPaymentModal .publish-payment-promo {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 5px 12px;
        border-radius: 999px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #047857;
        font-size: 0.75rem;
        font-weight: 700;
    }

    #publishPaymentModal .publish-payment-hint {
        margin: 16px 0 12px;
        font-size: 0.8125rem;
        color: #64748b;
        text-align: center;
        line-height: 1.45;
    }

    #publishPaymentModal .publish-payment-hint i {
        color: #94a3b8;
    }

    #publishPaymentModal .publish-payment-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    @media (max-width: 400px) {
        #publishPaymentModal .publish-payment-options {
            grid-template-columns: 1fr;
        }
    }

    #publishPaymentModal .publish-payment-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        width: 100%;
        padding: 16px 12px;
        border-radius: 14px;
        border: 2px solid #e2e8f0;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
        text-align: center;
    }

    #publishPaymentModal .publish-payment-option:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    #publishPaymentModal .publish-payment-option:disabled {
        opacity: 0.65;
        cursor: wait;
    }

    #publishPaymentModal .publish-payment-option-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        font-size: 1.25rem;
    }

    #publishPaymentModal .publish-payment-option--free .publish-payment-option-icon {
        background: #ecfdf5;
        color: #059669;
    }

    #publishPaymentModal .publish-payment-option--free {
        border-color: #bbf7d0;
    }

    #publishPaymentModal .publish-payment-option--free:hover:not(:disabled) {
        border-color: #34d399;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);
    }

    #publishPaymentModal .publish-payment-option--free.btn-publish-demo-active {
        border-color: #10b981;
        background: #f0fdf4;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
    }

    #publishPaymentModal .publish-payment-option--paid .publish-payment-option-icon {
        background: #eef2ff;
        color: #4f46e5;
    }

    #publishPaymentModal .publish-payment-option--paid {
        border-color: #c7d2fe;
    }

    #publishPaymentModal .publish-payment-option--paid:hover:not(:disabled) {
        border-color: #818cf8;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.18);
    }

    #publishPaymentModal .publish-payment-option--paid.btn-publish-demo-active {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3);
    }

    #publishPaymentModal .publish-payment-option-title {
        font-size: 0.9375rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
    }

    #publishPaymentModal .publish-payment-option-desc {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #94a3b8;
        line-height: 1.3;
    }

    #publishPaymentModal .publish-payment-option-tag {
        margin-top: 2px;
        padding: 2px 8px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    #publishPaymentModal #publishPaymentDemoStatus {
        min-height: 0;
    }

    #publishPaymentModal #publishPaymentDemoStatus .alert {
        border-radius: 10px;
        margin-top: 12px;
        margin-bottom: 0;
        font-size: 0.8125rem;
    }

    #publishPaymentModal .publish-payment-modal-footer {
        justify-content: center;
        padding: 12px 20px 18px;
        border-top: none;
        background: #f8fafc;
    }

    #publishPaymentModal .publish-payment-cancel-btn {
        padding: 8px 20px;
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 10px;
        transition: color 0.15s, background 0.15s;
    }

    #publishPaymentModal .publish-payment-cancel-btn:hover {
        color: #334155;
        background: #e2e8f0;
    }

    /* Превью баннера — пропорции как у карточки мероприятия в ленте (events/index) */
    #photo-preview {
        position: relative;
        width: 100%;
    }

    #photo-preview .event-photo-preview-label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    #photo-preview .event-photo-preview-icon-ok {
        color: #22c55e;
        display: none;
    }

    #photo-preview .event-photo-preview-icon-empty {
        color: #9ca3af;
    }

    #photo-preview.has-image .event-photo-preview-icon-ok {
        display: inline-block;
    }

    #photo-preview.has-image .event-photo-preview-icon-empty {
        display: none;
    }

    /* Общий слот баннера: одна пропорция для всех превью (как .event-banner в ленте) */
    .event-banner-preview-wrap {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
        padding: 8px 8px 0;
        background: #f0f2f5;
        border-radius: 12px;
        border: 1px solid rgba(11, 20, 26, 0.06);
        box-sizing: border-box;
    }

    .event-banner-slot {
        position: relative;
        width: 100%;
        aspect-ratio: <?= (int) $eventBannerAspectW ?> / <?= (int) $eventBannerAspectH ?>;
        overflow: hidden;
        border-radius: 8px;
        background: #eceff1;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.12);
    }

    .event-banner-slot>img {
        width: 100%;
        height: 100%;
        object-fit: fill;
        display: none;
        border-radius: 7.5px;
    }

    #photo-preview.has-image .event-banner-slot>img,
    .event-live-demo.has-image .event-banner-slot>img {
        display: block;
    }

    #photo-preview .event-photo-preview-frame.event-banner-slot {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 3px dashed #cbd5e1;
        transition: border-color 0.2s ease;
    }

    #photo-preview.has-image .event-photo-preview-frame.event-banner-slot {
        border: 3px solid #22c55e;
        background: #eceff1;
    }

    #photo-preview .event-photo-preview-placeholder {
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

    #photo-preview.has-image .event-photo-preview-placeholder,
    .event-live-demo.has-image .event-live-demo-placeholder {
        display: none;
    }

    #photo-preview .event-photo-preview-placeholder i {
        font-size: 36px;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    #photo-preview .event-photo-preview-hint {
        margin-top: 10px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
    }

    /* Макет «как на главной» — карточка мероприятия в ленте */
    .event-live-demo {
        margin-top: 20px;
        padding: 16px 14px 18px;
        background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 45%, #eceff1 100%);
        border-radius: 16px;
        border: 1px solid rgba(102, 126, 234, 0.2);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
    }

    .event-live-demo-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-live-demo-title i {
        color: #6366f1;
    }

    .event-live-demo-sub {
        font-size: 12px;
        color: #64748b;
        margin: 0 0 14px 0;
        line-height: 1.45;
    }

    .event-live-demo-chrome {
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

    .event-live-demo-chrome span:last-child {
        opacity: 0.9;
        font-weight: 500;
        font-size: 18px;
    }

    .event-live-demo-body {
        background: #f0f2f5;
        padding: 10px 8px 14px;
        border-radius: 0 0 14px 14px;
        border: 1px solid rgba(11, 20, 26, 0.06);
        border-top: none;
    }

    .event-live-demo-mock-card {
        background: #f0f2f5;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.08);
        border: 1px solid rgba(11, 20, 26, 0.06);
        width: 100%;
        max-width: 420px;
        margin-left: auto;
        margin-right: auto;
    }

    .event-banner-slot .event-live-demo-placeholder {
        position: absolute;
        inset: 0;
        border-radius: 8px;
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
        pointer-events: none;
    }

    .event-live-demo-placeholder i {
        font-size: 48px;
        margin-bottom: 8px;
        color: #94a3b8;
        opacity: 0.95;
    }

    .event-live-demo-mock-content {
        margin: 8px;
        padding: 12px 12px 14px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 0.5px rgba(11, 20, 26, 0.06);
    }

    .event-live-demo-mock-title {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 10px 0;
        line-height: 1.35;
    }

    .event-live-demo-mock-time {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #6b7280;
    }

    .event-live-demo-mock-time i {
        font-size: 16px;
        color: #667eea;
    }

    .event-live-demo-note {
        margin-top: 10px;
        font-size: 11px;
        color: #64748b;
        text-align: center;
        line-height: 1.4;
    }

    /* Модальное окно обрезки (как в ads/create) */
    .event-cropper-modal-overlay {
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

    .event-cropper-modal-content {
        background: #fff;
        border-radius: 12px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .event-cropper-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .event-cropper-modal-header h5 {
        margin: 0;
        font-weight: 600;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-cropper-modal-header h5 i {
        color: #667eea;
    }

    .event-cropper-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        line-height: 1;
    }

    .event-cropper-modal-close:hover {
        color: #1a1a1a;
    }

    .event-cropper-modal-body {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        max-height: 70vh;
    }

    .event-cropper-container-wrapper {
        width: 100%;
        min-height: 300px;
        max-height: 50vh;
        background: #f3f4f6;
        border-radius: 8px;
    }

    .event-cropper-container-wrapper img {
        max-width: 100%;
        max-height: 50vh;
        display: block;
    }

    .event-cropper-info {
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

    .event-cropper-preview-section {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        width: 100%;
    }

    .event-cropper-preview-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 10px;
    }

    .event-cropper-preview-banner {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
        aspect-ratio: <?= (int) $eventBannerRatioW ?> / <?= (int) $eventBannerRatioH ?>;
        height: auto;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        border: 3px solid #667eea;
    }

    .event-cropper-preview-banner #eventCropperPreview {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .event-cropper-preview-banner #eventCropperPreview img {
        width: 100%;
        height: 100%;
        object-fit: fill;
    }

    .event-cropper-preview-size {
        margin-top: 10px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
    }

    .event-cropper-controls {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .event-cropper-controls .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .event-cropper-modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .event-cropper-btn-cancel {
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        color: #374151;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
    }

    .event-cropper-btn-apply {
        background: #667eea;
        border: none;
        color: #fff;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
    }

    .event-photo-btn-edit {
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

    #photo-preview.has-image .event-photo-btn-edit {
        display: flex;
    }

    .event-photo-preview-container,
    .event-live-demo-mock-card {
        width: 100%;
    }

    .event-photo-btn-edit:hover {
        background: rgba(86, 104, 211, 0.95);
        transform: translateY(-2px);
    }
</style>

<div class="event-create-container mt-4">
    <h2 class="mb-4">Создать мероприятие</h2>

    <!-- <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        <strong>Важно:</strong> После создания мероприятие будет отправлено на модерацию администратору или менеджеру.
        Вы получите уведомление после проверки.
        <span class="d-block mt-2">
            Размещение на платформе: <strong><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</strong> за мероприятие.
            <?php if (!$eventPublishPaid): ?>
                <strong>Сейчас — без оплаты</strong> (тариф для будущего подключения оплаты).
            <?php else: ?>
                Оплата включена в настройках сайта — после подключения платёжного шага создание станет платным.
            <?php endif; ?>
        </span>
    </div> -->

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= Helper::escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= Helper::escape($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>events/store" class="event-create-form" enctype="multipart/form-data">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Название мероприятия *</label>
                    <input type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        required
                        placeholder="Например: Концерт в парке">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание *</label>
                    <textarea class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                        required
                        placeholder="Опишите ваше мероприятие..."></textarea>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Баннер мероприятия</label>
                    <input type="file"
                        class="form-control"
                        id="photo"
                        name="photo"
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <input type="hidden" name="photo_cropped_base64" id="photo_cropped_base64" value="">

                    <div id="photo-preview" class="mt-3">
                        <div class="event-photo-preview-container">
                            <div class="event-photo-preview-label">
                                <i class="bi bi-check-circle-fill event-photo-preview-icon-ok"></i>
                                <i class="bi bi-calendar2-event event-photo-preview-icon-empty"></i>
                                <span>Так будет выглядеть баннер в карточке:</span>
                            </div>
                            <div class="event-banner-preview-wrap">
                                <div class="event-banner-slot event-photo-preview-frame">
                                    <img id="photo-preview-img" src="" alt="Предпросмотр баннера">
                                    <div class="event-photo-preview-placeholder">
                                        <i class="bi bi-image"></i>
                                        <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">Здесь появится ваш баннер</div>
                                        <div style="font-size: 12px; max-width: 340px;">Обрезка под пропорции карточки в ленте (<?= (int) $eventBannerAspectW ?>:<?= (int) $eventBannerAspectH ?>)</div>
                                    </div>
                                    <button type="button" class="event-photo-btn-edit" id="eventPhotoEditBtn">
                                        <i class="bi bi-pencil-square"></i> Редактировать
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- <div class="event-live-demo" id="eventLiveDemo" aria-label="Превью карточки мероприятия в ленте">
                        <div class="event-live-demo-title">
                            <i class="bi bi-phone"></i>
                            Как это увидят пользователи на главной
                        </div>
                        <p class="event-live-demo-sub">Макет ленты: сверху баннер, под ним — название и дата, как в разделе мероприятий после публикации.</p>

                        <div class="event-live-demo-chrome">
                            <span>Aru</span>
                            <span aria-hidden="true"><i class="bi bi-person-circle"></i></span>
                        </div>
                        <div class="event-live-demo-body">
                            <div class="event-live-demo-mock-card">
                                <div class="event-banner-slot event-live-demo-mock-banner">
                                    <img id="eventLiveDemoImg" src="" alt="Баннер в ленте">
                                    <div class="event-live-demo-placeholder" id="eventLiveDemoPlaceholder">
                                        <i class="bi bi-calendar-event"></i>
                                        <div>Ваш баннер — здесь</div>
                                        <div style="font-size: 11px; font-weight: 500; margin-top: 4px; opacity: 0.9;">Загрузите изображение выше</div>
                                    </div>
                                </div>
                                <div class="event-live-demo-mock-content">
                                    <div class="event-live-demo-mock-title" id="eventLiveDemoTitle">Название мероприятия</div>
                                    <div class="event-live-demo-mock-time">
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        <span id="eventLiveDemoTimeSpan">Укажите дату и время проведения ниже</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="event-live-demo-note">Окончательный вид после модерации; в ленте также может отображаться город из адреса.</p>
                    </div> -->
                </div>

                <div class="mb-3">
                    <label for="event_date" class="form-label">Дата и время проведения *</label>
                    <input type="datetime-local"
                        class="form-control"
                        id="event_date"
                        name="event_date"
                        max="<?= date('Y') ?>-12-31T23:59"
                        required>
                </div>

                <div class="mb-3" style="position: relative;">
                    <label for="city" class="form-label">Город *</label>
                    <!-- Скрытое поле для отправки формы -->
                    <input type="text"
                        class="form-control"
                        id="city"
                        name="city"
                        required
                        autocomplete="off"
                        style="position: absolute; left: -9999px; opacity: 0; pointer-events: none; width: 0; height: 0; padding: 0; border: none; overflow: hidden;">

                    <div class="city-location-container">
                        <button type="button"
                            class="btn btn-outline-primary city-location-btn"
                            id="detectLocationBtn"
                            onclick="detectLocation()"
                            style="width: 100%; padding: 16px 20px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2); transition: all 0.3s ease; font-weight: 600;">
                            <i class="bi bi-geo-alt-fill" style="font-size: 18px;"></i>
                            <span>Геолокация</span>
                        </button>
                        <!-- <small class="city-location-hint" style="display: block; margin-top: 10px; padding: 12px 16px; background: #f7fafc; border-radius: 10px; color: #4a5568; font-size: 13px; line-height: 1.5; border-left: 3px solid #667eea;">
                            <i class="bi bi-info-circle" style="color: #667eea; margin-right: 8px;"></i>
                            <strong>Зачем нужна геолокация?</strong> Мы автоматически определим ваш город, чтобы другие пользователи могли найти ваше мероприятие. Это поможет участникам быстрее найти события в их регионе.
                        </small> -->
                    </div>

                    <div id="city-suggestions" class="list-group" style="position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; display: none; margin-top: 8px; width: 100%; border-radius: 12px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); border: 1px solid #e2e8f0; background: white;"></div>
                    <div id="city-selected-info" class="city-selected-info" style="display: none; margin-top: 12px; padding: 12px 16px; background: linear-gradient(135deg, #f0f4ff 0%, #e8edff 100%); border-radius: 12px; border-left: 4px solid #667eea; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1); animation: fadeIn 0.3s ease;">
                        <div class="d-flex align-items-center" style="gap: 10px;">
                            <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
                                <i class="bi bi-geo-alt-fill" style="color: white; font-size: 16px;"></i>
                            </div>
                            <div style="flex: 1;">
                                <small class="text-muted" style="display: block; font-size: 12px; margin-bottom: 2px; color: #718096;">Выбранный город:</small>
                                <strong id="city-selected-text" style="color: #667eea; font-size: 15px; font-weight: 600;"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3" style="position: relative;">
                    <label for="street" class="form-label">Улица *</label>
                    <input type="text"
                        class="form-control"
                        id="street"
                        name="street"
                        required
                        autocomplete="off"
                        placeholder="Сначала выберите город, затем выберите улицу из списка"
                        readonly>
                    <div id="street-suggestions" class="list-group" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; display: none; margin-top: 2px; width: 100%;"></div>
                    <small class="form-text text-muted d-block mt-2">
                        Сначала выберите город. После выбора города появится список всех улиц для выбора.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="house_number" class="form-label">Номер дома *</label>
                    <input type="text"
                        class="form-control"
                        id="house_number"
                        name="house_number"
                        required
                        placeholder="Например: 150">
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">Дополнительная информация о месте</label>
                    <input type="text"
                        class="form-control"
                        id="location"
                        name="location"
                        placeholder="Например: Парк Горького, вход со стороны главной аллеи">
                    <small class="form-text text-muted d-block mt-2">
                        Укажите дополнительные детали, если нужно (название парка, здания и т.д.)
                    </small>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Цена <span id="currency-symbol">₸</span></label>
                    <input type="number"
                        class="form-control"
                        id="price"
                        name="price"
                        value=""
                        min="0"
                        step="100">
                    <small class="text-muted">Укажите 0 если мероприятие бесплатное</small>
                    <input type="hidden" id="currency_code" name="currency_code" value="KZT">
                </div>

                <div class="event-publish-tariff mb-3 d-none">
                    <h6><i class="bi bi-wallet2"></i> Оплата размещения на платформе</h6>
                    <div class="event-publish-tariff-amount"><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</div>
                    <p class="event-publish-tariff-note mb-0">
                        Это отдельная плата за публикацию мероприятия в ленте Aru (не путать с ценой билета для участников выше).
                    </p>
                    <?php if (!$eventPublishPaid): ?>
                        <div class="event-publish-tariff-free">
                            <i class="bi bi-gift-fill"></i> Сейчас оформление <strong>бесплатное</strong> — списания нет
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                            Включена оплата в конфигурации. До подключения платёжного шага создание может быть недоступно — см. администратора.
                        </div>
                    <?php endif; ?>
                    <p class="event-publish-tariff-note mb-0 mt-2">
                        <i class="bi bi-info-circle"></i> Вариант оплаты размещения вы выберете в окне после нажатия «Создать мероприятие».
                    </p>
                    <input type="hidden" name="publish_payment_demo" id="publish_payment_demo" value="">
                </div>

                <div class="mb-3">
                    <label class="form-label">Проверка адреса</label>
                    <button type="button" class="btn btn-secondary" onclick="geocodeAddress()">
                        <i class="bi bi-geo-alt"></i> Найти адрес на карте
                    </button>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <div id="locationStatus" class="mt-2"></div>
                    <small class="form-text text-muted d-block mt-2">
                        Нажмите кнопку для проверки адреса и получения координат. Это необходимо для отображения мероприятия на карте.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" id="event-create-submit-btn">
                    <?php if (!$eventPublishPaid): ?>
                        <i class="bi bi-gift"></i> Создать мероприятие (сейчас бесплатно)
                    <?php else: ?>
                        <i class="bi bi-check-circle"></i> Создать мероприятие
                    <?php endif; ?>
                </button>
                <a href="<?= BASE_URL ?>events" class="btn btn-outline-secondary btn-lg">
                    Отмена
                </a>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="publishPaymentModal" tabindex="-1" aria-labelledby="publishPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered publish-payment-dialog">
        <div class="modal-content publish-payment-modal">
            <div class="publish-payment-modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                <div class="publish-payment-modal-badge" aria-hidden="true">
                    <i class="bi bi-wallet2"></i>
                </div>
                <h5 class="publish-payment-modal-title" id="publishPaymentModalLabel">Оплата размещения</h5>
                <p class="publish-payment-modal-subtitle">Подтвердите публикацию мероприятия в ленте Aru</p>
            </div>
            <div class="modal-body publish-payment-modal-body">
                <div class="publish-payment-price-card">
                    <span class="publish-payment-price-label">Тариф размещения</span>
                    <div class="publish-payment-price-value">
                        <?= number_format($eventPublishPrice, 0, ',', ' ') ?> <small>₸</small>
                    </div>
                    <?php if (!$eventPublishPaid): ?>
                        <div class="publish-payment-promo">
                            <i class="bi bi-gift-fill"></i> Сейчас оформление бесплатное
                        </div>
                    <?php endif; ?>
                </div>
                <p class="publish-payment-hint">
                    <i class="bi bi-shield-check"></i>
                    Демо-режим: реального списания с карты не будет
                </p>
                <div class="publish-payment-options">
                    <button type="button"
                        class="publish-payment-option publish-payment-option--free"
                        id="publishDemoFreeBtn"
                        title="Демо: размещение без оплаты">
                        <span class="publish-payment-option-icon"><i class="bi bi-gift"></i></span>
                        <span class="publish-payment-option-title">Бесплатно</span>
                        <span class="publish-payment-option-desc">Разместить без оплаты</span>
                    </button>
                    <button type="button"
                        class="publish-payment-option publish-payment-option--paid"
                        id="publishDemoPaidBtn"
                        data-demo-amount="<?= (int) $eventPublishPrice ?>"
                        title="Демо: условная оплата тарифа">
                        <span class="publish-payment-option-icon"><i class="bi bi-credit-card"></i></span>
                        <span class="publish-payment-option-title"><?= number_format($eventPublishPrice, 0, ',', ' ') ?> ₸</span>
                        <span class="publish-payment-option-desc">Условная оплата</span>
                        <span class="publish-payment-option-tag">демо</span>
                    </button>
                </div>
                <div id="publishPaymentDemoStatus"></div>
            </div>
            <div class="modal-footer publish-payment-modal-footer">
                <button type="button" class="publish-payment-cancel-btn" data-bs-dismiss="modal">
                    Вернуться к форме
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Редактор обрезки баннера мероприятия -->
<div class="event-cropper-modal-overlay" id="eventCropperModal">
    <div class="event-cropper-modal-content">
        <div class="event-cropper-modal-header">
            <h5><i class="bi bi-crop"></i> Обрезка баннера</h5>
            <div class="event-cropper-info">
                <i class="bi bi-arrows-move"></i> Перетащите и измените рамку
            </div>
            <button type="button" class="event-cropper-modal-close" id="eventCropperClose" aria-label="Закрыть">&times;</button>
        </div>
        <div class="event-cropper-modal-body">
            <div class="event-cropper-container-wrapper">
                <img id="eventCropperImage" src="" alt="Изображение для обрезки">
            </div>
            <div class="event-cropper-preview-section">
                <div class="event-cropper-preview-label">Предпросмотр (пропорции карточки в ленте):</div>
                <div class="event-cropper-preview-banner">
                    <div id="eventCropperPreview"></div>
                </div>
                <div class="event-cropper-preview-size" id="eventCropperSize">Размер: -- x -- px</div>
            </div>
            <div class="event-cropper-controls">
                <button type="button" class="btn btn-outline-secondary" id="eventCropperRotateLeft">
                    <i class="bi bi-arrow-counterclockwise"></i> Влево
                </button>
                <button type="button" class="btn btn-outline-secondary" id="eventCropperRotateRight">
                    <i class="bi bi-arrow-clockwise"></i> Вправо
                </button>
                <button type="button" class="btn btn-outline-secondary" id="eventCropperFlipH">
                    <i class="bi bi-symmetry-vertical"></i> Отразить
                </button>
                <button type="button" class="btn btn-outline-secondary" id="eventCropperReset">
                    <i class="bi bi-arrow-repeat"></i> Сбросить
                </button>
            </div>
        </div>
        <div class="event-cropper-modal-footer">
            <button type="button" class="event-cropper-btn-cancel" id="eventCropperCancel">Отмена</button>
            <button type="button" class="event-cropper-btn-apply" id="eventCropperApply">
                <i class="bi bi-check-lg"></i> Применить
            </button>
        </div>
    </div>
</div>

<style>
    /* Стили для автодополнения */
    #city-suggestions,
    #street-suggestions {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
        background: white;
    }

    .list-group-item {
        padding: 12px 16px;
        cursor: pointer;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        background: white;
        transition: background 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .list-group-item:hover,
    .list-group-item.active {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #667eea;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    .suggestion-text {
        font-weight: 500;
        color: #2d3748;
        flex: 1;
    }

    .suggestion-type {
        font-size: 11px;
        color: #718096;
        margin-left: 8px;
        white-space: nowrap;
    }

    @media (max-width: 767px) {
        .list-group-item {
            padding: 10px 12px;
            font-size: 14px;
        }

        .suggestion-type {
            font-size: 10px;
        }
    }

    /* Стили для кнопки геолокации */
    .city-location-btn {
        border: 2px solid #667eea !important;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%) !important;
        color: #667eea !important;
        position: relative;
        overflow: hidden;
    }

    .city-location-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transition: left 0.3s ease;
        z-index: 0;
    }

    .city-location-btn:hover::before {
        left: 0;
    }

    .city-location-btn:hover {
        border-color: #667eea !important;
        color: #ffffff !important;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4) !important;
        transform: translateY(-2px);
    }

    .city-location-btn i,
    .city-location-btn span {
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .city-location-btn:hover i {
        transform: scale(1.1);
    }

    .city-location-btn span {
        font-weight: 600;
        font-size: 16px;
    }

    .city-location-btn:active {
        transform: translateY(0) !important;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
    }

    .city-location-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Контейнер для геолокации */
    .city-location-container {
        position: relative;
    }

    /* Стили для информационной подсказки */
    .city-location-hint {
        display: block;
    }

    /* Анимация появления информационного блока */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .city-selected-info {
        animation: fadeIn 0.3s ease;
    }

    /* Адаптивные стили для кнопки геолокации */
    @media (max-width: 767px) {
        .city-location-btn {
            padding: 14px 16px !important;
        }

        .city-location-btn span {
            font-size: 15px;
        }

        .city-location-btn i {
            font-size: 16px !important;
        }

        .city-location-hint {
            font-size: 12px !important;
            padding: 10px 14px !important;
        }
    }
</style>

<script>
    let citySuggestionsTimeout;
    let streetSuggestionsTimeout;
    let selectedCity = '';
    let selectedCityData = null; // Хранит данные о выбранном городе (название, страна)

    /**
     * Экранирование HTML для безопасного отображения
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Определение валюты по стране
     */
    function getCurrencyByCountry(country, countryCode) {
        const currencyMap = {
            // Казахстан
            'KZ': {
                symbol: '₸',
                code: 'KZT',
                name: 'тенге'
            },
            'Казахстан': {
                symbol: '₸',
                code: 'KZT',
                name: 'тенге'
            },
            'Kazakhstan': {
                symbol: '₸',
                code: 'KZT',
                name: 'тенге'
            },

            // Россия
            'RU': {
                symbol: '₽',
                code: 'RUB',
                name: 'рублей'
            },
            'Россия': {
                symbol: '₽',
                code: 'RUB',
                name: 'рублей'
            },
            'Russia': {
                symbol: '₽',
                code: 'RUB',
                name: 'рублей'
            },
            'Российская Федерация': {
                symbol: '₽',
                code: 'RUB',
                name: 'рублей'
            },

            // Беларусь
            'BY': {
                symbol: 'Br',
                code: 'BYN',
                name: 'белорусских рублей'
            },
            'Беларусь': {
                symbol: 'Br',
                code: 'BYN',
                name: 'белорусских рублей'
            },
            'Belarus': {
                symbol: 'Br',
                code: 'BYN',
                name: 'белорусских рублей'
            },

            // Украина
            'UA': {
                symbol: '₴',
                code: 'UAH',
                name: 'гривен'
            },
            'Украина': {
                symbol: '₴',
                code: 'UAH',
                name: 'гривен'
            },
            'Ukraine': {
                symbol: '₴',
                code: 'UAH',
                name: 'гривен'
            },

            // США
            'US': {
                symbol: '$',
                code: 'USD',
                name: 'долларов'
            },
            'США': {
                symbol: '$',
                code: 'USD',
                name: 'долларов'
            },
            'United States': {
                symbol: '$',
                code: 'USD',
                name: 'долларов'
            },

            // Европа (EUR)
            'DE': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Германия
            'FR': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Франция
            'IT': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Италия
            'ES': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Испания
            'NL': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Нидерланды
            'BE': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Бельгия
            'AT': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Австрия
            'PT': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Португалия
            'GR': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Греция
            'IE': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Ирландия
            'FI': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            }, // Финляндия
            'Германия': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Франция': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Италия': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Испания': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Germany': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'France': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Italy': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },
            'Spain': {
                symbol: '€',
                code: 'EUR',
                name: 'евро'
            },

            // Великобритания
            'GB': {
                symbol: '£',
                code: 'GBP',
                name: 'фунтов стерлингов'
            },
            'Великобритания': {
                symbol: '£',
                code: 'GBP',
                name: 'фунтов стерлингов'
            },
            'United Kingdom': {
                symbol: '£',
                code: 'GBP',
                name: 'фунтов стерлингов'
            },

            // Китай
            'CN': {
                symbol: '¥',
                code: 'CNY',
                name: 'юаней'
            },
            'Китай': {
                symbol: '¥',
                code: 'CNY',
                name: 'юаней'
            },
            'China': {
                symbol: '¥',
                code: 'CNY',
                name: 'юаней'
            },

            // Япония
            'JP': {
                symbol: '¥',
                code: 'JPY',
                name: 'иен'
            },
            'Япония': {
                symbol: '¥',
                code: 'JPY',
                name: 'иен'
            },
            'Japan': {
                symbol: '¥',
                code: 'JPY',
                name: 'иен'
            },

            // Турция
            'TR': {
                symbol: '₺',
                code: 'TRY',
                name: 'турецких лир'
            },
            'Турция': {
                symbol: '₺',
                code: 'TRY',
                name: 'турецких лир'
            },
            'Turkey': {
                symbol: '₺',
                code: 'TRY',
                name: 'турецких лир'
            },

            // Кыргызстан
            'KG': {
                symbol: 'сом',
                code: 'KGS',
                name: 'сом'
            },
            'Кыргызстан': {
                symbol: 'сом',
                code: 'KGS',
                name: 'сом'
            },
            'Kyrgyzstan': {
                symbol: 'сом',
                code: 'KGS',
                name: 'сом'
            },

            // Узбекистан
            'UZ': {
                symbol: 'сум',
                code: 'UZS',
                name: 'сум'
            },
            'Узбекистан': {
                symbol: 'сум',
                code: 'UZS',
                name: 'сум'
            },
            'Uzbekistan': {
                symbol: 'сум',
                code: 'UZS',
                name: 'сум'
            },
        };

        // Сначала проверяем по коду страны
        if (countryCode && currencyMap[countryCode.toUpperCase()]) {
            return currencyMap[countryCode.toUpperCase()];
        }

        // Затем проверяем по названию страны
        if (country) {
            const countryUpper = country.trim();
            if (currencyMap[countryUpper]) {
                return currencyMap[countryUpper];
            }
        }

        // По умолчанию возвращаем тенге (Казахстан)
        return {
            symbol: '₸',
            code: 'KZT',
            name: 'тенге'
        };
    }

    /**
     * Обновление валюты в поле цены
     */
    function updateCurrency(cityData) {
        if (!cityData) {
            // Если данных о городе нет, используем валюту по умолчанию
            const defaultCurrency = {
                symbol: '₸',
                code: 'KZT',
                name: 'тенге'
            };
            document.getElementById('currency-symbol').textContent = defaultCurrency.symbol;
            document.getElementById('currency_code').value = defaultCurrency.code;
            return;
        }

        const currency = getCurrencyByCountry(cityData.country, cityData.countryCode);
        document.getElementById('currency-symbol').textContent = currency.symbol;
        document.getElementById('currency_code').value = currency.code;
    }

    /**
     * Обновление информации о выбранном городе и стране
     */
    function updateCitySelectedInfo(cityData) {
        const cityInfoEl = document.getElementById('city-selected-info');
        const cityTextEl = document.getElementById('city-selected-text');

        if (!cityData || !cityData.country) {
            if (cityInfoEl) {
                cityInfoEl.style.display = 'none';
            }
            return;
        }

        if (cityInfoEl && cityTextEl) {
            const cityName = cityData.name || '';
            const countryName = cityData.country || '';
            cityTextEl.textContent = cityName + ', ' + countryName;
            cityInfoEl.style.display = 'block';
        }
    }

    /**
     * Определение города по геолокации (ручной запрос)
     */
    window.detectLocation = function() {
        const cityInput = document.getElementById('city');
        const detectBtn = document.getElementById('detectLocationBtn');
        const locationStatus = document.getElementById('locationStatus');

        if (!navigator.geolocation) {
            if (locationStatus) {
                locationStatus.innerHTML =
                    '<div class="alert alert-warning">' +
                    '<i class="bi bi-exclamation-triangle"></i> Геолокация не поддерживается вашим браузером' +
                    '</div>';
            }
            return;
        }

        // Показываем индикатор загрузки
        if (detectBtn) {
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        }

        // Не показываем сообщение в locationStatus, если это не блок для геолокации
        // Вместо этого показываем в консоли или используем другой элемент

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                    try {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`;

                        const response = await fetch(url, {
                            headers: {
                                'User-Agent': 'Tanisu App'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const address = data.address || {};

                            // Получаем название города
                            const cityName = address.city ||
                                address.town ||
                                address.village ||
                                address.municipality ||
                                address.county ||
                                '';

                            const country = address.country || '';
                            const countryCode = address.country_code || '';

                            if (cityName && cityInput) {
                                // Заполняем поле города
                                cityInput.value = cityName;
                                selectedCity = cityName;

                                // Создаем объект с данными о городе
                                selectedCityData = {
                                    name: cityName,
                                    country: country,
                                    countryCode: countryCode,
                                    lat: lat,
                                    lon: lon
                                };

                                // Обновляем валюту
                                updateCurrency(selectedCityData);

                                // Показываем информацию о выбранном городе
                                updateCitySelectedInfo(selectedCityData);

                                // Автоматически загружаем улицы для найденного города
                                loadCityStreets(cityName, selectedCityData);

                                // Устанавливаем координаты
                                const latInput = document.getElementById('latitude');
                                const lonInput = document.getElementById('longitude');
                                if (latInput) {
                                    latInput.value = lat;
                                }
                                if (lonInput) {
                                    lonInput.value = lon;
                                }

                                // Показываем уведомление только если locationStatus существует и пустой
                                if (locationStatus && !locationStatus.innerHTML.trim()) {
                                    locationStatus.innerHTML =
                                        '<div class="alert alert-success">' +
                                        '<i class="bi bi-check-circle"></i> Город определен по геолокации!<br>' +
                                        '<small>Город: ' + escapeHtml(cityName) + (country ? ', ' + escapeHtml(country) : '') + '</small><br>' +
                                        '<small>Выберите улицу из списка ниже</small>' +
                                        '</div>';
                                }
                            } else {
                                // Город в ответе Nominatim не распознан — при наличии страны всё равно выставляем валюту (как при авто-геолокации)
                                if (country || countryCode) {
                                    updateCurrency({
                                        country: country,
                                        countryCode: countryCode
                                    });
                                }
                                // Если город не найден, но поле уже заполнено, просто обновляем координаты
                                if (cityInput && cityInput.value.trim()) {
                                    const latInput = document.getElementById('latitude');
                                    const lonInput = document.getElementById('longitude');
                                    if (latInput) latInput.value = lat;
                                    if (lonInput) lonInput.value = lon;

                                    if (locationStatus && !locationStatus.innerHTML.trim()) {
                                        const currencyHint = (country || countryCode) ?
                                            '<br><small>Валюта цены обновлена по стране' + (country ? ': ' + escapeHtml(country) : '') + '</small>' : '';
                                        locationStatus.innerHTML =
                                            '<div class="alert alert-info">' +
                                            '<i class="bi bi-info-circle"></i> Координаты обновлены по геолокации' +
                                            currencyHint +
                                            '</div>';
                                    }
                                } else {
                                    // Только если город не заполнен, показываем предупреждение
                                    if (locationStatus && !locationStatus.innerHTML.trim()) {
                                        const currencyHint = (country || countryCode) ?
                                            '<br><small>Валюта цены установлена по стране' + (country ? ': ' + escapeHtml(country) : '') + '</small>' : '';
                                        locationStatus.innerHTML =
                                            '<div class="alert alert-warning">' +
                                            '<i class="bi bi-exclamation-triangle"></i> Не удалось определить город по геолокации. Введите город вручную.' +
                                            currencyHint +
                                            '</div>';
                                    }
                                }
                            }
                        } else {
                            // Если запрос не удался, но город уже заполнен, просто обновляем координаты
                            if (cityInput && cityInput.value.trim()) {
                                const latInput = document.getElementById('latitude');
                                const lonInput = document.getElementById('longitude');
                                if (latInput) latInput.value = position.coords.latitude;
                                if (lonInput) lonInput.value = position.coords.longitude;
                            } else {
                                throw new Error('Ошибка запроса к сервису геокодирования');
                            }
                        }
                    } catch (error) {
                        console.error('Ошибка определения города:', error);
                        // Не показываем ошибку, если город уже заполнен
                        if (cityInput && !cityInput.value.trim() && locationStatus && !locationStatus.innerHTML.trim()) {
                            locationStatus.innerHTML =
                                '<div class="alert alert-warning">' +
                                '<i class="bi bi-exclamation-triangle"></i> Не удалось определить город. Введите город вручную или используйте кнопку "Найти адрес на карте".' +
                                '</div>';
                        }
                    } finally {
                        if (detectBtn) {
                            detectBtn.disabled = false;
                            detectBtn.innerHTML = '<i class="bi bi-geo-alt"></i>';
                        }
                    }
                },
                function(error) {
                    console.error('Ошибка геолокации:', error);
                    // Не показываем ошибку, если город уже заполнен
                    if (cityInput && !cityInput.value.trim() && locationStatus && !locationStatus.innerHTML.trim()) {
                        let errorMessage = 'Не удалось определить местоположение. ';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Разрешите доступ к геолокации в настройках браузера.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Информация о местоположении недоступна.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Превышено время ожидания.';
                                break;
                            default:
                                errorMessage += 'Введите город вручную.';
                                break;
                        }

                        locationStatus.innerHTML =
                            '<div class="alert alert-warning">' +
                            '<i class="bi bi-exclamation-triangle"></i> ' + errorMessage +
                            '</div>';
                    }

                    if (detectBtn) {
                        detectBtn.disabled = false;
                        detectBtn.innerHTML = '<i class="bi bi-geo-alt"></i>';
                    }
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
        );
    };

    /**
     * Автодополнение для городов (поиск по всему миру через Nominatim)
     */
    async function searchCities(query) {
        if (query.length < 2) {
            return [];
        }

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&featuretype=city&addressdetails=1`;

            const response = await fetch(url, {
                headers: {
                    'User-Agent': 'Tanisu App'
                }
            });

            if (!response.ok) {
                return [];
            }

            const data = await response.json();

            // Фильтруем и форматируем результаты
            const cities = [];
            const seen = new Set();

            for (const item of data) {
                const address = item.address || {};
                const cityName = address.city ||
                    address.town ||
                    address.village ||
                    address.municipality ||
                    address.county ||
                    item.display_name.split(',')[0];

                const country = address.country || '';
                const countryCode = address.country_code || '';

                // Создаем уникальный ключ
                const key = `${cityName}, ${country}`.toLowerCase();

                if (cityName && !seen.has(key)) {
                    seen.add(key);
                    cities.push({
                        name: cityName,
                        country: country,
                        countryCode: countryCode,
                        displayName: item.display_name,
                        bbox: item.boundingbox || item.bbox,
                        lat: item.lat,
                        lon: item.lon
                    });
                }
            }

            return cities;
        } catch (error) {
            console.error('Ошибка поиска городов:', error);
            return [];
        }
    }

    /**
     * Автодополнение для городов
     */
    function initCityAutocomplete() {
        const cityInput = document.getElementById('city');
        const citySuggestions = document.getElementById('city-suggestions');

        if (!cityInput || !citySuggestions) return;

        // Устанавливаем ширину выпадающего списка равной ширине поля ввода
        function updateSuggestionsWidth() {
            // Учитываем, что поле города теперь в контейнере с кнопкой
            const cityContainer = cityInput.parentElement;
            if (cityContainer) {
                citySuggestions.style.width = cityContainer.offsetWidth + 'px';
            } else {
                citySuggestions.style.width = cityInput.offsetWidth + 'px';
            }
        }

        cityInput.addEventListener('input', function() {
            const query = this.value.trim();

            clearTimeout(citySuggestionsTimeout);

            if (query.length < 2) {
                citySuggestions.style.display = 'none';
                selectedCity = '';
                selectedCityData = null;
                updateCitySelectedInfo(null);
                return;
            }

            citySuggestionsTimeout = setTimeout(async () => {
                const cities = await searchCities(query);

                if (cities.length === 0) {
                    citySuggestions.style.display = 'none';
                    return;
                }

                updateSuggestionsWidth();
                citySuggestions.innerHTML = '';

                cities.forEach(city => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item';
                    item.innerHTML = `
                        <span class="suggestion-text">${city.name}</span>
                        <span class="suggestion-type">${city.country}</span>
                    `;
                    item.addEventListener('click', function() {
                        cityInput.value = city.name;
                        selectedCity = city.name;
                        selectedCityData = city;
                        citySuggestions.style.display = 'none';
                        // Обновляем валюту в зависимости от страны
                        updateCurrency(city);
                        // Показываем информацию о выбранном городе и стране
                        updateCitySelectedInfo(city);
                        // Загружаем улицы выбранного города
                        loadCityStreets(city.name, city);
                    });
                    citySuggestions.appendChild(item);
                });

                citySuggestions.style.display = 'block';
            }, 300);
        });

        // Скрываем подсказки при клике вне поля
        document.addEventListener('click', function(e) {
            if (!cityInput.contains(e.target) && !citySuggestions.contains(e.target)) {
                citySuggestions.style.display = 'none';
            }
        });

        // Сохраняем выбранный город и загружаем улицы
        cityInput.addEventListener('blur', function() {
            setTimeout(async () => {
                if (this.value && this.value.length >= 2) {
                    // Если город не был выбран из списка, пытаемся найти его
                    if (!selectedCityData || selectedCityData.name !== this.value) {
                        const cities = await searchCities(this.value);
                        if (cities.length > 0) {
                            selectedCity = cities[0].name;
                            selectedCityData = cities[0];
                            // Обновляем валюту в зависимости от страны
                            updateCurrency(cities[0]);
                            // Показываем информацию о выбранном городе и стране
                            updateCitySelectedInfo(cities[0]);
                            loadCityStreets(cities[0].name, cities[0]);
                        }
                    } else {
                        // Обновляем валюту в зависимости от страны
                        updateCurrency(selectedCityData);
                        // Показываем информацию о выбранном городе и стране
                        updateCitySelectedInfo(selectedCityData);
                        loadCityStreets(selectedCity, selectedCityData);
                    }
                } else {
                    // Если поле пустое, скрываем информацию
                    updateCitySelectedInfo(null);
                }
            }, 200);
        });

        // Обновляем ширину при изменении размера окна
        window.addEventListener('resize', updateSuggestionsWidth);
    }

    /**
     * Загрузка всех улиц выбранного города
     */
    let allCityStreets = []; // Хранит все улицы текущего города
    let streetSearchHandlerAdded = false; // Флаг, что обработчик поиска уже добавлен

    /**
     * Альтернативный метод поиска улиц через Nominatim (для случаев, когда Overpass не работает)
     */
    async function searchStreetsViaNominatim(city, cityData = null) {
        try {
            const country = cityData ? (cityData.countryCode || '') : '';
            const searchQueries = country ? [`street, ${city}, ${country}`, `road, ${city}, ${country}`, city] : [`street, ${city}`, `road, ${city}`, city];

            const allStreets = new Set();
            const streetsList = [];

            // Делаем несколько запросов для получения большего количества улиц
            for (const query of searchQueries) {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=50&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                for (const item of data) {
                    const address = item.address || {};
                    let streetName = address.road ||
                        address.street ||
                        address.pedestrian ||
                        address.path ||
                        address.footway ||
                        address.residential;

                    // Если не нашли в address, пробуем из display_name
                    if (!streetName && item.display_name) {
                        const parts = item.display_name.split(',');
                        for (let part of parts) {
                            part = part.trim();
                            // Пропускаем части с городом и страной
                            if (part.toLowerCase().includes(city.toLowerCase()) ||
                                (cityData && cityData.country && part.toLowerCase().includes(cityData.country.toLowerCase()))) {
                                continue;
                            }
                            // Проверяем, содержит ли часть название улицы
                            if (part.match(/(street|st|road|rd|avenue|ave|boulevard|blvd|проспект|пр|улица|ул|проезд|пер|переулок|бульвар)/i)) {
                                streetName = part;
                                break;
                            }
                        }
                    }

                    if (streetName) {
                        // Очищаем название
                        streetName = streetName.toString().trim();
                        streetName = streetName.replace(/^(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.|street|st|road|rd|avenue|ave|boulevard|blvd)\s+/i, '').trim();
                        streetName = streetName.replace(/\s+(улица|ул\.|ул|street|st|road|rd|avenue|ave|boulevard|blvd)$/i, '').trim();

                        const streetLower = streetName.toLowerCase();
                        if (streetName.length > 1 &&
                            !streetName.match(/^\d+$/) &&
                            !allStreets.has(streetLower)) {
                            allStreets.add(streetLower);
                            streetsList.push(streetName);
                        }
                    }
                }
            }

            // Сортируем по алфавиту
            streetsList.sort((a, b) => a.localeCompare(b));
            return streetsList;
        } catch (error) {
            console.error('Ошибка поиска улиц через Nominatim:', error);
            return [];
        }
    }

    async function loadCityStreets(city, cityData = null) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (!city || city.length < 2) {
            return;
        }

        // Очищаем поле улицы и делаем его доступным для поиска
        streetInput.value = '';
        streetInput.removeAttribute('readonly');
        streetInput.placeholder = 'Начните вводить для поиска улицы или выберите из списка';

        // Показываем индикатор загрузки
        const cityDisplayName = cityData ? `${city}, ${cityData.country}` : city;
        streetSuggestions.innerHTML = '<div class="list-group-item"><span class="suggestion-text">Загрузка улиц города ' + cityDisplayName + '...</span></div>';
        streetSuggestions.style.display = 'block';
        streetSuggestions.style.width = streetInput.offsetWidth + 'px';

        let streetsList = [];
        let useOverpass = true;

        try {
            let cityBbox;

            // Если данные о городе уже есть, используем их
            if (cityData && cityData.bbox) {
                cityBbox = cityData.bbox;
            } else {
                // Шаг 1: Получаем bounding box города через Nominatim (без ограничения по стране)
                const citySearchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(city)}&limit=1&featuretype=city&addressdetails=1`;

                const cityResponse = await fetch(citySearchUrl, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!cityResponse.ok) {
                    throw new Error('Не удалось найти город');
                }

                const cityResponseData = await cityResponse.json();
                if (!cityResponseData || cityResponseData.length === 0) {
                    throw new Error('Город не найден');
                }

                cityBbox = cityResponseData[0].boundingbox || cityResponseData[0].bbox;
                if (!cityBbox) {
                    throw new Error('Не удалось получить границы города');
                }
            }

            const [south, north, west, east] = cityBbox.map(parseFloat);

            // Шаг 2: Пробуем использовать Overpass API для получения всех улиц
            try {
                const overpassUrl = 'https://overpass-api.de/api/interpreter';
                const overpassQuery = `[out:json][timeout:25];
(
  way["highway"~"^(primary|secondary|tertiary|residential|unclassified|living_street|pedestrian|service|trunk)$"]["name"](${south},${west},${north},${east});
);
out body;
>;
out skel qt;`;

                const overpassResponse = await fetch(overpassUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'User-Agent': 'Tanisu App'
                    },
                    body: `data=${encodeURIComponent(overpassQuery)}`
                });

                if (overpassResponse.ok) {
                    const overpassData = await overpassResponse.json();

                    const allStreets = new Set();

                    // Извлекаем названия улиц
                    if (overpassData.elements && overpassData.elements.length > 0) {
                        overpassData.elements.forEach(element => {
                            if (element.tags && element.tags.name) {
                                let streetName = element.tags.name.toString().trim();

                                // Очищаем название (универсальная очистка для разных языков)
                                streetName = streetName.replace(/^(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.|street|st|road|rd|avenue|ave|boulevard|blvd|via|viale|calle|callejón|rue|straße|straße)\s+/i, '').trim();
                                streetName = streetName.replace(/\s+(улица|ул\.|ул|street|st|road|rd|avenue|ave|boulevard|blvd)$/i, '').trim();
                                streetName = streetName.replace(/\s+\d+[а-яa-z]*$/i, '').trim();

                                if (streetName.length > 1 &&
                                    !streetName.match(/^\d+$/) &&
                                    !allStreets.has(streetName.toLowerCase())) {
                                    allStreets.add(streetName.toLowerCase());
                                    streetsList.push(streetName);
                                }
                            }
                        });

                        // Сортируем по алфавиту
                        streetsList.sort((a, b) => a.localeCompare(b));
                        useOverpass = true;
                    } else {
                        // Overpass вернул пустой результат, пробуем альтернативный метод
                        useOverpass = false;
                    }
                } else {
                    // Overpass API недоступен, пробуем альтернативный метод
                    useOverpass = false;
                }
            } catch (overpassError) {
                console.log('Overpass API недоступен, используем альтернативный метод:', overpassError);
                useOverpass = false;
            }

            // Если Overpass не сработал или вернул мало результатов, используем Nominatim
            if (!useOverpass || streetsList.length < 10) {
                const nominatimStreets = await searchStreetsViaNominatim(city, cityData);
                if (nominatimStreets.length > 0) {
                    // Объединяем результаты, убирая дубликаты
                    const combinedSet = new Set(streetsList.map(s => s.toLowerCase()));
                    for (const street of nominatimStreets) {
                        if (!combinedSet.has(street.toLowerCase())) {
                            streetsList.push(street);
                            combinedSet.add(street.toLowerCase());
                        }
                    }
                    // Сортируем заново
                    streetsList.sort((a, b) => a.localeCompare(b));
                }
            }

            // Сохраняем список для фильтрации
            allCityStreets = streetsList;

            // Показываем все улицы
            displayStreetsList(streetsList, city);

        } catch (error) {
            console.error('Ошибка загрузки улиц:', error);
            // Пробуем альтернативный метод через Nominatim
            try {
                const nominatimStreets = await searchStreetsViaNominatim(city, cityData);
                if (nominatimStreets.length > 0) {
                    allCityStreets = nominatimStreets;
                    displayStreetsList(nominatimStreets, city);
                } else {
                    // Если и альтернативный метод не сработал, просто разрешаем ручной ввод
                    streetSuggestions.innerHTML = `
                        <div class="list-group-item">
                            <span class="suggestion-text text-muted">Автоматическая загрузка улиц недоступна для этого города. Вы можете ввести название улицы вручную.</span>
                        </div>
                    `;
                    streetInput.removeAttribute('readonly');
                    streetInput.placeholder = 'Введите название улицы вручную';
                }
            } catch (fallbackError) {
                console.error('Ошибка альтернативного метода:', fallbackError);
                streetSuggestions.innerHTML = `
                    <div class="list-group-item">
                        <span class="suggestion-text text-muted">Автоматическая загрузка улиц недоступна. Вы можете ввести название улицы вручную.</span>
                    </div>
                `;
                streetInput.removeAttribute('readonly');
                streetInput.placeholder = 'Введите название улицы вручную';
            }
        }
    }

    /**
     * Отображение списка улиц с поиском
     */
    function displayStreetsList(streetsList, city) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (streetsList.length === 0) {
            streetSuggestions.innerHTML = `
                <div class="list-group-item">
                    <span class="suggestion-text text-muted">Улицы не найдены для города "${city}". Вы можете ввести название улицы вручную.</span>
                </div>
            `;
            streetSuggestions.style.display = 'block';
            return;
        }

        // Отображаем все улицы
        renderStreets(streetsList, city);

        // Добавляем поиск при вводе (только один раз)
        if (!streetSearchHandlerAdded) {
            streetInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                clearTimeout(streetSuggestionsTimeout);

                streetSuggestionsTimeout = setTimeout(() => {
                    if (query === '') {
                        renderStreets(allCityStreets, city);
                    } else {
                        const filtered = allCityStreets.filter(street =>
                            street.toLowerCase().includes(query)
                        );
                        renderStreets(filtered, city);
                    }
                }, 200);
            });
            streetSearchHandlerAdded = true;
        }
    }

    /**
     * Рендеринг списка улиц
     */
    function renderStreets(streetsList, city) {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        // Обновляем ширину списка
        streetSuggestions.style.width = streetInput.offsetWidth + 'px';

        if (streetsList.length === 0) {
            streetSuggestions.innerHTML = `
                <div class="list-group-item">
                    <span class="suggestion-text text-muted">Улицы не найдены. Попробуйте другой поисковый запрос.</span>
                </div>
            `;
            streetSuggestions.style.display = 'block';
            return;
        }

        streetSuggestions.innerHTML = '';

        // Показываем первые 100 улиц (для производительности)
        const displayList = streetsList.slice(0, 100);

        displayList.forEach(street => {
            const item = document.createElement('div');
            item.className = 'list-group-item';
            item.innerHTML = `<span class="suggestion-text">${street}</span>`;
            item.addEventListener('click', function() {
                streetInput.value = street;
                streetSuggestions.style.display = 'none';
            });
            streetSuggestions.appendChild(item);
        });

        if (streetsList.length > 100) {
            const moreItem = document.createElement('div');
            moreItem.className = 'list-group-item';
            moreItem.innerHTML = `<span class="suggestion-text text-muted">... и еще ${streetsList.length - 100} улиц. Используйте поиск для уточнения.</span>`;
            streetSuggestions.appendChild(moreItem);
        }

        streetSuggestions.style.display = 'block';
    }

    /**
     * Старая функция поиска (оставлена для совместимости, но не используется)
     */
    async function searchStreetSuggestions(query, city) {
        if (!city || query.length < 2) {
            return [];
        }

        try {
            // Используем только самые эффективные варианты поиска (уменьшено с 5 до 2)
            const searchQueries = [
                `${query}, ${city}, Казахстан`,
                `${city}, ${query}, Казахстан`
            ];

            const allStreets = [];
            const seenStreets = new Set();

            // Делаем запросы параллельно для ускорения
            const fetchPromises = searchQueries.map(searchQuery => {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=15&countrycodes=kz&addressdetails=1`;

                return fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                }).then(response => {
                    if (!response.ok) return null;
                    return response.json();
                }).catch(err => {
                    console.error('Ошибка при запросе:', err);
                    return null;
                });
            });

            // Ждем все запросы параллельно
            const results = await Promise.all(fetchPromises);

            // Обрабатываем все результаты
            outerLoop: for (const data of results) {
                if (!data || data.length === 0) continue;

                for (const item of data) {
                    if (allStreets.length >= 10) break outerLoop;

                    const address = item.address || {};
                    // Пробуем разные поля для названия улицы
                    let streetName = address.road ||
                        address.street ||
                        address.pedestrian ||
                        address.path ||
                        address.footway ||
                        address.residential;

                    // Если не нашли в address, пробуем из display_name
                    if (!streetName && item.display_name) {
                        const parts = item.display_name.split(',');
                        for (let part of parts) {
                            part = part.trim();
                            // Пропускаем части с городом и страной
                            if (part.toLowerCase().includes(city.toLowerCase()) ||
                                part.toLowerCase().includes('казахстан') ||
                                part.toLowerCase().includes('kz')) {
                                continue;
                            }
                            // Проверяем, содержит ли часть название улицы
                            if (part.toLowerCase().includes(query.toLowerCase()) ||
                                part.toLowerCase().match(/(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.)/i)) {
                                streetName = part;
                                break;
                            }
                        }
                    }

                    // Если все еще не нашли, пробуем из type
                    if (!streetName && item.type && item.type.includes('road')) {
                        streetName = item.display_name.split(',')[0];
                    }

                    if (streetName) {
                        // Очищаем название от лишних слов
                        streetName = streetName.toString();
                        streetName = streetName.replace(/^(улица|ул\.|ул|проспект|пр\.|пр|проезд|пер\.|переулок|бульвар|б-р|б\.)\s+/i, '').trim();
                        streetName = streetName.replace(/\s+(улица|ул\.|ул)$/i, '').trim();
                        streetName = streetName.replace(/\s+\d+[а-я]*$/i, '').trim();

                        const streetLower = streetName.toLowerCase();
                        // Проверяем что название содержит запрос
                        if (streetName.length > 1 &&
                            !seenStreets.has(streetLower) &&
                            streetLower.includes(query.toLowerCase())) {
                            seenStreets.add(streetLower);
                            allStreets.push({
                                name: streetName,
                                fullAddress: item.display_name
                            });
                        }
                    }
                }
            }

            // Сортируем результаты по релевантности (начинающиеся с запроса)
            allStreets.sort((a, b) => {
                const aLower = a.name.toLowerCase();
                const bLower = b.name.toLowerCase();
                const queryLower = query.toLowerCase();

                const aStarts = aLower.startsWith(queryLower);
                const bStarts = bLower.startsWith(queryLower);
                const aContains = aLower.includes(queryLower);
                const bContains = bLower.includes(queryLower);

                if (aStarts && !bStarts) return -1;
                if (!aStarts && bStarts) return 1;
                if (aContains && !bContains) return -1;
                if (!aContains && bContains) return 1;
                return a.name.localeCompare(b.name);
            });

            return allStreets.slice(0, 10);
        } catch (error) {
            console.error('Ошибка поиска улиц:', error);
            return [];
        }
    }

    /**
     * Инициализация обработчиков для улиц
     */
    function initStreetHandlers() {
        const streetInput = document.getElementById('street');
        const streetSuggestions = document.getElementById('street-suggestions');

        if (!streetInput || !streetSuggestions) return;

        // Скрываем подсказки при клике вне поля
        document.addEventListener('click', function(e) {
            if (!streetInput.contains(e.target) && !streetSuggestions.contains(e.target)) {
                streetSuggestions.style.display = 'none';
            }
        });

        // Обновляем ширину списка при изменении размера окна
        window.addEventListener('resize', function() {
            if (streetSuggestions.style.display !== 'none') {
                streetSuggestions.style.width = streetInput.offsetWidth + 'px';
            }
        });
    }

    /**
     * Геокодирование адреса в координаты
     * Использует OpenStreetMap Nominatim API (бесплатный)
     * Работает с любыми странами
     */
    async function geocodeAddress() {
        const city = document.getElementById('city').value.trim();
        const street = document.getElementById('street').value.trim();
        const houseNumber = document.getElementById('house_number').value.trim();
        const location = document.getElementById('location').value.trim();
        const latInput = document.getElementById('latitude');
        const lonInput = document.getElementById('longitude');

        // Проверяем обязательные поля
        if (!city || !street || !houseNumber) {
            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Заполните все поля адреса (город, улица, номер дома)</div>';
            return;
        }

        // Показываем статус загрузки
        document.getElementById('locationStatus').innerHTML =
            '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Поиск адреса на карте...</div>';

        // Получаем информацию о стране и координатах города
        const country = selectedCityData ? selectedCityData.country : '';
        const cityLat = selectedCityData && selectedCityData.lat ? parseFloat(selectedCityData.lat) : null;
        const cityLon = selectedCityData && selectedCityData.lon ? parseFloat(selectedCityData.lon) : null;

        // Стратегия 1: Если есть координаты города, используем их для поиска улицы в радиусе
        if (cityLat && cityLon && !isNaN(cityLat) && !isNaN(cityLon)) {
            try {
                // Ищем улицу в радиусе 5км от центра города
                const searchQuery = `${street} ${houseNumber}, ${city}`;
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=10&addressdetails=1&bounded=1&viewbox=${(cityLon - 0.05)},${(cityLat - 0.05)},${(cityLon + 0.05)},${(cityLat + 0.05)}`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data && data.length > 0) {
                        // Ищем наиболее подходящий результат
                        for (const result of data) {
                            const resultAddress = result.address || {};
                            const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                            const resultStreet = resultAddress.road || resultAddress.street || '';

                            const cityMatch = resultCity.toLowerCase().includes(city.toLowerCase()) ||
                                city.toLowerCase().includes(resultCity.toLowerCase());

                            const streetMatch = resultStreet.toLowerCase().includes(street.toLowerCase()) ||
                                street.toLowerCase().includes(resultStreet.toLowerCase());

                            if (cityMatch && streetMatch) {
                                const lat = parseFloat(result.lat);
                                const lon = parseFloat(result.lon);

                                if (!isNaN(lat) && !isNaN(lon)) {
                                    if (latInput) latInput.value = lat;
                                    if (lonInput) lonInput.value = lon;

                                    document.getElementById('locationStatus').innerHTML =
                                        '<div class="alert alert-success">' +
                                        '<i class="bi bi-check-circle"></i> Адрес найден на карте!<br>' +
                                        '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                                        '<small>Найденный адрес: ' + escapeHtml(result.display_name) + '</small>' +
                                        '</div>';
                                    return;
                                }
                            }
                        }
                    }
                }
            } catch (error) {
                console.log('Ошибка поиска в радиусе:', error);
            }
        }

        // Стратегия 2: Поиск по полному адресу с разными форматами
        const searchQueries = [];

        // Формируем запросы в порядке приоритета
        if (country) {
            // С номером дома и страной
            searchQueries.push(`${houseNumber} ${street}, ${city}, ${country}`);
            searchQueries.push(`${street} ${houseNumber}, ${city}, ${country}`);
            searchQueries.push(`${city}, ${street} ${houseNumber}, ${country}`);
        }

        // Без страны
        searchQueries.push(`${houseNumber} ${street}, ${city}`);
        searchQueries.push(`${street} ${houseNumber}, ${city}`);
        searchQueries.push(`${city}, ${street} ${houseNumber}`);

        let found = false;
        let bestResult = null;

        // Пробуем каждый вариант поиска
        for (const query of searchQueries) {
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                // Ищем наиболее подходящий результат
                for (const result of data) {
                    const resultAddress = result.address || {};
                    const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                    const resultStreet = resultAddress.road || resultAddress.street || '';

                    // Проверяем совпадение города (более строгая проверка)
                    const cityLower = city.toLowerCase();
                    const resultCityLower = resultCity.toLowerCase();
                    const displayNameLower = result.display_name.toLowerCase();

                    const cityMatch = resultCityLower.includes(cityLower) ||
                        cityLower.includes(resultCityLower) ||
                        displayNameLower.includes(cityLower);

                    // Проверяем совпадение улицы
                    const streetLower = street.toLowerCase();
                    const resultStreetLower = resultStreet.toLowerCase();

                    const streetMatch = resultStreetLower.includes(streetLower) ||
                        streetLower.includes(resultStreetLower) ||
                        displayNameLower.includes(streetLower);

                    if (cityMatch && streetMatch) {
                        const lat = parseFloat(result.lat);
                        const lon = parseFloat(result.lon);

                        if (!isNaN(lat) && !isNaN(lon)) {
                            bestResult = result;
                            found = true;
                            break;
                        }
                    }
                }

                if (found) break;
            } catch (error) {
                console.log('Ошибка поиска:', error);
                continue;
            }
        }

        // Если нашли результат
        if (found && bestResult) {
            const lat = parseFloat(bestResult.lat);
            const lon = parseFloat(bestResult.lon);

            if (latInput) latInput.value = lat;
            if (lonInput) lonInput.value = lon;

            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-success">' +
                '<i class="bi bi-check-circle"></i> Адрес найден на карте!<br>' +
                '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                '<small>Найденный адрес: ' + escapeHtml(bestResult.display_name) + '</small>' +
                '</div>';
            return;
        }

        // Стратегия 3: Поиск без номера дома
        const queriesWithoutHouse = [];
        if (country) {
            queriesWithoutHouse.push(`${street}, ${city}, ${country}`);
            queriesWithoutHouse.push(`${city}, ${street}, ${country}`);
        }
        queriesWithoutHouse.push(`${street}, ${city}`);
        queriesWithoutHouse.push(`${city}, ${street}`);

        for (const query of queriesWithoutHouse) {
            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=10&addressdetails=1`;

                const response = await fetch(url, {
                    headers: {
                        'User-Agent': 'Tanisu App'
                    }
                });

                if (!response.ok) continue;

                const data = await response.json();
                if (!data || data.length === 0) continue;

                for (const result of data) {
                    const resultAddress = result.address || {};
                    const resultCity = resultAddress.city || resultAddress.town || resultAddress.village || resultAddress.municipality || '';
                    const resultStreet = resultAddress.road || resultAddress.street || '';

                    const cityLower = city.toLowerCase();
                    const resultCityLower = resultCity.toLowerCase();
                    const displayNameLower = result.display_name.toLowerCase();

                    const cityMatch = resultCityLower.includes(cityLower) ||
                        cityLower.includes(resultCityLower) ||
                        displayNameLower.includes(cityLower);

                    const streetLower = street.toLowerCase();
                    const resultStreetLower = resultStreet.toLowerCase();

                    const streetMatch = resultStreetLower.includes(streetLower) ||
                        streetLower.includes(resultStreetLower) ||
                        displayNameLower.includes(streetLower);

                    if (cityMatch && streetMatch) {
                        const lat = parseFloat(result.lat);
                        const lon = parseFloat(result.lon);

                        if (!isNaN(lat) && !isNaN(lon)) {
                            if (latInput) latInput.value = lat;
                            if (lonInput) lonInput.value = lon;

                            document.getElementById('locationStatus').innerHTML =
                                '<div class="alert alert-warning">' +
                                '<i class="bi bi-exclamation-triangle"></i> Адрес найден приблизительно (без номера дома)<br>' +
                                '<small>Координаты: ' + lat.toFixed(6) + ', ' + lon.toFixed(6) + '</small><br>' +
                                '<small>Найденный адрес: ' + escapeHtml(result.display_name) + '</small><br>' +
                                '<small>Проверьте правильность адреса на карте</small>' +
                                '</div>';

                            found = true;
                            return;
                        }
                    }
                }
            } catch (error) {
                continue;
            }
        }

        // Стратегия 4: Используем координаты центра города (гарантированный результат)
        if (cityLat && cityLon && !isNaN(cityLat) && !isNaN(cityLon)) {
            if (latInput) latInput.value = cityLat;
            if (lonInput) lonInput.value = cityLon;

            document.getElementById('locationStatus').innerHTML =
                '<div class="alert alert-success">' +
                '<i class="bi bi-check-circle"></i> Координаты установлены!<br>' +
                '<small>Координаты: ' + cityLat.toFixed(6) + ', ' + cityLon.toFixed(6) + '</small><br>' +
                '<small>Используются координаты центра города ' + escapeHtml(city) + '</small><br>' +
                '<small>Адрес: ' + escapeHtml(houseNumber + ', ' + street + ', ' + city + (country ? ', ' + country : '')) + '</small>' +
                '</div>';
            return;
        }

        // Если ничего не сработало
        document.getElementById('locationStatus').innerHTML =
            '<div class="alert alert-danger">' +
            '<i class="bi bi-exclamation-triangle"></i> Не удалось найти адрес на карте.<br>' +
            '<small>Проверьте правильность введенного адреса. Убедитесь, что указаны: город, улица и номер дома.</small><br>' +
            '<small>Попробуйте:</small><br>' +
            '<small>• Убедиться, что город определен автоматически или выбран из списка</small><br>' +
            '<small>• Убедиться, что улица выбрана из списка после выбора города</small><br>' +
            '<small>• Проверить правильность написания названия улицы</small>' +
            '</div>';
    }

    /**
     * Демо-кнопки «Бесплатно» / тариф в тенге — имитация оплаты размещения (без реального платежа).
     */
    let publishPaymentPendingSubmit = false;

    function getPublishPaymentModal() {
        const modalEl = document.getElementById('publishPaymentModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
            return null;
        }
        return bootstrap.Modal.getOrCreateInstance(modalEl);
    }

    function showPublishPaymentModal() {
        const modal = getPublishPaymentModal();
        if (modal) {
            modal.show();
            return;
        }
        const statusEl = document.getElementById('publishPaymentDemoStatus');
        if (statusEl) {
            statusEl.innerHTML =
                '<div class="alert alert-warning py-2 mb-0"><i class="bi bi-exclamation-triangle"></i> Выберите вариант имитации оплаты.</div>';
        }
    }

    function completePublishPaymentDemoAndSubmit() {
        const form = document.querySelector('.event-create-form');
        const modal = getPublishPaymentModal();
        if (modal) {
            modal.hide();
        }
        if (form && publishPaymentPendingSubmit) {
            publishPaymentPendingSubmit = false;
            form.requestSubmit();
        }
    }

    function initPublishPaymentDemo() {
        const hidden = document.getElementById('publish_payment_demo');
        const freeBtn = document.getElementById('publishDemoFreeBtn');
        const paidBtn = document.getElementById('publishDemoPaidBtn');
        const statusEl = document.getElementById('publishPaymentDemoStatus');
        const modalEl = document.getElementById('publishPaymentModal');

        if (!hidden || !freeBtn || !paidBtn) {
            return;
        }

        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                if (!String(hidden.value || '').trim()) {
                    publishPaymentPendingSubmit = false;
                }
            });
        }

        function setActiveButton(active) {
            freeBtn.classList.remove('btn-publish-demo-active');
            paidBtn.classList.remove('btn-publish-demo-active');
            if (active === 'free') {
                freeBtn.classList.add('btn-publish-demo-active');
            } else if (active === 'paid') {
                paidBtn.classList.add('btn-publish-demo-active');
            }
        }

        freeBtn.addEventListener('click', function() {
            hidden.value = 'free';
            setActiveButton('free');
            freeBtn.disabled = false;
            paidBtn.disabled = false;
            if (statusEl) {
                statusEl.innerHTML =
                    '<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle"></i> Демо: размещение бесплатное. Публикуем…</div>';
            }
            completePublishPaymentDemoAndSubmit();
        });

        paidBtn.addEventListener('click', function() {
            const amount = paidBtn.getAttribute('data-demo-amount') || '500';
            freeBtn.disabled = true;
            paidBtn.disabled = true;
            setActiveButton(null);
            if (statusEl) {
                statusEl.innerHTML =
                    '<div class="alert alert-info py-2 mb-0"><i class="bi bi-hourglass-split"></i> Демо: имитация оплаты…</div>';
            }

            window.setTimeout(function() {
                hidden.value = 'paid';
                setActiveButton('paid');
                freeBtn.disabled = false;
                paidBtn.disabled = false;
                if (statusEl) {
                    statusEl.innerHTML =
                        '<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle"></i> Демо: условная оплата <strong>' +
                        escapeHtml(String(amount)) + ' ₸</strong> прошла успешно. Публикуем…</div>';
                }
                completePublishPaymentDemoAndSubmit();
            }, 900);
        });
    }

    /**
     * Формат даты/времени для превью карточки (как в events/index: дд.мм.гггг в чч:мм).
     */
    function formatEventDateTimeLocal(val) {
        if (!val || typeof val !== 'string') {
            return '';
        }
        const d = new Date(val);
        if (isNaN(d.getTime())) {
            return '';
        }
        const pad = function(n) {
            return String(n).padStart(2, '0');
        };
        return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' в ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    /**
     * Синхронизация названия и даты с макетом «как в ленте на главной».
     */
    function initEventLiveDemo() {
        const titleInput = document.getElementById('title');
        const dateInput = document.getElementById('event_date');
        const demoTitle = document.getElementById('eventLiveDemoTitle');
        const demoTime = document.getElementById('eventLiveDemoTimeSpan');

        function syncTitle() {
            if (!demoTitle) {
                return;
            }
            const t = titleInput && titleInput.value.trim() ? titleInput.value.trim() : 'Название мероприятия';
            demoTitle.textContent = t;
        }

        function syncDate() {
            if (!demoTime) {
                return;
            }
            const f = dateInput && dateInput.value ? formatEventDateTimeLocal(dateInput.value) : '';
            demoTime.textContent = f || 'Укажите дату и время проведения ниже';
        }

        if (titleInput) {
            titleInput.addEventListener('input', syncTitle);
        }
        if (dateInput) {
            dateInput.addEventListener('change', syncDate);
            dateInput.addEventListener('input', syncDate);
        }
        syncTitle();
        syncDate();
    }

    /**
     * Обрезка баннера мероприятия (Cropper.js), отправка обрезанного на сервер в photo_cropped_base64.
     */
    function initEventBannerCropper() {
        if (typeof Cropper === 'undefined') {
            console.warn('Cropper.js не загружен');
            return;
        }

        const EVENT_BANNER_ASPECT = <?= (int) $eventBannerAspectW ?> / <?= (int) $eventBannerAspectH ?>;
        const EVENT_BANNER_EXPORT_W = <?= (int) $eventBannerExportW ?>;
        const EVENT_BANNER_EXPORT_H = <?= (int) $eventBannerExportH ?>;

        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        const photoPreviewImg = document.getElementById('photo-preview-img');
        const eventLiveDemo = document.getElementById('eventLiveDemo');
        const eventLiveDemoImg = document.getElementById('eventLiveDemoImg');
        const base64Input = document.getElementById('photo_cropped_base64');
        const cropperModal = document.getElementById('eventCropperModal');
        const cropperImage = document.getElementById('eventCropperImage');
        const cropperClose = document.getElementById('eventCropperClose');
        const cropperCancel = document.getElementById('eventCropperCancel');
        const cropperApply = document.getElementById('eventCropperApply');
        const cropperRotateLeft = document.getElementById('eventCropperRotateLeft');
        const cropperRotateRight = document.getElementById('eventCropperRotateRight');
        const cropperFlipH = document.getElementById('eventCropperFlipH');
        const cropperReset = document.getElementById('eventCropperReset');
        const editBtn = document.getElementById('eventPhotoEditBtn');

        if (!photoInput || !cropperModal || !cropperImage || !base64Input) {
            return;
        }

        let cropper = null;
        let currentFile = null;
        let originalFile = null;

        function syncEventLiveDemoFromDataUrl(dataUrl) {
            if (photoPreviewImg && dataUrl) {
                photoPreviewImg.src = dataUrl;
            }
            if (photoPreview && dataUrl) {
                photoPreview.classList.add('has-image');
            }
            if (eventLiveDemoImg && dataUrl) {
                eventLiveDemoImg.src = dataUrl;
            }
            if (eventLiveDemo && dataUrl) {
                eventLiveDemo.classList.add('has-image');
            }
        }

        function clearPhotoState() {
            if (photoInput) {
                photoInput.value = '';
            }
            if (base64Input) {
                base64Input.value = '';
            }
            if (photoPreviewImg) {
                photoPreviewImg.src = '';
            }
            if (photoPreview) {
                photoPreview.classList.remove('has-image');
            }
            if (eventLiveDemoImg) {
                eventLiveDemoImg.src = '';
            }
            if (eventLiveDemo) {
                eventLiveDemo.classList.remove('has-image');
            }
            currentFile = null;
            originalFile = null;
        }

        function openCropperModal(file) {
            currentFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                cropperImage.src = e.target.result;
                cropperModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                cropperImage.onload = function() {
                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }
                    window.setTimeout(function() {
                        cropper = new Cropper(cropperImage, {
                            aspectRatio: EVENT_BANNER_ASPECT,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: true,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                            responsive: true,
                            background: true,
                            preview: '#eventCropperPreview',
                            crop: function(ev) {
                                const w = Math.round(ev.detail.width);
                                const h = Math.round(ev.detail.height);
                                const sizeEl = document.getElementById('eventCropperSize');
                                if (sizeEl) {
                                    sizeEl.textContent = 'Размер: ' + w + ' x ' + h + ' px';
                                }
                            }
                        });
                    }, 100);
                };
            };
            reader.readAsDataURL(file);
        }

        function closeCropperModal() {
            cropperModal.style.display = 'none';
            document.body.style.overflow = '';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            clearPhotoState();
        }

        function applyCrop() {
            if (!cropper) {
                return;
            }

            const canvas = cropper.getCroppedCanvas({
                width: EVENT_BANNER_EXPORT_W,
                height: EVENT_BANNER_EXPORT_H,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (!canvas) {
                window.alert('Не удалось обработать изображение');
                return;
            }

            let mimeType = currentFile && currentFile.type ? currentFile.type : 'image/jpeg';
            if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(mimeType)) {
                mimeType = 'image/jpeg';
            }

            canvas.toBlob(function(blob) {
                if (!blob) {
                    window.alert('Не удалось создать изображение');
                    return;
                }

                const dataUrl = canvas.toDataURL(mimeType, 0.92);
                if (base64Input) {
                    base64Input.value = dataUrl;
                }

                const croppedFile = new File([blob], (currentFile && currentFile.name) ? currentFile.name : 'banner.jpg', {
                    type: mimeType,
                    lastModified: Date.now()
                });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                photoInput.files = dataTransfer.files;

                syncEventLiveDemoFromDataUrl(dataUrl);

                cropperModal.style.display = 'none';
                document.body.style.overflow = '';
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            }, mimeType, 0.92);
        }

        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (!file.type.match(/^image\/(jpeg|pjpeg|png|gif|webp)$/i)) {
                        window.alert('Выберите изображение в формате JPG, PNG, GIF или WebP');
                        photoInput.value = '';
                        return;
                    }
                    originalFile = file;
                    openCropperModal(file);
                } else {
                    clearPhotoState();
                }
            });
        }

        if (editBtn) {
            editBtn.addEventListener('click', function() {
                if (originalFile) {
                    openCropperModal(originalFile);
                }
            });
        }

        if (cropperClose) {
            cropperClose.addEventListener('click', closeCropperModal);
        }
        if (cropperCancel) {
            cropperCancel.addEventListener('click', closeCropperModal);
        }
        if (cropperApply) {
            cropperApply.addEventListener('click', applyCrop);
        }

        if (cropperRotateLeft) {
            cropperRotateLeft.addEventListener('click', function() {
                if (cropper) {
                    cropper.rotate(-90);
                }
            });
        }
        if (cropperRotateRight) {
            cropperRotateRight.addEventListener('click', function() {
                if (cropper) {
                    cropper.rotate(90);
                }
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
                if (cropper) {
                    cropper.reset();
                }
            });
        }

        if (cropperModal) {
            cropperModal.addEventListener('click', function(e) {
                if (e.target === cropperModal) {
                    closeCropperModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && cropperModal && cropperModal.style.display === 'flex') {
                closeCropperModal();
            }
        });
    }

    // Предпросмотр фото
    document.addEventListener('DOMContentLoaded', function() {
        initEventBannerCropper();

        initEventLiveDemo();

        // Инициализируем автодополнение
        initCityAutocomplete();
        initStreetHandlers();
        initPublishPaymentDemo();

        // Инициализируем валюту по умолчанию (тенге для Казахстана)
        updateCurrency(null);

        // Автоматическое определение города по геолокации
        if (navigator.geolocation) {
            // Показываем индикатор загрузки
            const cityInput = document.getElementById('city');
            if (cityInput && !cityInput.value) {
                cityInput.placeholder = 'Определение местоположения...';
            }

            navigator.geolocation.getCurrentPosition(
                async function(position) {
                        try {
                            const lat = position.coords.latitude;
                            const lon = position.coords.longitude;
                            const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`;

                            const response = await fetch(url, {
                                headers: {
                                    'User-Agent': 'Tanisu App'
                                }
                            });

                            if (response.ok) {
                                const data = await response.json();
                                const address = data.address || {};

                                // Получаем название города
                                const cityName = address.city ||
                                    address.town ||
                                    address.village ||
                                    address.municipality ||
                                    address.county ||
                                    '';

                                const country = address.country || '';
                                const countryCode = address.country_code || '';

                                if (cityName && cityInput && !cityInput.value) {
                                    // Заполняем поле города
                                    cityInput.value = cityName;
                                    selectedCity = cityName;

                                    // Создаем объект с данными о городе
                                    selectedCityData = {
                                        name: cityName,
                                        country: country,
                                        countryCode: countryCode,
                                        lat: lat,
                                        lon: lon
                                    };

                                    // Обновляем валюту
                                    updateCurrency(selectedCityData);

                                    // Показываем информацию о выбранном городе
                                    updateCitySelectedInfo(selectedCityData);

                                    // Автоматически загружаем улицы для найденного города
                                    loadCityStreets(cityName, selectedCityData);

                                    // Устанавливаем координаты (если они еще не установлены)
                                    const latInput = document.getElementById('latitude');
                                    const lonInput = document.getElementById('longitude');
                                    if (latInput && !latInput.value) {
                                        latInput.value = lat;
                                    }
                                    if (lonInput && !lonInput.value) {
                                        lonInput.value = lon;
                                    }

                                    // Показываем уведомление
                                    const locationStatus = document.getElementById('locationStatus');
                                    if (locationStatus) {
                                        locationStatus.innerHTML =
                                            '<div class="alert alert-success">' +
                                            '<i class="bi bi-check-circle"></i> Город определен автоматически по геолокации!<br>' +
                                            '<small>Город: ' + escapeHtml(cityName) + (country ? ', ' + escapeHtml(country) : '') + '</small><br>' +
                                            '<small>Выберите улицу из списка ниже</small>' +
                                            '</div>';
                                    }
                                } else if (country || countryCode) {
                                    // Если город не найден, но страна есть, обновляем только валюту
                                    const cityData = {
                                        country: country,
                                        countryCode: countryCode
                                    };
                                    updateCurrency(cityData);
                                }
                            }
                        } catch (error) {
                            console.log('Ошибка определения города по геолокации:', error);
                            if (cityInput && !cityInput.value) {
                                cityInput.placeholder = 'Начните вводить название города...';
                            }
                        }
                    },
                    function(error) {
                        // Игнорируем ошибки геолокации
                        console.log('Геолокация недоступна:', error.message);
                        const cityInput = document.getElementById('city');
                        if (cityInput && !cityInput.value) {
                            cityInput.placeholder = 'Начните вводить название города...';
                        }
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
            );
        }

        // Валидация формы перед отправкой
        const form = document.querySelector('.event-create-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const latitude = document.getElementById('latitude').value;
                const longitude = document.getElementById('longitude').value;

                if (!latitude || !longitude) {
                    e.preventDefault();
                    document.getElementById('locationStatus').innerHTML =
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-exclamation-triangle"></i> Необходимо найти адрес на карте перед отправкой формы!<br>' +
                        '<small>Заполните поля адреса (город, улица, номер дома) и нажмите кнопку "Найти адрес на карте"</small>' +
                        '</div>';

                    // Прокручиваем к сообщению об ошибке
                    document.getElementById('locationStatus').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }

                const demoInput = document.getElementById('publish_payment_demo');
                if (demoInput && !String(demoInput.value || '').trim()) {
                    e.preventDefault();
                    publishPaymentPendingSubmit = true;
                    const pubStatus = document.getElementById('publishPaymentDemoStatus');
                    if (pubStatus) {
                        pubStatus.innerHTML = '';
                    }
                    showPublishPaymentModal();
                    return false;
                }
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Создать мероприятие';
include __DIR__ . '/../layout.php';
?>