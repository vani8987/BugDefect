<?php

use App\Controllers\AuthController;
use App\Controllers\BoardMemberController;
use App\Controllers\BoardsController;
use App\Controllers\InviteBoardController;
use App\Controllers\NotificationController;
use App\Controllers\StatusesController;
use Core\Router;

// регистрация
Router::route('/api/register', 'POST', [AuthController::class, 'register'], false);
Router::route('/api/login', 'POST', [AuthController::class, 'login'], false);
Router::route('/api/logout', 'POST', [AuthController::class, 'logout'], true);
Router::route('/api/me', 'GET', [AuthController::class, 'user'], true);

// доска
Router::route('/api/createBoard', 'POST', [BoardsController::class, 'createBoard'], true);
Router::route('/api/boards', 'GET', [BoardsController::class, 'getAllBoards'], true);
Router::route('/api/boards/{boardId}', 'GET', [BoardsController::class, 'getBoard'], true);
Router::route('/api/boards/{boardId}', 'DELETE', [BoardsController::class, 'deleteBoard'], true);
Router::route('/api/boards/{boardId}/members', 'GET', [BoardMemberController::class, 'getUsers'], true);
Router::route('/api/boards/{boardId}/members/{userId}', 'DELETE', [BoardMemberController::class, 'deleteUser'], true);
Router::route('/api/board/{boardId}/invite', 'POST', [InviteBoardController::class, 'addUser'], true);

// уведомления
Router::route('/api/notifications', 'GET', [NotificationController::class, 'getAll'], true);
Router::route('/api/notifications/read', 'PATCH', [NotificationController::class, 'markAllAsRead'], true);
Router::route('/api/boards/{boardId}/invite/accept', 'POST', [NotificationController::class, 'acceptInvite'], true);
Router::route('/api/boards/{boardId}/invite/reject', 'POST', [NotificationController::class, 'rejectInvite'], true);

// статусы
Router::route('/api/status/{boardId}', "GET", [StatusesController::class, 'getAll'], true);
Router::route('/api/status/{boardId}', "POST", [StatusesController::class, 'createStatuses'], true);
Router::route('/api/status/{boardId}/position', "PATCH", [StatusesController::class, 'updatePosition'], true);
