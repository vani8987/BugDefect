<?php

namespace App\Controllers;

use Core\Controller;
use Core\Logger;
use Core\Request;
use Core\Response;

use App\Models\Notification;
use App\Models\InviteBoardMember;
use App\Models\BoardsMember;
use App\Models\User;


interface NotificationControllerInterface {
    public function getAll(): void;
    public function markAllAsRead(): void;
    public function acceptInvite(int $boardId): void;
    public function rejectInvite(int $boardId): void;
}

class NotificationController extends Controller implements NotificationControllerInterface {
    private Notification $notification;
    private InviteBoardMember $inviteBoardMember;
    private BoardsMember $boardsMember;
    private User $user;

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?Notification $notification = null,
        ?InviteBoardMember $inviteBoardMember = null,
        ?BoardsMember $boardsMember = null,
        ?User $user = null
    ) {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $this->inviteBoardMember = $inviteBoardMember ?? new InviteBoardMember();
        $this->notification = $notification ?? new Notification();
        $this->boardsMember = $boardsMember ?? new BoardsMember($request);
        $this->user = $user ?? new User();

        parent::__construct($logger ?? new Logger('Notification.log'), $response, $request);
    }

    public function getAll(): void {
        $userId = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Необходимо авторизоваться', $userId, 401) === null) return;
        $userId = (int) $userId;

        $allNotification = $this->notification->findAll(
            ['*'],
            'user_id',
            $userId
        );

        $notifications = array_map(function (array $notification): array {
            if (isset($notification['data']) && is_string($notification['data'])) {
                $decodedData = json_decode($notification['data'], true);
                $notification['data'] = is_array($decodedData) ? $decodedData : null;

                if (
                    $notification['type'] === 'invite'
                    && isset($notification['data']['invite_id'])
                    && filter_var($notification['data']['invite_id'], FILTER_VALIDATE_INT) !== false
                ) {
                    $invite = $this->inviteBoardMember->find(
                        ['status'],
                        (int) $notification['data']['invite_id']
                    );

                    if ($invite !== null) {
                        $notification['data']['status'] = $invite['status'];
                    }
                }
            }

            return $notification;
        }, $allNotification);

        $this->response->json([
            'notifications' => $notifications
        ], 200);
    }

    public function markAllAsRead(): void {
        $userId = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Необходимо авторизоваться', $userId, 401) === null) return;
        $userId = (int) $userId;

        $allNotification = $this->notification->findAll(
            ['id', 'is_read'],
            'user_id',
            $userId
        );

        foreach ($allNotification as $notification) {
            if (isset($notification['is_read']) && (int) $notification['is_read'] === 0) {
                $updated = $this->notification->update(['is_read'], [1], (int) $notification['id']);

                if (!$this->validate($updated, 'Не удалось обновить уведомление', 500)) {
                    return;
                }
            }
        }

        $this->response->json([
            'message' => 'Все уведомления отмечены как прочитанные'
        ], 200);
    }

    public function acceptInvite(int $boardId): void {
        if ($this->positiveId('Некорректный id доски', $boardId) === null) return;
        $boardId = (int) $boardId;

        $inviteId = $this->request->getDataJson('invite_id');
        $userId = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Необходимо авторизоваться', $userId, 401) === null) return;
        $userId = (int) $userId;

        if ($this->positiveId('Некорректный id приглашения', $inviteId) === null) return;
        $inviteId = (int) $inviteId;

        $invite = $this->inviteBoardMember->find(
            ['id', 'board_id', 'invited_user_id', 'inviter_user_id', 'role_id', 'status'],
            $inviteId
        );

        if (!$this->validate($invite !== null, 'Приглашение не найдено', 404)) {
            return;
        }

        if (!$this->validate(
            (int) $invite['board_id'] === $boardId,
            'Приглашение не относится к этой доске',
            403
        )) {
            return;
        }

        if (!$this->validate(
            (int) $invite['invited_user_id'] === $userId,
            'Приглашение не принадлежит текущему пользователю',
            403
        )) {
            return;
        }

        if (!$this->validate($invite['status'] === 'pending', 'Приглашение уже обработано', 422)) {
            return;
        }

        $memberships = $this->boardsMember->findAll(['id', 'board_id'], 'user_id', $userId);

        foreach ($memberships as $membership) {
            if ((int) $membership['board_id'] === $boardId) {
                $this->response->json([
                    'message' => 'Пользователь уже состоит в этой доске'
                ], 422);
                return;
            }
        }

        $memberCreated = $this->boardsMember->create(
            ['board_id', 'user_id', 'role_id'],
            [$boardId, $userId, (int) $invite['role_id']]
        );

        if (!$this->validate($memberCreated, 'Не удалось добавить пользователя в доску', 500)) {
            return;
        }

        $inviteUpdated = $this->inviteBoardMember->update(['status'], ['accepted'], $inviteId);

        if (!$this->validate($inviteUpdated, 'Не удалось обновить приглашение', 500)) {
            return;
        }

        $user = $this->user->find(['name'], $userId);

        if (!$this->validate($user !== null, 'Пользователь не найден', 404)) {
            return;
        }

        $notificationData = json_encode([
            'invite_id' => $inviteId,
            'invited_user_id' => $userId,
            'board_id' => $boardId
        ]);

        if (!$this->validate($notificationData !== false, 'Некорректные данные уведомления', 500)) {
            return;
        }

        $notificationCreated = $this->notification->create(
            ['title', 'type', 'data', 'description', 'user_id'],
            [
                "Пользователь {$user['name']} принял приглашение",
                'info',
                $notificationData,
                "Пользователь {$user['name']} присоединился к доске.",
                (int) $invite['inviter_user_id']
            ]
        );

        if (!$this->validate($notificationCreated, 'Не удалось создать уведомление', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Приглашение принято'
        ], 200);
    }

    public function rejectInvite(int $boardId): void {
        if ($this->positiveId('Некорректный id доски', $boardId) === null) return;
        $boardId = (int) $boardId;

        $inviteId = $this->request->getDataJson('invite_id');
        $userId = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Необходимо авторизоваться', $userId, 401) === null) return;
        $userId = (int) $userId;

        if ($this->positiveId('Некорректный id приглашения', $inviteId) === null) return;
        $inviteId = (int) $inviteId;

        $invite = $this->inviteBoardMember->find(
            ['id', 'board_id', 'invited_user_id', 'inviter_user_id', 'status'],
            $inviteId
        );

        if (!$this->validate($invite !== null, 'Приглашение не найдено', 404)) {
            return;
        }

        if (!$this->validate(
            (int) $invite['board_id'] === $boardId,
            'Приглашение не относится к этой доске',
            403
        )) {
            return;
        }

        if (!$this->validate(
            (int) $invite['invited_user_id'] === $userId,
            'Приглашение не принадлежит текущему пользователю',
            403
        )) {
            return;
        }

        if (!$this->validate($invite['status'] === 'pending', 'Приглашение уже обработано', 422)) {
            return;
        }

        $inviteUpdated = $this->inviteBoardMember->update(['status'], ['declined'], $inviteId);

        if (!$this->validate($inviteUpdated, 'Не удалось обновить приглашение', 500)) {
            return;
        }

        $user = $this->user->find(['name'], $userId);

        if (!$this->validate($user !== null, 'Пользователь не найден', 404)) {
            return;
        }

        $notificationData = json_encode([
            'invite_id' => $inviteId,
            'invited_user_id' => $userId,
            'board_id' => $boardId
        ]);

        if (!$this->validate($notificationData !== false, 'Некорректные данные уведомления', 500)) {
            return;
        }

        $notificationCreated = $this->notification->create(
            ['title', 'type', 'data', 'description', 'user_id'],
            [
                "Пользователь {$user['name']} отклонил приглашение",
                'info',
                $notificationData,
                "Пользователь {$user['name']} отказался присоединиться к доске.",
                (int) $invite['inviter_user_id']
            ]
        );

        if (!$this->validate($notificationCreated, 'Не удалось создать уведомление', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Приглашение отклонено'
        ], 200);
    }
}
