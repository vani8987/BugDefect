<?php

namespace App\Controllers;

use Core\Controller;
use Core\Logger;
use Core\Request;
use Core\Response;
use App\Models\Notification;


interface NotificationControllerInterface {
    public function getAll(): void;
}

class NotificationController extends Controller implements NotificationControllerInterface {
    private Notification $notification;

    public function __construct()
    {
        $this->notification = new Notification();
        parent::__construct(new Logger('Notification.log'), new Response(), new Request());
    }

    public function getAll(): void {
        $userId = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($userId, FILTER_VALIDATE_INT) !== false && (int) $userId > 0,
            'Unauthorized',
            401
        )) {
            return;
        }

        $allNotification = $this->notification->findAll(
            ['*'],
            'user_id',
            (int) $userId
        );

        $notifications = array_map(function (array $notification): array {
            if (isset($notification['data']) && is_string($notification['data'])) {
                $decodedData = json_decode($notification['data'], true);
                $notification['data'] = is_array($decodedData) ? $decodedData : null;
            }

            return $notification;
        }, $allNotification);

        $this->response->json([
            'notifications' => $notifications
        ], 200);
    }

    public function markAllAsRead(): void {
        $userId = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($userId, FILTER_VALIDATE_INT) !== false && (int) $userId > 0,
            'Unauthorized',
            401
        )) {
            return;
        }

        $allNotification = $this->notification->findAll(
            ['*'],
            'user_id',
            (int) $userId
        );

        foreach($allNotification as $notification) {
            if (isset($notification['is_read']) && (int) $notification['is_read'] === 0) {
               $updated = $this->notification->update(['is_read'], [true], $notification['id']);

               if (!$this->validate($updated, 'Notification was not updated', 500)) {
                   return;
               }
            }

        }

        $this->response->json([
            'message' => 'all notifacation is read'
        ], 200);
    }
}
