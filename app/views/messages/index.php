<?php

/**
 * СТРАНИЦА СООБЩЕНИЙ И УВЕДОМЛЕНИЙ
 */

ob_start();
?>

<?php if ($selectedUserId): ?>
    <style>
        /* Стили для страницы чата в стиле Telegram */
        body.chat-page {
            padding: 0 !important;
            margin: 0 !important;
            background: #e5e5e5;
            overflow: hidden;
        }

        body.chat-page .mobile-bottom-nav {
            display: none !important;
        }

        body.chat-page .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        body.chat-page .mobile-page-container {
            margin: 0 !important;
            padding: 0 !important;
            padding-bottom: env(safe-area-inset-bottom, 0px);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        /* Контейнер сообщений */
        #messages-container {
            background: #e5e5e5;
            padding: 12px 16px;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        #messages-container::-webkit-scrollbar {
            width: 5px;
        }

        #messages-container::-webkit-scrollbar-track {
            background: transparent;
        }

        #messages-container::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        #messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Сообщения в стиле Telegram */
        .message-item {
            margin-bottom: 10px;
            display: flex;
        }

        .message-item.text-end {
            justify-content: flex-end;
        }

        .message-item.text-start {
            justify-content: flex-start;
        }

        .message-bubble {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 12px;
            max-width: 70%;
            word-wrap: break-word;
            position: relative;
        }

        .message-bubble.own {
            background: #3390ec;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-bubble.other {
            background: white;
            color: #000;
            border-bottom-left-radius: 4px;
        }

        .message-bubble .message-sender {
            font-size: 13px;
            font-weight: 600;
            color: #3390ec;
            margin-bottom: 4px;
        }

        .message-bubble.own .message-sender {
            display: none;
        }

        .message-bubble .message-role-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 8px;
            margin-left: 6px;
            text-transform: uppercase;
            vertical-align: middle;
        }

        .message-bubble.other .message-role-badge.manager {
            background: #ffc107;
            color: #000;
        }

        .message-bubble.own .message-role-badge.manager {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .message-bubble.other .message-role-badge.admin {
            background: #dc3545;
            color: #fff;
        }

        .message-bubble.own .message-role-badge.admin {
            background: rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .message-bubble .message-content {
            font-size: 15px;
            line-height: 1.4;
            margin: 0;
            word-break: break-word;
        }

        .message-bubble .message-time {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }

        .message-bubble.own .message-time {
            opacity: 0.9;
        }

        /* Заголовок страницы */
        .chat-page-header {
            background: white;
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            flex-shrink: 0;
            z-index: 100;
            position: relative;
            overflow: visible;
        }

        .chat-page-header .d-flex {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            overflow: visible;
        }

        .chat-page-header .btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            z-index: 101;
            position: relative;
            flex-shrink: 0;
        }

        .chat-page-header h2,
        .chat-page-header h4 {
            margin: 0;
            font-size: 18px;
        }

        .chat-page-header .btn-danger {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .chat-page-header .dropdown-toggle {
            border: none;
            background: transparent;
            padding: 4px 8px;
        }

        .chat-page-header .dropdown-toggle:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .chat-page-header .dropdown button.btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 6px 10px;
            border: 1px solid #dee2e6;
            background: transparent;
        }

        .chat-page-header .dropdown button.btn:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .chat-page-header .dropdown button.btn i.bi-three-dots-vertical {
            margin: 0 !important;
            line-height: 1;
            vertical-align: middle;
        }

        .chat-page-header .d-flex>div:last-child {
            display: flex !important;
            align-items: center;
            justify-content: flex-end;
        }

        .chat-page-header .dropdown-menu {
            min-width: 180px;
        }

        .chat-page-header .badge {
            font-size: 12px;
            padding: 6px 10px;
        }

        .chat-page-header .bi,
        .chat-card .bi {
            display: inline-block !important;
            font-size: 1em;
            line-height: 1;
            vertical-align: middle;
            font-family: "bootstrap-icons" !important;
            font-style: normal;
            font-weight: normal;
            font-variant: normal;
            text-transform: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .chat-page-header .btn .bi {
            margin-right: 4px;
        }

        .chat-page-header .btn .bi:only-child {
            margin-right: 0;
        }

        /* Убеждаемся, что иконки видны */
        .chat-page-header i.bi,
        .chat-card i.bi {
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Карточка чата */
        .chat-card {
            border: none;
            box-shadow: none;
            flex: 1;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
            min-height: 0;
            overflow: visible;
        }

        .chat-card .card-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 16px;
            flex-shrink: 0;
        }

        .chat-card .card-header strong {
            font-size: 16px;
        }

        .chat-card .card-body {
            padding: 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        /* Форма ввода в стиле Telegram */
        .chat-card .card-footer {
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 8px 12px;
            padding-bottom: calc(8px + env(safe-area-inset-bottom, 0px));
            flex-shrink: 0;
            position: relative;
            z-index: 100;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .chat-input-form {
            margin: 0;
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100%;
        }

        .chat-input-form .d-flex {
            display: flex !important;
            width: 100%;
            align-items: center;
        }

        .chat-input-form .form-control {
            border-radius: 22px;
            border: 1px solid #e0e0e0;
            padding: 8px 16px;
            font-size: 15px;
            background: #f0f0f0;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex: 1;
            min-width: 0;
        }

        .chat-input-form textarea.form-control {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            overflow-x: hidden !important;
            resize: none !important;
        }

        .chat-input-form .form-control::-webkit-scrollbar {
            height: 4px;
        }

        .chat-input-form .form-control::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-input-form .form-control::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }

        .chat-input-form .form-control:focus {
            background: white;
            border-color: #3390ec;
            box-shadow: none;
        }

        .chat-input-form textarea.form-control:focus {
            white-space: pre-wrap !important;
            overflow-x: hidden !important;
        }

        .chat-input-form .btn-primary {
            border-radius: 22px;
            padding: 8px 20px;
            background: #3390ec;
            border: none;
            margin-left: 8px;
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0;
        }

        .chat-input-form .btn-primary:hover {
            background: #2a7fd4;
        }

        /* ПК ВЕРСИЯ (от 1025px) */
        @media (min-width: 1025px) {
            body.chat-page {
                background: #f5f5f5;
            }

            body.chat-page .container-fluid {
                padding: 20px !important;
                max-width: 1400px !important;
                margin: 0 auto !important;
            }

            body.chat-page .mobile-page-container {
                display: flex;
                flex-direction: row;
                gap: 20px;
                height: calc(100vh - 100px);
                min-height: 600px;
                max-height: 900px;
                padding: 0 !important;
            }

            /* Контейнер для списка диалогов */
            .desktop-conversations-sidebar {
                width: 380px;
                min-width: 380px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .desktop-conversations-sidebar .card {
                border: none;
                box-shadow: none;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .desktop-conversations-sidebar .card-header {
                padding: 16px 20px;
                border-bottom: 1px solid #e0e0e0;
                background: white;
                flex-shrink: 0;
            }

            .desktop-conversations-sidebar .card-header h5 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }

            .desktop-conversations-sidebar .card-body {
                flex: 1;
                overflow-y: auto;
                padding: 8px;
                min-height: 0;
            }

            .desktop-conversations-sidebar .list-group-item {
                border-radius: 8px;
                margin-bottom: 4px;
                border: 1px solid transparent;
                transition: all 0.2s;
            }

            .desktop-conversations-sidebar .list-group-item:hover {
                background: #f5f5f5;
                border-color: #e0e0e0;
            }

            .desktop-conversations-sidebar .list-group-item.active {
                background: #e3f2fd;
                border-color: #3390ec;
            }

            .desktop-conversations-sidebar .list-group-item.active a {
                color: #1976d2 !important;
            }

            /* Контейнер для чата */
            .desktop-chat-container {
                flex: 1;
                min-width: 0;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            .desktop-chat-container .chat-page-header {
                border-radius: 12px 12px 0 0;
                padding: 16px 20px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .desktop-chat-container .chat-page-header h2 {
                font-size: 20px;
                font-weight: 600;
            }

            .desktop-chat-container .chat-card {
                flex: 1;
                min-height: 0;
                height: auto;
                border-radius: 0 0 12px 12px;
            }

            .desktop-chat-container #messages-container-desktop {
                padding: 20px 24px;
                max-height: none;
                overflow-y: auto;
                flex: 1;
                min-height: 0;
            }

            .desktop-chat-container .message-bubble {
                max-width: 60%;
                padding: 10px 14px;
            }

            .desktop-chat-container .message-bubble .message-content {
                font-size: 15px;
                line-height: 1.5;
            }

            .desktop-chat-container .chat-card .card-footer {
                padding: 16px 20px;
                border-top: 1px solid #e0e0e0;
                border-radius: 0 0 12px 12px;
            }

            .desktop-chat-container .chat-input-form .form-control {
                padding: 10px 18px;
                font-size: 15px;
                border-radius: 24px;
            }

            .desktop-chat-container .chat-input-form .btn-primary {
                padding: 10px 24px;
                border-radius: 24px;
            }

            /* Уведомления на ПК */
            .desktop-notifications-section {
                margin-bottom: 20px;
                background: white;
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .desktop-notifications-section .card {
                border: none;
                box-shadow: none;
            }

            .desktop-notifications-section .card-header {
                padding: 16px 20px;
                border-bottom: 1px solid #e0e0e0;
            }

            .desktop-notifications-section .card-header h5 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }

            .desktop-notifications-section .card-body {
                max-height: 250px;
                padding: 12px;
            }

            /* Скрываем кнопку "Назад" на ПК */
            .desktop-chat-container .chat-page-header .btn-secondary {
                display: none !important;
            }

            /* Улучшаем скроллбары на ПК */
            .desktop-conversations-sidebar .card-body::-webkit-scrollbar,
            .desktop-chat-container #messages-container-desktop::-webkit-scrollbar {
                width: 8px;
            }

            .desktop-conversations-sidebar .card-body::-webkit-scrollbar-track,
            .desktop-chat-container #messages-container-desktop::-webkit-scrollbar-track {
                background: #f5f5f5;
                border-radius: 4px;
            }

            .desktop-conversations-sidebar .card-body::-webkit-scrollbar-thumb,
            .desktop-chat-container #messages-container-desktop::-webkit-scrollbar-thumb {
                background: #c0c0c0;
                border-radius: 4px;
            }

            .desktop-conversations-sidebar .card-body::-webkit-scrollbar-thumb:hover,
            .desktop-chat-container #messages-container-desktop::-webkit-scrollbar-thumb:hover {
                background: #a0a0a0;
            }
        }

        /* ПЛАНШЕТЫ (768px - 1024px) */
        @media (min-width: 768px) and (max-width: 1024px) {
            .chat-page-header {
                padding: clamp(10px, 1.5vw, 14px) clamp(12px, 2vw, 18px);
            }

            .chat-page-header h2 {
                font-size: clamp(16px, 2.2vw, 20px);
            }

            #messages-container {
                padding: clamp(10px, 1.5vw, 14px) clamp(12px, 2vw, 18px);
            }

            .message-bubble {
                max-width: 75%;
                padding: clamp(8px, 1.2vw, 12px) clamp(10px, 1.5vw, 14px);
            }

            .message-bubble .message-content {
                font-size: clamp(14px, 1.6vw, 16px);
            }

            .chat-card .card-footer {
                padding: clamp(8px, 1.2vw, 12px) clamp(10px, 1.5vw, 14px);
            }

            .chat-input-form .form-control {
                font-size: clamp(14px, 1.6vw, 16px);
                padding: clamp(7px, 1vw, 10px) clamp(12px, 1.8vw, 16px);
            }

            .chat-input-form textarea.form-control {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
                overflow-x: hidden !important;
            }

            .chat-input-form .btn-primary {
                padding: clamp(7px, 1vw, 10px) clamp(16px, 2.2vw, 20px);
            }
        }

        /* МОБИЛЬНЫЕ УСТРОЙСТВА (до 767px) */
        @media (max-width: 767px) {
            body.chat-page {
                background: #e5e5e5;
                height: 100vh;
                height: 100dvh;
                overflow: hidden;
            }

            body.chat-page .mobile-top-nav {
                display: none !important;
            }

            .mobile-page-container {
                height: 100vh;
                height: 100dvh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
                padding-bottom: env(safe-area-inset-bottom, 0px);
                box-sizing: border-box;
            }

            .chat-page-header {
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
                position: sticky;
                top: 0;
                z-index: 100;
                flex-shrink: 0;
                overflow: visible;
            }

            .chat-page-header .d-flex {
                flex-wrap: nowrap;
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                overflow: visible;
            }

            .chat-page-header .btn {
                display: inline-flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                z-index: 101;
                position: relative;
                flex-shrink: 0;
                min-width: auto;
                align-items: center !important;
                justify-content: center !important;
            }

            .chat-page-header .dropdown button.btn {
                padding: clamp(4px, 1.2vw, 6px) clamp(8px, 2vw, 10px);
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .chat-page-header .dropdown button.btn i.bi-three-dots-vertical {
                margin: 0 !important;
            }

            .chat-page-header .btn-danger {
                padding: clamp(4px, 1.2vw, 6px) clamp(8px, 2vw, 12px);
                font-size: clamp(12px, 3.5vw, 16px);
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: clamp(14px, 4vw, 18px);
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: clamp(120px, 30vw, 200px);
            }

            .chat-card {
                flex: 1;
                min-height: 0;
                display: flex;
                flex-direction: column;
                height: 0;
                overflow: visible;
            }

            #messages-container {
                flex: 1;
                min-height: 0;
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .chat-card .card-footer {
                padding: clamp(4px, 1.5vw, 8px) clamp(6px, 2vw, 10px);
                padding-bottom: calc(clamp(4px, 1.5vw, 8px) + env(safe-area-inset-bottom, 0px));
                flex-shrink: 0;
                background: white;
                z-index: 100;
                position: relative;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100%;
                box-sizing: border-box;
                margin-bottom: 0;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
            }

            .chat-input-form {
                margin: 0;
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                width: 100%;
            }

            .chat-input-form .d-flex {
                display: flex !important;
                width: 100%;
                align-items: center;
            }

            .chat-input-form .form-control {
                font-size: 16px;
                padding: clamp(6px, 1.8vw, 10px) clamp(10px, 3vw, 14px);
                min-height: clamp(36px, 10vw, 44px);
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                flex: 1;
                min-width: 0;
            }

            .chat-input-form textarea.form-control {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                resize: none !important;
            }

            .chat-input-form .btn-primary {
                padding: clamp(6px, 1.8vw, 10px) clamp(12px, 3.5vw, 18px);
                margin-left: clamp(4px, 1.5vw, 8px);
                min-width: clamp(44px, 12vw, 56px);
                min-height: clamp(36px, 10vw, 44px);
                display: inline-flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                flex-shrink: 0;
            }

            .message-bubble {
                max-width: clamp(75%, 85%, 90%);
                padding: clamp(6px, 2vw, 10px) clamp(8px, 2.5vw, 12px);
            }

            .message-bubble .message-sender {
                font-size: clamp(11px, 3.2vw, 14px);
                margin-bottom: clamp(2px, 0.6vw, 4px);
            }

            .message-bubble .message-content {
                font-size: clamp(13px, 3.8vw, 16px);
                line-height: 1.4;
            }

            .message-bubble .message-time {
                font-size: clamp(10px, 2.8vw, 12px);
                margin-top: clamp(2px, 0.6vw, 4px);
            }

            .message-item {
                margin-bottom: clamp(6px, 2vw, 10px);
            }
        }

        /* СРЕДНИЕ МОБИЛЬНЫЕ (481px - 767px) */
        @media (min-width: 481px) and (max-width: 767px) {
            .chat-page-header {
                padding: 10px 14px;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: 17px;
                max-width: 180px;
            }

            #messages-container {
                padding: 10px 14px;
            }

            .chat-card .card-footer {
                padding: 8px 10px;
            }

            .message-bubble {
                max-width: 80%;
                padding: 9px 11px;
            }

            .message-bubble .message-content {
                font-size: 15px;
            }
        }

        /* МАЛЕНЬКИЕ МОБИЛЬНЫЕ (375px - 480px) */
        @media (min-width: 375px) and (max-width: 480px) {
            .chat-page-header {
                padding: 8px 10px;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: 16px;
                max-width: 150px;
            }

            #messages-container {
                padding: 8px 10px;
            }

            .chat-card .card-footer {
                padding: 6px 8px;
            }

            .message-bubble {
                max-width: 85%;
                padding: 8px 10px;
            }

            .message-bubble .message-content {
                font-size: 14px;
            }
        }

        /* ОЧЕНЬ МАЛЕНЬКИЕ ЭКРАНЫ (320px - 374px) */
        @media (min-width: 320px) and (max-width: 374px) {
            .chat-page-header {
                padding: 6px 8px;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: 15px;
                max-width: 120px;
            }

            .chat-card .card-footer {
                padding: 5px 7px;
            }

            .chat-input-form .form-control {
                padding: 6px 10px;
            }

            .chat-input-form textarea.form-control {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
                overflow-x: hidden !important;
            }

            .chat-input-form .btn-primary {
                padding: 6px 12px;
                margin-left: 4px;
            }

            #messages-container {
                padding: 6px 8px;
            }

            .message-bubble {
                max-width: 88%;
                padding: 7px 9px;
            }

            .message-bubble .message-content {
                font-size: 13px;
            }
        }

        /* ЭКСТРА МАЛЕНЬКИЕ ЭКРАНЫ (до 320px) */
        @media (max-width: 319px) {
            .chat-page-header {
                padding: 5px 6px;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: 14px;
                max-width: 100px;
            }

            .chat-page-header .btn {
                font-size: 11px;
                padding: 4px 6px;
                display: inline-flex !important;
                visibility: visible !important;
                opacity: 1 !important;
                z-index: 101;
                position: relative;
                flex-shrink: 0;
                min-width: auto;
            }

            .chat-card .card-footer {
                padding: 4px 6px;
            }

            .chat-input-form .form-control {
                font-size: 16px;
                padding: 5px 8px;
                min-height: 34px;
            }

            .chat-input-form textarea.form-control {
                white-space: pre-wrap !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
                overflow-x: hidden !important;
            }

            .chat-input-form .btn-primary {
                padding: 5px 10px;
                margin-left: 3px;
                min-width: 40px;
                min-height: 34px;
            }

            #messages-container {
                padding: 5px 6px;
            }

            .message-bubble {
                max-width: 92%;
                padding: 6px 8px;
            }

            .message-bubble .message-sender {
                font-size: 11px;
            }

            .message-bubble .message-content {
                font-size: 12px;
            }

            .message-bubble .message-time {
                font-size: 10px;
            }

            .message-item {
                margin-bottom: 6px;
            }
        }

        /* ЛАНДШАФТНАЯ ОРИЕНТАЦИЯ НА МОБИЛЬНЫХ */
        @media (max-width: 767px) and (orientation: landscape) {
            .chat-page-header {
                padding: 6px 10px;
            }

            .chat-page-header h2,
            .chat-page-header h4 {
                font-size: clamp(14px, 2.5vw, 16px);
            }

            .chat-card .card-footer {
                padding: 5px 8px;
            }

            .message-bubble {
                max-width: 70%;
            }
        }
    </style>
<?php endif; ?>

<style>
    /* Стили для списка диалогов */
    .list-group-item {
        position: relative;
    }

    .list-group-item .delete-conversation-form {
        opacity: 0;
        transition: opacity 0.2s;
    }

    .list-group-item:hover .delete-conversation-form {
        opacity: 1;
    }

    .list-group-item .delete-conversation-form {
        display: flex;
        align-items: stretch;
    }

    .list-group-item .d-flex.gap-1 {
        align-items: stretch;
    }

    .list-group-item .block-and-delete-dialog-btn,
    .list-group-item .delete-dialog-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        height: 36px;
        width: 36px;
        padding: 0;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        flex-shrink: 0;
    }

    .list-group-item .block-and-delete-dialog-btn {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .list-group-item .block-and-delete-dialog-btn:hover {
        background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(146, 64, 14, 0.4);
    }

    .list-group-item .block-and-delete-dialog-btn:active {
        background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%);
        transform: scale(0.95);
    }

    .list-group-item .block-and-delete-dialog-btn i {
        font-size: 16px;
        transition: transform 0.2s ease;
    }

    .list-group-item .block-and-delete-dialog-btn:hover i {
        transform: rotate(15deg) scale(1.1);
    }

    .list-group-item .delete-dialog-btn {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    .list-group-item .delete-dialog-btn:hover {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
    }

    .list-group-item .delete-dialog-btn:active {
        background: linear-gradient(135deg, #fca5a5 0%, #ef4444 100%);
        transform: scale(0.95);
    }

    .list-group-item .delete-dialog-btn i {
        font-size: 16px;
        transition: transform 0.2s ease;
    }

    .list-group-item .delete-dialog-btn:hover i {
        transform: rotate(15deg) scale(1.1);
    }

    .list-group-item a {
        flex: 1;
        min-width: 0;
    }

    @media (max-width: 767px) {
        .list-group-item .delete-conversation-form {
            opacity: 1;
        }
        
        .list-group-item .block-and-delete-dialog-btn,
        .list-group-item .delete-dialog-btn {
            width: 32px;
            height: 32px;
            min-height: 32px;
        }

        .list-group-item .block-and-delete-dialog-btn i,
        .list-group-item .delete-dialog-btn i {
            font-size: 14px;
        }
        
        .desktop-notifications-section .card-header h5 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>

<div class="mobile-page-container">
    <?php if ($selectedUserId): ?>
        <!-- ПК ВЕРСИЯ: Две колонки (список диалогов + чат) -->
        <div class="desktop-conversations-sidebar d-none d-lg-flex">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Диалоги</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($conversations)): ?>
                        <p class="text-muted mb-0">Нет диалогов</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($conversations as $conv): ?>
                                <div class="list-group-item d-flex align-items-center <?= $conv['other_user_id'] == $selectedUserId ? 'active' : '' ?>">
                                    <a href="<?= BASE_URL ?>messages?user_id=<?= $conv['other_user_id'] ?>"
                                        class="list-group-item-action flex-grow-1 text-decoration-none" style="color: inherit;">
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($conv['photo'])): ?>
                                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $conv['photo'] ?>"
                                                    class="rounded-circle me-2"
                                                    style="width: 48px; height: 48px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center"
                                                    style="width: 48px; height: 48px;">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h6 class="mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0; font-size: 15px;"><?= Helper::escape($conv['other_user_full_name'] ?? $conv['other_user_email']) ?></h6>
                                                    <?php if (isset($conv['unread_count']) && $conv['unread_count'] > 0): ?>
                                                        <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.7rem; min-width: 18px; padding: 2px 6px; flex-shrink: 0;">
                                                            <?= $conv['unread_count'] > 99 ? '99+' : $conv['unread_count'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px;">
                                                    <?= Helper::escape($conv['last_message'] ?? '') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="block-and-delete-dialog-btn"
                                            data-user-id="<?= $conv['other_user_id'] ?>"
                                            title="Заблокировать и удалить">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>messages/deleteConversation"
                                            class="delete-conversation-form d-inline m-0"
                                            data-user-id="<?= $conv['other_user_id'] ?>">
                                            <input type="hidden" name="other_user_id" value="<?= $conv['other_user_id'] ?>">
                                            <button type="button" class="delete-dialog-btn"
                                                title="Удалить диалог">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ПК ВЕРСИЯ: Чат -->
        <div class="desktop-chat-container d-none d-lg-flex">
            <?php
            $userModel = new User();
            $selectedUser = $userModel->findById($selectedUserId);
            $photoModel = new UserPhoto();
            $photos = $photoModel->getByUserId($selectedUserId);
            $firstPhoto = !empty($photos) ? $photos[0]['photo'] : null;
            ?>
            <!-- Заголовок -->
            <div class="chat-page-header">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                        <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                            <?php if ($firstPhoto): ?>
                                <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $firstPhoto ?>"
                                    class="rounded-circle me-2 flex-shrink-0"
                                    style="width: 44px; height: 44px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width: 44px; height: 44px;">
                                    <i class="bi bi-person text-white" style="display: inline-block; font-size: 22px;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="min-width: 0; overflow: hidden;">
                                <h2 class="mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= Helper::escape($selectedUser['full_name'] ?? $selectedUser['email'] ?? 'Пользователь') ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center flex-shrink-0 ms-2">
                        <?php if ($isBlockedByOther): ?>
                            <span class="badge bg-warning text-dark">Собеседник вас заблокировал</span>
                        <?php elseif ($isBlockedByMe): ?>
                            <span class="badge bg-secondary">Пользователь заблокирован</span>
                        <?php else: ?>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="chatMenuButtonDesktop" data-bs-toggle="dropdown" aria-expanded="false" title="Меню">
                                    <i class="bi bi-three-dots-vertical" style="display: inline-block;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chatMenuButtonDesktop">
                                    <li>
                                        <button type="button" class="dropdown-item text-warning" onclick="event.stopPropagation(); blockAndDeleteConversation(<?= $selectedUserId ?>)">
                                            <i class="bi bi-lock-fill"></i> Заблокировать и удалить
                                        </button>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?= BASE_URL ?>messages/block" class="d-inline m-0" id="block-form-desktop">
                                            <input type="hidden" name="blocked_user_id" value="<?= $selectedUserId ?>">
                                            <button type="submit" class="dropdown-item" onclick="event.stopPropagation(); return confirm('Заблокировать этого пользователя?')">
                                                Заблокировать
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?= BASE_URL ?>messages/deleteConversation" class="d-inline m-0" id="delete-conversation-form-desktop">
                                            <input type="hidden" name="other_user_id" value="<?= $selectedUserId ?>">
                                            <button type="submit" class="dropdown-item text-danger" onclick="event.stopPropagation();">
                                                <i class="bi bi-trash"></i> Удалить диалог
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Чат -->
            <div class="card chat-card">
                <div class="card-body" id="messages-container-desktop">
                    <?php if ($isBlockedByOther): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Собеседник вас заблокировал. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif ($isBlockedByMe): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Вы заблокировали этого пользователя. Вы не можете видеть сообщения.</p>
                        </div>
                    <?php elseif (empty($messages)): ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <p class="mb-0">Нет сообщений. Начните общение!</p>
                        </div>
                    <?php else: ?>
                        <?php
                        foreach ($messages as $msg):
                            $isManager = isset($msg['from_role']) && $msg['from_role'] === 'manager';
                            $isAdmin = isset($msg['from_is_admin']) && $msg['from_is_admin'] == 1;
                            $isOwnMessage = $msg['from_user_id'] == Helper::getUserId();
                            $roleBadge = '';
                            if ($isAdmin) {
                                $roleBadge = '<span class="message-role-badge admin">Админ</span>';
                            } elseif ($isManager) {
                                $roleBadge = '<span class="message-role-badge manager">Менеджер</span>';
                            }
                        ?>
                            <div class="message-item <?= $isOwnMessage ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                                <div class="message-bubble <?= $isOwnMessage ? 'own' : 'other' ?>">
                                    <?php if (!$isOwnMessage): ?>
                                        <div class="message-sender">
                                            <?= Helper::escape($msg['from_full_name'] ?? $msg['from_email'] ?? 'Пользователь') ?>
                                            <?= $roleBadge ?>
                                        </div>
                                    <?php elseif ($roleBadge): ?>
                                        <div class="message-sender" style="display: block; margin-bottom: 4px;">
                                            <?= $roleBadge ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="message-content">
                                        <?= nl2br(Helper::escape($msg['message'])) ?>
                                    </div>
                                    <div class="message-time">
                                        <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!$isBlockedByMe && !$isBlockedByOther): ?>
                    <div class="card-footer">
                        <form method="POST" action="<?= BASE_URL ?>messages/send" id="message-form-desktop" class="chat-input-form">
                            <input type="hidden" name="to_user_id" value="<?= $selectedUserId ?>">
                            <div class="d-flex align-items-end">
                                <textarea
                                    class="form-control flex-grow-1"
                                    name="message"
                                    id="message-input-desktop"
                                    placeholder="Сообщение..."
                                    autocomplete="off"
                                    rows="1"
                                    style="min-height: 38px; max-height: 150px; resize: none; word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;"
                                    required></textarea>
                                <button type="submit" class="btn btn-primary ms-2">
                                    <i class="bi bi-send" style="display: inline-block;"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card-footer">
                        <div class="alert alert-warning mb-0" role="alert">
                            <?php if ($isBlockedByOther): ?>
                                Собеседник вас заблокировал. Вы не можете отправлять сообщения.
                            <?php elseif ($isBlockedByMe): ?>
                                Вы заблокировали этого пользователя. Вы не можете отправлять сообщения.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Список диалогов и уведомления (без выбранного пользователя) -->
    <div id="conversations-view" class="<?= $selectedUserId ? 'd-none d-lg-none' : '' ?>">
       
        <!-- Уведомления -->
        <div class="col-lg-8">
            <div class="row">
                <!-- Уведомления от администратора -->
                <div class="col-12 mb-4 desktop-notifications-section">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Уведомления от администратора</h5>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($unreadAdminCount > 0): ?>
                                    <span class="badge bg-warning text-dark"><?= $unreadAdminCount ?> новых</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($adminNotifications)): ?>
                                <p class="text-muted mb-0">Нет уведомлений от администратора</p>
                            <?php else: ?>
                                <div class="list-group" id="admin-notifications-list">
                                    <?php foreach ($adminNotifications as $notification): ?>
                                        <?php 
                                        $isManager = isset($notification['from_role']) && $notification['from_role'] === 'manager';
                                        $badgeText = $isManager ? 'Менеджер' : 'Администратор';
                                        $badgeClass = $isManager ? 'bg-info' : 'bg-danger';
                                        ?>
                                        <div class="list-group-item <?= ($notification['is_read'] == 0 || $notification['is_read'] === null) ? 'bg-light' : '' ?>" data-notification-id="<?= $notification['id'] ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <span class="badge <?= $badgeClass ?> me-2"><?= $badgeText ?></span>
                                                    </h6>
                                                    <p class="mb-1"><?= nl2br(Helper::escape($notification['message'])) ?></p>
                                                    <small class="text-muted">
                                                        <?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger ms-2 delete-admin-notification"
                                                    data-message-id="<?= $notification['id'] ?>"
                                                    title="Удалить уведомление">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Список диалогов -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Диалоги</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($conversations)): ?>
                            <p class="text-muted">Нет диалогов</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($conversations as $conv): ?>
                                    <div class="list-group-item d-flex align-items-center">
                                        <a href="<?= BASE_URL ?>messages?user_id=<?= $conv['other_user_id'] ?>"
                                            class="list-group-item-action flex-grow-1 text-decoration-none" style="color: inherit;">
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($conv['photo'])): ?>
                                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $conv['photo'] ?>"
                                                        class="rounded-circle me-2"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; min-width: 0;"><?= Helper::escape($conv['other_user_full_name'] ?? $conv['other_user_email']) ?></h6>
                                                        <?php if (isset($conv['unread_count']) && $conv['unread_count'] > 0): ?>
                                                            <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.7rem; min-width: 18px; padding: 2px 5px; flex-shrink: 0;">
                                                                <?= $conv['unread_count'] > 99 ? '99+' : $conv['unread_count'] ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted" style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?= Helper::escape($conv['last_message'] ?? '') ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="block-and-delete-dialog-btn"
                                                data-user-id="<?= $conv['other_user_id'] ?>"
                                                title="Заблокировать и удалить">
                                                <i class="bi bi-lock-fill"></i>
                                            </button>
                                            <form method="POST" action="<?= BASE_URL ?>messages/deleteConversation"
                                                class="delete-conversation-form d-inline m-0"
                                                data-user-id="<?= $conv['other_user_id'] ?>">
                                                <input type="hidden" name="other_user_id" value="<?= $conv['other_user_id'] ?>">
                                                <button type="button" class="delete-dialog-btn"
                                                    title="Удалить диалог">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- МОБИЛЬНАЯ ВЕРСИЯ: Полноэкранный чат -->
    <?php if ($selectedUserId): ?>
        <div class="d-lg-none">
            <?php
            $userModel = new User();
            $selectedUser = $userModel->findById($selectedUserId);
            $photoModel = new UserPhoto();
            $photos = $photoModel->getByUserId($selectedUserId);
            $firstPhoto = !empty($photos) ? $photos[0]['photo'] : null;
            ?>
            <div id="chat-view" class="mobile-page-container">
                <!-- Заголовок -->
                <div class="chat-page-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                            <button type="button" onclick="goBack()" class="btn btn-sm btn-secondary me-2 flex-shrink-0">
                                <i class="bi bi-arrow-left" style="display: inline-block;"></i> <span class="d-none d-md-inline">Назад</span>
                            </button>
                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                <?php if ($firstPhoto): ?>
                                    <img src="<?= BASE_URL . UPLOAD_DIR . 'photos/' . $firstPhoto ?>"
                                        class="rounded-circle me-2 flex-shrink-0"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-person text-white" style="display: inline-block; font-size: 20px;"></i>
                                    </div>
                                <?php endif; ?>
                                <div style="min-width: 0; overflow: hidden;">
                                    <h2 class="d-none d-md-block mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= Helper::escape($selectedUser['full_name'] ?? $selectedUser['email'] ?? 'Пользователь') ?></h2>
                                    <h4 class="d-block d-md-none mb-0" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= Helper::escape($selectedUser['full_name'] ?? $selectedUser['email'] ?? 'Пользователь') ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-shrink-0 ms-2">
                            <?php if ($isBlockedByOther): ?>
                                <span class="badge bg-warning text-dark">Собеседник вас заблокировал</span>
                            <?php elseif ($isBlockedByMe): ?>
                                <span class="badge bg-secondary">Пользователь заблокирован</span>
                            <?php else: ?>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" id="chatMenuButton" data-bs-toggle="dropdown" aria-expanded="false" title="Меню">
                                        <i class="bi bi-three-dots-vertical" style="display: inline-block;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chatMenuButton">
                                        <li>
                                            <button type="button" class="dropdown-item text-warning" onclick="event.stopPropagation(); blockAndDeleteConversation(<?= $selectedUserId ?>)">
                                                <i class="bi bi-lock-fill"></i> Заблокировать и удалить
                                            </button>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= BASE_URL ?>messages/block" class="d-inline m-0" id="block-form">
                                                <input type="hidden" name="blocked_user_id" value="<?= $selectedUserId ?>">
                                                <button type="submit" class="dropdown-item" onclick="event.stopPropagation(); return confirm('Заблокировать этого пользователя?')">
                                                    <i class="bi bi-lock-fill"></i> Заблокировать
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="<?= BASE_URL ?>messages/deleteConversation" class="d-inline m-0" id="delete-conversation-form">
                                                <input type="hidden" name="other_user_id" value="<?= $selectedUserId ?>">
                                                <button type="submit" class="dropdown-item text-danger" onclick="event.stopPropagation();">
                                                    <i class="bi bi-trash"></i> Удалить диалог
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Чат -->
                <div class="card chat-card">
                    <div class="card-body" id="messages-container">
                        <?php if ($isBlockedByOther): ?>
                            <div style="text-align: center; padding: 40px; color: #999;">
                                <p class="mb-0">Собеседник вас заблокировал. Вы не можете видеть сообщения.</p>
                            </div>
                        <?php elseif ($isBlockedByMe): ?>
                            <div style="text-align: center; padding: 40px; color: #999;">
                                <p class="mb-0">Вы заблокировали этого пользователя. Вы не можете видеть сообщения.</p>
                            </div>
                        <?php elseif (empty($messages)): ?>
                            <div style="text-align: center; padding: 40px; color: #999;">
                                <p class="mb-0">Нет сообщений. Начните общение!</p>
                            </div>
                        <?php else: ?>
                            <?php
                            foreach ($messages as $msg):
                                $isManager = isset($msg['from_role']) && $msg['from_role'] === 'manager';
                                $isAdmin = isset($msg['from_is_admin']) && $msg['from_is_admin'] == 1;
                                $isOwnMessage = $msg['from_user_id'] == Helper::getUserId();
                                $roleBadge = '';
                                if ($isAdmin) {
                                    $roleBadge = '<span class="message-role-badge admin">Админ</span>';
                                } elseif ($isManager) {
                                    $roleBadge = '<span class="message-role-badge manager">Менеджер</span>';
                                }
                            ?>
                                <div class="message-item <?= $isOwnMessage ? 'text-end' : 'text-start' ?>" data-message-id="<?= $msg['id'] ?>">
                                    <div class="message-bubble <?= $isOwnMessage ? 'own' : 'other' ?>">
                                        <?php if (!$isOwnMessage): ?>
                                            <div class="message-sender">
                                                <?= Helper::escape($msg['from_full_name'] ?? $msg['from_email'] ?? 'Пользователь') ?>
                                                <?= $roleBadge ?>
                                            </div>
                                        <?php elseif ($roleBadge): ?>
                                            <div class="message-sender" style="display: block; margin-bottom: 4px;">
                                                <?= $roleBadge ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="message-content">
                                            <?= nl2br(Helper::escape($msg['message'])) ?>
                                        </div>
                                        <div class="message-time">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!$isBlockedByMe && !$isBlockedByOther): ?>
                        <div class="card-footer">
                            <form method="POST" action="<?= BASE_URL ?>messages/send" id="message-form" class="chat-input-form">
                                <input type="hidden" name="to_user_id" value="<?= $selectedUserId ?>">
                                <div class="d-flex align-items-end">
                                    <textarea
                                        class="form-control flex-grow-1"
                                        name="message"
                                        id="message-input"
                                        placeholder="Сообщение..."
                                        autocomplete="off"
                                        rows="1"
                                        style="min-height: 38px; max-height: 150px; resize: none; word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;"
                                        required></textarea>
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="bi bi-send" style="display: inline-block;"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="card-footer">
                            <div class="alert alert-warning mb-0" role="alert">
                                <?php if ($isBlockedByOther): ?>
                                    Собеседник вас заблокировал. Вы не можете отправлять сообщения.
                                <?php elseif ($isBlockedByMe): ?>
                                    Вы заблокировали этого пользователя. Вы не можете отправлять сообщения.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($selectedUserId): ?>
    <script>
        // Функция для возврата назад или на страницу сообщений
        function goBack() {
            // Проверяем, есть ли история для возврата
            if (window.history.length > 1 && document.referrer) {
                // Если есть предыдущая страница, возвращаемся назад
                window.history.back();
            } else {
                // Иначе переходим на страницу сообщений
                window.location.href = '<?= BASE_URL ?>messages';
            }
        }

        // Передаем ID текущего пользователя в JavaScript
        window.currentUserId = <?= Helper::getUserId() ?>;
        window.selectedUserId = <?= $selectedUserId ?>;

        // Инициализация push-уведомлений для чата
        if (typeof window.PushNotifications !== 'undefined' && window.PushNotifications.isSupported()) {
            // Убеждаемся, что push-уведомления инициализированы
            if (typeof window.PushNotifications.init === 'function') {
                window.PushNotifications.init();
            }
        }

        // Функция для добавления сообщения в чат
        window.addMessageToChat = function(container, messageData) {
            const isOwnMessage = messageData.from_user_id == window.currentUserId;
            const isManager = messageData.from_role === 'manager';
            const isAdmin = messageData.from_is_admin === true || messageData.from_role === 'admin';
            const messageItem = document.createElement('div');
            messageItem.className = `message-item ${isOwnMessage ? 'text-end' : 'text-start'}`;
            messageItem.setAttribute('data-message-id', messageData.id);

            const bubbleClass = isOwnMessage ? 'own' : 'other';
            const date = new Date(messageData.created_at);
            const timeStr = date.toLocaleTimeString('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            });

            let roleBadge = '';
            if (isAdmin) {
                roleBadge = '<span class="message-role-badge admin">Админ</span>';
            } else if (isManager) {
                roleBadge = '<span class="message-role-badge manager">Менеджер</span>';
            }

            let senderHtml = '';
            if (!isOwnMessage) {
                const senderName = messageData.from_full_name || messageData.from_email || 'Пользователь';
                senderHtml = `<div class="message-sender">${senderName}${roleBadge}</div>`;
            } else if (roleBadge) {
                senderHtml = `<div class="message-sender" style="display: block; margin-bottom: 4px;">${roleBadge}</div>`;
            }

            messageItem.innerHTML = `
            <div class="message-bubble ${bubbleClass}">
                ${senderHtml}
                <div class="message-content">${(messageData.message || '').replace(/\n/g, '<br>')}</div>
                <div class="message-time">${timeStr}</div>
            </div>
        `;

            container.appendChild(messageItem);
            container.scrollTop = container.scrollHeight;
        };

        // Функция для автоматического изменения размера textarea
        window.autoResizeTextarea = function(textarea) {
            if (!textarea) return;

            // Сбрасываем высоту, чтобы получить правильный scrollHeight
            textarea.style.height = 'auto';

            // Вычисляем новую высоту на основе содержимого
            const scrollHeight = textarea.scrollHeight;
            const minHeight = 38;
            const maxHeight = 150;
            const newHeight = Math.max(minHeight, Math.min(scrollHeight, maxHeight));

            textarea.style.height = newHeight + 'px';

            // Если текст превышает максимальную высоту, показываем скролл
            if (scrollHeight > maxHeight) {
                textarea.style.overflowY = 'auto';
            } else {
                textarea.style.overflowY = 'hidden';
            }
        };

        // Инициализация textarea после загрузки DOM (мобильная и ПК версии)
        function initTextarea(textareaId, formId) {
            const messageInput = document.getElementById(textareaId);
            if (messageInput) {
                // Устанавливаем начальную высоту
                window.autoResizeTextarea(messageInput);

                // Автоматическое изменение размера при вводе
                messageInput.addEventListener('input', function() {
                    window.autoResizeTextarea(this);
                });

                // Автоматическое изменение размера при вставке текста
                messageInput.addEventListener('paste', function() {
                    setTimeout(() => window.autoResizeTextarea(this), 0);
                });

                // Обработка клавиш: Enter - отправка, Shift+Enter - новая строка
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const form = document.getElementById(formId);
                        if (form) {
                            form.dispatchEvent(new Event('submit'));
                        }
                    }
                });
            }
        }

        // Инициализируем обе версии
        initTextarea('message-input', 'message-form');
        initTextarea('message-input-desktop', 'message-form-desktop');

        // Функция для отправки сообщения через AJAX
        function setupMessageForm(formId, inputId, containerId) {
            const form = document.getElementById(formId);
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const messageInput = document.getElementById(inputId);
                const message = messageInput.value.trim();

                if (!message) return;

                const formData = new FormData(form);
                const messagesContainer = document.getElementById(containerId);

                fetch(BASE_URL + 'messages/send', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.message) {
                            // Очищаем поле ввода и сбрасываем высоту
                            messageInput.value = '';
                            if (typeof window.autoResizeTextarea === 'function') {
                                window.autoResizeTextarea(messageInput);
                            } else {
                                messageInput.style.height = 'auto';
                            }

                            // Добавляем сообщение в чат
                            if (messagesContainer && typeof window.addMessageToChat === 'function') {
                                window.addMessageToChat(messagesContainer, data.message);
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            } else {
                                // Если функция не доступна, перезагружаем страницу
                                window.location.reload();
                            }

                            // Обновляем также другую версию, если она существует
                            const otherContainerId = containerId === 'messages-container' ? 'messages-container-desktop' : 'messages-container';
                            const otherContainer = document.getElementById(otherContainerId);
                            if (otherContainer && typeof window.addMessageToChat === 'function') {
                                window.addMessageToChat(otherContainer, data.message);
                                otherContainer.scrollTop = otherContainer.scrollHeight;
                            }
                        } else {
                            const errorMsg = data?.error || 'Не удалось отправить сообщение. Попробуйте еще раз.';
                            alert(errorMsg);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при отправке сообщения:', error);
                        alert('Не удалось отправить сообщение. Попробуйте еще раз.');
                    });
            });
        }

        // Настраиваем формы для обеих версий
        setupMessageForm('message-form', 'message-input', 'messages-container');
        setupMessageForm('message-form-desktop', 'message-input-desktop', 'messages-container-desktop');

        // Прокрутка к последнему сообщению (обе версии)
        function scrollToBottom() {
            const mobileContainer = document.getElementById('messages-container');
            const desktopContainer = document.getElementById('messages-container-desktop');

            if (mobileContainer) {
                mobileContainer.scrollTop = mobileContainer.scrollHeight;
            }
            if (desktopContainer) {
                desktopContainer.scrollTop = desktopContainer.scrollHeight;
            }
        }

        setTimeout(scrollToBottom, 100);

        // Автоматическая высота контейнера (только для десктопной версии)
        function adjustMessagesContainerHeight() {
            // На мобильных и планшетах используем flexbox, не устанавливаем фиксированную высоту
            if (window.innerWidth <= 1024) {
                return;
            }

            const desktopContainer = document.getElementById('messages-container-desktop');
            if (desktopContainer) {
                const headerHeight = document.querySelector('.desktop-chat-container .chat-page-header')?.offsetHeight || 0;
                const cardFooterHeight = document.querySelector('.desktop-chat-container .chat-card .card-footer')?.offsetHeight || 0;
                const parentHeight = desktopContainer.closest('.desktop-chat-container')?.offsetHeight || 0;
                const totalHeight = parentHeight - headerHeight - cardFooterHeight;
                desktopContainer.style.height = totalHeight + 'px';
            }
        }

        // Обработка изменения ориентации экрана
        function handleOrientationChange() {
            setTimeout(() => {
                adjustMessagesContainerHeight();
                scrollToBottom();
            }, 100);
        }

        window.addEventListener('resize', adjustMessagesContainerHeight);
        window.addEventListener('orientationchange', handleOrientationChange);
        window.addEventListener('load', adjustMessagesContainerHeight);
        setTimeout(adjustMessagesContainerHeight, 100);

        // Обработка виртуальной клавиатуры на мобильных устройствах
        if (window.innerWidth <= 767) {
            let viewportHeight = window.innerHeight;

            // Функция для обеспечения видимости footer
            function ensureFooterVisible() {
                const footer = document.querySelector('.chat-card .card-footer');
                if (footer) {
                    const rect = footer.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    const safeAreaBottom = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--safe-area-inset-bottom') || '0');

                    // Если footer скрывается за нижней границей экрана
                    if (rect.bottom > viewportHeight - safeAreaBottom) {
                        footer.style.marginBottom = '0';
                        // Прокручиваем страницу, чтобы footer был виден
                        footer.scrollIntoView({
                            behavior: 'smooth',
                            block: 'end'
                        });
                    }
                }
            }

            window.addEventListener('resize', function() {
                const currentHeight = window.innerHeight;
                // Если высота уменьшилась более чем на 150px, вероятно открылась клавиатура
                if (currentHeight < viewportHeight - 150) {
                    // Клавиатура открыта
                    setTimeout(() => {
                        scrollToBottom();
                        ensureFooterVisible();
                    }, 300);
                } else {
                    viewportHeight = currentHeight;
                    ensureFooterVisible();
                }
            });

            // Проверяем видимость footer при загрузке и после изменений
            window.addEventListener('load', ensureFooterVisible);
            window.addEventListener('scroll', ensureFooterVisible);
            setTimeout(ensureFooterVisible, 500);
        }
    </script>
<?php endif; ?>

<script>
    // Обработка очистки уведомлений через AJAX
    document.getElementById('clear-notifications-form')?.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!confirm('Очистить все уведомления?')) {
            return;
        }

        const form = this;
        const formData = new FormData(form);

        fetch(BASE_URL + 'messages/clearNotifications', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Перезагружаем страницу для обновления списка уведомлений
                    window.location.reload();
                } else {
                    alert('Не удалось очистить уведомления. Попробуйте еще раз.');
                }
            })
            .catch(error => {
                console.error('Ошибка при очистке уведомлений:', error);
                alert('Не удалось очистить уведомления. Попробуйте еще раз.');
            });
    });

    // Обработка блокировки пользователя через AJAX (мобильная и ПК версии)
    function setupBlockForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!confirm('Заблокировать этого пользователя?')) {
                return;
            }

            const formData = new FormData(form);

            fetch(BASE_URL + 'messages/block', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Перезагружаем страницу для обновления интерфейса
                        window.location.reload();
                    } else {
                        alert('Не удалось заблокировать пользователя. Попробуйте еще раз.');
                    }
                })
                .catch(error => {
                    console.error('Ошибка при блокировке пользователя:', error);
                    alert('Не удалось заблокировать пользователя. Попробуйте еще раз.');
                });
        });
    }

    setupBlockForm('block-form');
    setupBlockForm('block-form-desktop');

    // Функция для блокировки и удаления диалога
    window.blockAndDeleteConversation = function(userId) {
        if (!confirm('Вы уверены, что хотите заблокировать этот «чат»?\n\nПользователь будет заблокирован навсегда. Все ваши сообщения в этом чате будут безвозвратно удалены.')) {
            return;
        }

        const formData = new FormData();
        formData.append('other_user_id', userId);

        fetch(BASE_URL + 'messages/blockAndDeleteConversation', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.success === true) {
                    alert('Пользователь заблокирован и диалог удален');
                    // Перезагружаем страницу
                    window.location.reload();
                } else {
                    const errorMsg = data?.error || data?.message || 'Не удалось заблокировать пользователя и удалить диалог. Попробуйте еще раз.';
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Ошибка при блокировке и удалении диалога:', error);
                alert('Произошла ошибка при блокировке и удалении диалога. Попробуйте еще раз.');
            });
    };

    // Обработка кнопки "Заблокировать и удалить" в списке диалогов
    document.querySelectorAll('.block-and-delete-dialog-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const userId = this.getAttribute('data-user-id');
            if (userId) {
                window.blockAndDeleteConversation(userId);
            }
        });
    });

    // Обработка удаления диалога из списка диалогов
    document.querySelectorAll('.delete-dialog-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (!confirm('Удалить этот диалог? Это действие нельзя отменить.')) {
                return;
            }

            const form = this.closest('.delete-conversation-form');
            const formData = new FormData(form);
            const otherUserId = formData.get('other_user_id');
            const listItem = form.closest('.list-group-item');

            if (!otherUserId) {
                alert('Ошибка: не указан ID пользователя');
                return;
            }

            fetch(BASE_URL + 'messages/deleteConversation', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Удаляем элемент диалога из списка
                        listItem.remove();

                        // Если диалогов не осталось, показываем сообщение
                        const listGroup = document.querySelector('.list-group');
                        if (listGroup && listGroup.children.length === 0) {
                            listGroup.innerHTML = '<p class="text-muted mb-0">Нет диалогов</p>';
                        }
                    } else {
                        const errorMsg = data.error || 'Не удалось удалить диалог';
                        alert('Ошибка: ' + errorMsg);
                        console.error('Ошибка удаления диалога:', data);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при удалении диалога:', error);
                    alert('Не удалось удалить диалог. Проверьте консоль для деталей.');
                });
        });
    });

    // Обработка удаления диалога через AJAX (для формы в чате - обе версии)
    function setupDeleteConversationForm(formId) {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!confirm('Удалить весь диалог? Это действие нельзя отменить.')) {
                return;
            }

            const formData = new FormData(form);
            const otherUserId = formData.get('other_user_id');

            if (!otherUserId) {
                alert('Ошибка: не указан ID пользователя');
                return;
            }

            fetch(BASE_URL + 'messages/deleteConversation', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Перенаправляем на страницу сообщений
                        window.location.href = BASE_URL + 'messages';
                    } else {
                        const errorMsg = data.error || 'Не удалось удалить диалог';
                        alert('Ошибка: ' + errorMsg);
                        console.error('Ошибка удаления диалога:', data);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при удалении диалога:', error);
                    alert('Не удалось удалить диалог. Проверьте консоль для деталей.');
                });
        });
    }

    setupDeleteConversationForm('delete-conversation-form');
    setupDeleteConversationForm('delete-conversation-form-desktop');

    // Обработчик удаления уведомления от администратора
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-admin-notification');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!confirm('Удалить это уведомление?')) {
                    return;
                }

                const messageId = this.getAttribute('data-message-id');
                const notificationItem = this.closest('.list-group-item');

                const formData = new FormData();
                formData.append('message_id', messageId);

                fetch(BASE_URL + 'messages/deleteAdminNotification', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Удаляем элемент из DOM
                            notificationItem.remove();

                            // Проверяем, остались ли уведомления
                            const notificationsList = document.getElementById('admin-notifications-list');
                            if (notificationsList && notificationsList.children.length === 0) {
                                notificationsList.innerHTML = '<p class="text-muted mb-0">Нет уведомлений от администратора</p>';
                            }

                            // Обновляем счетчик непрочитанных (если есть)
                            // Можно добавить логику обновления счетчика здесь
                        } else {
                            const errorMsg = data.error || 'Не удалось удалить уведомление';
                            alert('Ошибка: ' + errorMsg);
                            console.error('Ошибка удаления уведомления:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при удалении уведомления:', error);
                        alert('Не удалось удалить уведомление. Проверьте консоль для деталей.');
                    });
            });
        });
    });
</script>
</div>

<?php
$content = ob_get_clean();
$title = 'Сообщения';
// Добавляем класс для страницы чата, если открыт диалог
$bodyClass = $selectedUserId ? 'chat-page' : '';
include __DIR__ . '/../layout.php';
?>
