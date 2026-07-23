<?php

use App\Controllers\AuthController;
use App\Controllers\BoardMemberController;
use App\Controllers\BoardsController;
use App\Controllers\DefectsController;
use App\Controllers\InviteBoardController;
use App\Controllers\NotificationController;
use App\Controllers\StatusesController;
use App\Middleware\AuthMiddleware;
use App\Middleware\BoardMiddleware;
use Core\Router;

$authMiddleware = [AuthMiddleware::class, ['userAuth']];
$boardAccessMiddleware = [BoardMiddleware::class, ['userAuth', 'boardAccess']];
$boardAdminMiddleware = [BoardMiddleware::class, ['userAuth', 'boardAdmin']];

// Регистрация и авторизация
Router::route('/api/register', 'POST', [AuthController::class, 'register'], false);
Router::route('/api/login', 'POST', [AuthController::class, 'login'], false);
Router::route('/api/logout', 'POST', [AuthController::class, 'logout'], false, $authMiddleware);
Router::route('/api/me', 'GET', [AuthController::class, 'user'], false, $authMiddleware);

// Доски
Router::route('/api/createBoard', 'POST', [BoardsController::class, 'createBoard'], false, $authMiddleware);
Router::route('/api/boards', 'GET', [BoardsController::class, 'getAllBoards'], false, $authMiddleware);
Router::route('/api/boards/{boardId}', 'GET', [BoardsController::class, 'getBoard'], false, $boardAccessMiddleware);
Router::route('/api/boards/{boardId}', 'DELETE', [BoardsController::class, 'deleteBoard'], false, $boardAdminMiddleware);
Router::route('/api/boards/{boardId}/members', 'GET', [BoardMemberController::class, 'getUsers'], false, $boardAccessMiddleware);
Router::route('/api/boards/{boardId}/members/{userId}', 'DELETE', [BoardMemberController::class, 'deleteUser'], false, $boardAdminMiddleware);
Router::route('/api/board/{boardId}/invite', 'POST', [InviteBoardController::class, 'addUser'], false, $boardAdminMiddleware);

// Уведомления
Router::route('/api/notifications', 'GET', [NotificationController::class, 'getAll'], false, $authMiddleware);
Router::route('/api/notifications/read', 'PATCH', [NotificationController::class, 'markAllAsRead'], false, $authMiddleware);
Router::route('/api/boards/{boardId}/invite/accept', 'POST', [NotificationController::class, 'acceptInvite'], false, $authMiddleware);
Router::route('/api/boards/{boardId}/invite/reject', 'POST', [NotificationController::class, 'rejectInvite'], false, $authMiddleware);

// Статусы
Router::route('/api/status/{boardId}', 'GET', [StatusesController::class, 'getAll'], false, $boardAccessMiddleware);
Router::route('/api/status/{boardId}', 'POST', [StatusesController::class, 'createStatuses'], false, $boardAdminMiddleware);
Router::route('/api/status/{boardId}/position', 'PATCH', [StatusesController::class, 'updatePosition'], false, $boardAdminMiddleware);
Router::route('/api/status/{boardId}/{statusId}', 'DELETE', [StatusesController::class, 'deleteStatus'], false, $boardAdminMiddleware);

// Дефекты
Router::route('/api/boards/{boardId}/defects', 'POST', [DefectsController::class, 'createDefect'], false, $boardAdminMiddleware);
Router::route('/api/boards/{boardId}/defects/{defectId}/move', 'PATCH', [DefectsController::class, 'moveDefect'], false, $boardAdminMiddleware);
Router::route('/api/boards/{boardId}/defects/{defectId}', 'DELETE', [DefectsController::class, 'deleteDefect'], false, $boardAdminMiddleware);
