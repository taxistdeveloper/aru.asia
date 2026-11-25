<?php

/**
 * ОПЛАТЫ И ЧЕКИ
 *  - admin/payments               — ожидающие оплаты + архив чеков по годам/месяцам
 *  - admin/payments/confirm       — «Оплата получена» → чек на email, в сообщения и в архив
 *  - admin/payments/cancel        — отменить неоплаченную заявку
 *  - admin/receipts/view          — открыть/скачать чек из архива
 *  - receipts/view                — пользователь открывает свой чек
 */
class PaymentsController
{
    public function adminIndex()
    {
        Helper::requireAdmin();

        $tree = PaymentService::archiveTree();
        $year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
        $month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
        if (!$year && !empty($tree)) {
            $year = (int)array_key_first($tree);
        }
        if ($year && !$month && !empty($tree[$year])) {
            $month = (int)max(array_keys($tree[$year]));
        }
        $receipts = ($year && $month >= 1 && $month <= 12) ? PaymentService::receiptsForMonth($year, $month) : [];

        View::render('admin/payments', [
            'pending' => PaymentService::listByStatus('pending'),
            'tree' => $tree,
            'year' => $year,
            'month' => $month,
            'receipts' => $receipts,
        ]);
    }

    public function adminConfirm()
    {
        Helper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = PaymentService::confirm((int)($_POST['payment_id'] ?? 0), (int)Helper::getAdminId());
            if ($result['ok']) {
                $parts = ['Оплата подтверждена, чек № ' . $result['receipt'] . ' сохранён в архиве'];
                $parts[] = $result['email'] ? 'отправлен на email' : 'email НЕ отправлен (проверьте SMTP)';
                $parts[] = $result['message'] ? 'отправлен в сообщения пользователя' : 'сообщение на сайте не отправлено (нет аккаунта)';
                $_SESSION['success_message'] = implode(', ', $parts) . '.';
            } else {
                $_SESSION['error_message'] = $result['error'];
            }
        }
        Helper::redirect('admin/payments');
    }

    public function adminCancel()
    {
        Helper::requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['payment_id'] ?? 0);
            if (PaymentService::cancel($id)) {
                ActivityLogger::info('payment.cancel', 'Заявка на оплату отменена', 'payment', $id);
                $_SESSION['success_message'] = 'Заявка на оплату отменена';
            } else {
                $_SESSION['error_message'] = 'Не удалось отменить заявку';
            }
        }
        Helper::redirect('admin/payments');
    }

    public function adminReceipt()
    {
        Helper::requireAdmin();
        $payment = PaymentService::find((int)($_GET['id'] ?? 0));
        if (!$payment || $payment['status'] !== 'paid') {
            $_SESSION['error_message'] = 'Чек не найден';
            Helper::redirect('admin/payments');
            return;
        }
        $this->outputReceipt($payment, !empty($_GET['download']));
    }

    public function userReceipt()
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            return;
        }
        $payment = PaymentService::find((int)($_GET['id'] ?? 0));
        if (!$payment || $payment['status'] !== 'paid' || (int)$payment['user_id'] !== (int)Helper::getUserId()) {
            $_SESSION['error_message'] = 'Чек не найден';
            Helper::redirect('messages');
            return;
        }
        $this->outputReceipt($payment, !empty($_GET['download']));
    }

    private function outputReceipt(array $payment, bool $download): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        if ($download) {
            header('Content-Disposition: attachment; filename="' . $payment['receipt_number'] . '.html"');
        }
        echo PaymentService::receiptHtml($payment);
    }
}
