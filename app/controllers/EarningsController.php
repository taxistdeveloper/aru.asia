<?php

/**
 * КАБИНЕТ ЗАРАБОТКА ПАРТНЁРА
 *
 * Доступ из личного кабинета: «Мой заработок»
 */

class EarningsController
{
    private $partnerModel;
    private $companyModel;
    private $earningModel;
    private $payoutModel;
    private $userModel;

    public function __construct()
    {
        $this->partnerModel = new Partner();
        $this->companyModel = new PartnerCompany();
        $this->earningModel = new PartnerEarning();
        $this->payoutModel = new PartnerPayout();
        $this->userModel = new User();
    }

    private function requireUser(): int
    {
        if (!Helper::isLoggedIn()) {
            Helper::redirect('auth/login');
            exit;
        }
        Helper::checkUserExists();
        return (int)Helper::getUserId();
    }

    private function render(string $view, array $data = []): void
    {
        $userId = (int)Helper::getUserId();
        $data['user'] = $data['user'] ?? $this->userModel->findById($userId);
        $data['balance'] = $data['balance'] ?? $this->partnerModel->getBalance($userId);
        $data['isMobile'] = View::isMobile();
        View::render('earnings/' . $view, $data);
    }

    /** Хаб кабинета заработка */
    public function index()
    {
        $userId = $this->requireUser();
        $user = $this->userModel->findById($userId);
        $aruCode = $this->userModel->ensureAruCode($userId);
        $isPartner = !empty($user['is_partner']);

        $this->render('index', [
            'user' => $user,
            'companiesCount' => $this->companyModel->countByPartner($userId),
            'isPartner' => $isPartner,
            'partnerLink' => $isPartner && $aruCode ? PartnerReferral::buildLink($aruCode) : null,
            'aruCode' => $aruCode,
        ]);
    }

    /**
     * Стать партнёром — активирует статус и выдаёт ссылку
     */
    public function becomePartner()
    {
        $userId = $this->requireUser();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('profile/earnings');
            return;
        }

        if ($this->userModel->becomePartner($userId)) {
            ActivityLogger::info('partner.become', 'Пользователь стал партнёром', 'user', $userId, null, $userId);
            $_SESSION['success_message'] = 'Вы партнёр! Скопируйте ссылку и отправляйте предпринимателям.';
        } else {
            $_SESSION['error_message'] = 'Не удалось активировать партнёрство';
        }

        Helper::redirect('profile/earnings');
    }

    public function instructions()
    {
        $userId = $this->requireUser();
        $user = $this->userModel->findById($userId);
        $aruCode = $this->userModel->ensureAruCode($userId);
        $isPartner = !empty($user['is_partner']);

        $this->render('instructions', [
            'user' => $user,
            'isPartner' => $isPartner,
            'partnerLink' => $isPartner && $aruCode ? PartnerReferral::buildLink($aruCode) : null,
        ]);
    }

    public function invite()
    {
        $userId = $this->requireUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->companyModel->create([
                'partner_user_id' => $userId,
                'company_name' => $_POST['company_name'] ?? '',
                'contact_name' => $_POST['contact_name'] ?? '',
                'contact_email' => $_POST['contact_email'] ?? '',
                'contact_phone' => $_POST['contact_phone'] ?? '',
                'website' => $_POST['website'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);

            if ($result['ok']) {
                ActivityLogger::info(
                    'partner.invite_company',
                    'Партнёр пригласил компанию',
                    'partner_company',
                    $result['id'] ?? null,
                    ['company_name' => $_POST['company_name'] ?? ''],
                    $userId
                );
                $_SESSION['success_message'] = 'Заявка отправлена. Компания закреплена за вами.';
                Helper::redirect('profile/earnings/companies');
                return;
            }

            $_SESSION['error_message'] = $result['error'] ?? 'Ошибка при отправке заявки';
            Helper::redirect('profile/earnings/invite');
            return;
        }

        $this->render('invite');
    }

    public function companies()
    {
        $userId = $this->requireUser();
        $this->render('companies', [
            'companies' => $this->companyModel->getByPartner($userId),
        ]);
    }

    public function balance()
    {
        $this->requireUser();
        $this->render('balance');
    }

    public function history()
    {
        $userId = $this->requireUser();
        $this->render('history', [
            'earnings' => $this->earningModel->getByPartner($userId),
        ]);
    }

    public function payouts()
    {
        $userId = $this->requireUser();
        $this->render('payouts', [
            'payouts' => $this->payoutModel->getByPartner($userId),
        ]);
    }

    public function withdraw()
    {
        $userId = $this->requireUser();
        $balance = $this->partnerModel->getBalance($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float)str_replace([' ', ','], ['', '.'], (string)($_POST['amount'] ?? '0'));
            $paymentDetails = trim((string)($_POST['payment_details'] ?? ''));

            if ($amount <= 0) {
                $_SESSION['error_message'] = 'Укажите сумму вывода';
                Helper::redirect('profile/earnings/withdraw');
                return;
            }

            if ($amount > $balance['available']) {
                $_SESSION['error_message'] = 'Сумма превышает доступный баланс (' .
                    Partner::formatMoney($balance['available']) . ')';
                Helper::redirect('profile/earnings/withdraw');
                return;
            }

            if ($paymentDetails === '') {
                $_SESSION['error_message'] = 'Укажите реквизиты для выплаты';
                Helper::redirect('profile/earnings/withdraw');
                return;
            }

            $id = $this->payoutModel->create([
                'partner_user_id' => $userId,
                'amount' => $amount,
                'payment_details' => $paymentDetails,
            ]);

            if ($id) {
                ActivityLogger::info(
                    'partner.withdraw_request',
                    'Заявка на вывод средств',
                    'partner_payout',
                    $id,
                    ['amount' => $amount],
                    $userId
                );
                $_SESSION['success_message'] = 'Заявка на вывод отправлена';
                Helper::redirect('profile/earnings/payouts');
                return;
            }

            $_SESSION['error_message'] = 'Не удалось создать заявку';
            Helper::redirect('profile/earnings/withdraw');
            return;
        }

        $this->render('withdraw', [
            'balance' => $balance,
        ]);
    }
}
