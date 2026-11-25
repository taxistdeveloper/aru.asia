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
        $user = $this->userModel->findById($userId);
        $aruCode = $this->userModel->ensureAruCode($userId);
        $isPartner = !empty($user['is_partner']);

        $people = $this->userModel->getPartnerPeople($userId);
        $knownEmails = [];
        foreach ($people as $person) {
            $email = strtolower(trim((string)($person['email'] ?? '')));
            if ($email !== '') {
                $knownEmails[$email] = true;
            }
        }
        $companiesWithoutAccount = [];
        foreach ($this->companyModel->getByPartner($userId) as $company) {
            $email = strtolower(trim((string)($company['contact_email'] ?? '')));
            if ($email === '' || !isset($knownEmails[$email])) {
                $companiesWithoutAccount[] = $company;
            }
        }

        $people = $this->attachPublications($people);

        $this->render('companies', [
            'people' => $people,
            'companiesWithoutAccount' => $companiesWithoutAccount,
            'user' => $user,
            'isPartner' => $isPartner,
            'partnerLink' => $isPartner && $aruCode ? PartnerReferral::buildLink($aruCode) : null,
        ]);
    }

    public function balance()
    {
        $this->requireUser();
        Helper::redirect('profile/earnings');
    }

    public function history()
    {
        $userId = $this->requireUser();
        $this->render('history', [
            'tree' => $this->buildEarningsTree($this->earningModel->getByPartnerWithPeople($userId)),
        ]);
    }

    /**
     * Для каждого человека находит его рекламу и мероприятие (последние, кроме отклонённых)
     * и сортирует: сначала с активной публикацией, затем на модерации, затем завершённые, затем без публикаций.
     */
    private function attachPublications(array $people): array
    {
        if (empty($people)) {
            return $people;
        }
        $db = Database::getInstance()->getConnection();
        $adStmt = $db->prepare("
            SELECT id, image_path, status, start_date, end_date,
                   (status = 'active' AND end_date >= NOW()) AS is_live
            FROM ads
            WHERE LOWER(TRIM(advertiser_email)) = LOWER(TRIM(:email))
              AND status IN ('active', 'pending', 'expired')
            ORDER BY is_live DESC, end_date DESC
            LIMIT 1
        ");
        $eventStmt = $db->prepare("
            SELECT id, title, photo, status, event_date,
                   (status = 'approved' AND event_date >= NOW()) AS is_live
            FROM events
            WHERE user_id = :uid AND status IN ('approved', 'pending')
            ORDER BY is_live DESC, event_date DESC
            LIMIT 1
        ");

        $now = time();
        foreach ($people as $i => $person) {
            $pubs = [];
            $email = trim((string)($person['email'] ?? ''));
            if ($email !== '') {
                $adStmt->execute([':email' => $email]);
                if ($ad = $adStmt->fetch()) {
                    $pubs[] = [
                        'type' => 'Реклама',
                        'image' => !empty($ad['image_path']) ? UPLOAD_DIR . 'ads/' . $ad['image_path'] : null,
                        'state' => (int)$ad['is_live'] === 1
                            ? (strtotime($ad['start_date']) > $now ? 'scheduled' : 'live')
                            : ($ad['status'] === 'pending' ? 'pending' : 'finished'),
                        'until' => $ad['end_date'],
                    ];
                }
            }
            $eventStmt->execute([':uid' => (int)$person['id']]);
            if ($ev = $eventStmt->fetch()) {
                $pubs[] = [
                    'type' => 'Мероприятие',
                    'image' => !empty($ev['photo']) ? UPLOAD_DIR . 'photos/' . $ev['photo'] : null,
                    'state' => (int)$ev['is_live'] === 1 ? 'live' : ($ev['status'] === 'pending' ? 'pending' : 'finished'),
                    'until' => $ev['event_date'],
                ];
            }

            // Активные — первыми в карточке
            $stateRank = ['live' => 0, 'scheduled' => 1, 'pending' => 2, 'finished' => 3];
            usort($pubs, function ($a, $b) use ($stateRank) {
                return $stateRank[$a['state']] <=> $stateRank[$b['state']];
            });

            $people[$i]['publications'] = $pubs;
            $people[$i]['_rank'] = empty($pubs) ? 4 : $stateRank[$pubs[0]['state']];
            $people[$i]['_order'] = $i;
        }

        usort($people, function ($a, $b) {
            return [$a['_rank'], $a['_order']] <=> [$b['_rank'], $b['_order']];
        });
        return $people;
    }

    /**
     * Начисления по папкам: [год => [месяц => ['total'=>.., 'people'=>[...]]]].
     * Года — новые сверху, месяцы — по порядку. Новый год появляется автоматически с первым начислением.
     */
    private function buildEarningsTree(array $earnings): array
    {
        $tree = [];
        foreach ($earnings as $e) {
            $ts = strtotime((string)$e['created_at']);
            $y = (int)date('Y', $ts);
            $m = (int)date('n', $ts);
            if (!isset($tree[$y][$m])) {
                $tree[$y][$m] = ['total' => 0.0, 'people' => []];
            }
            $amount = (float)$e['amount'];
            $tree[$y][$m]['total'] += $amount;

            if (!empty($e['person_id'])) {
                $key = 'u' . (int)$e['person_id'];
            } elseif (!empty($e['company_id'])) {
                $key = 'c' . (int)$e['company_id'];
            } else {
                $key = 'other';
            }
            if (!isset($tree[$y][$m]['people'][$key])) {
                $tree[$y][$m]['people'][$key] = [
                    'id' => !empty($e['person_id']) ? (int)$e['person_id'] : null,
                    'name' => trim((string)($e['person_name'] ?? '')) ?: (trim((string)($e['company_name'] ?? '')) ?: (trim((string)($e['ad_advertiser_name'] ?? '')) ?: 'Прочие начисления')),
                    'age' => (int)($e['person_age'] ?? 0),
                    'email' => $e['person_email'] ?? null,
                    'photo' => $e['person_photo'] ?? null,
                    'sum' => 0.0,
                    'count' => 0,
                ];
            }
            $tree[$y][$m]['people'][$key]['sum'] += $amount;
            $tree[$y][$m]['people'][$key]['count']++;
        }

        krsort($tree);
        foreach ($tree as $y => $months) {
            ksort($months);
            foreach ($months as $m => $data) {
                uasort($data['people'], function ($a, $b) {
                    return $b['sum'] <=> $a['sum'];
                });
                $months[$m] = $data;
            }
            $tree[$y] = $months;
        }
        return $tree;
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
