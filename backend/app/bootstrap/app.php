<?php

namespace App\bootstrap;

use App\Controllers\AuthController;
use App\Controllers\BoardMemberController;
use App\Controllers\BoardsController;
use App\Controllers\DefectsController;
use App\Controllers\InviteBoardController;
use App\Controllers\NotificationController;
use App\Controllers\StatusesController;
use App\Middleware\AuthMiddleware;
use App\Middleware\BoardMiddleware;
use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\Defect;
use App\Models\InviteBoardMember;
use App\Models\Notification;
use App\Models\Roles;
use App\Models\Statuses;
use App\Models\User;
use Core\Auth;
use Core\Container;
use Core\ConnectDB;
use Core\Logger;
use Core\Request;
use Core\Response;
use Core\Router;

$container = new Container();

$container->bind(Logger::class, fn (): Logger => new Logger('system.log'));
$container->bind(Request::class, fn (Container $container): Request => new Request($container->make(Logger::class)));
$container->bind(Response::class, fn (Container $container): Response => new Response($container->make(Logger::class)));

$container->bind(ConnectDB::class, fn (): ConnectDB => new ConnectDB(new Logger('database.log')));
$container->bind(User::class, fn (): User => new User(new Logger('database.log')));
$container->bind(Roles::class, fn (): Roles => new Roles(new Logger('database.log')));
$container->bind(Boards::class, fn (): Boards => new Boards(new Logger('database.log')));
$container->bind(BoardsMember::class, fn (Container $container): BoardsMember => new BoardsMember(
    $container->make(Request::class),
    new Logger('database.log'),
));
$container->bind(Defect::class, fn (): Defect => new Defect(new Logger('database.log')));
$container->bind(Statuses::class, fn (): Statuses => new Statuses(new Logger('database.log')));
$container->bind(Notification::class, fn (): Notification => new Notification(new Logger('database.log')));
$container->bind(InviteBoardMember::class, fn (): InviteBoardMember => new InviteBoardMember(new Logger('database.log')));

$container->bind(Auth::class, fn (Container $container): Auth => new Auth(
    $container->make(Request::class),
    $container->make(User::class),
    new Logger('auth.log'),
));

$container->bind(AuthController::class, fn (Container $container): AuthController => new AuthController(
    $container->make(Auth::class),
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('User.log'),
    $container->make(User::class),
    $container->make(Roles::class),
));

$container->bind(BoardsController::class, fn (Container $container): BoardsController => new BoardsController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('Board.log'),
    $container->make(Boards::class),
    $container->make(BoardsMember::class),
    $container->make(Roles::class),
));

$container->bind(BoardMemberController::class, fn (Container $container): BoardMemberController => new BoardMemberController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('BoardMember.log'),
    $container->make(BoardsMember::class),
));

$container->bind(DefectsController::class, fn (Container $container): DefectsController => new DefectsController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('Defects.log'),
    $container->make(Defect::class),
    $container->make(Boards::class),
    $container->make(BoardsMember::class),
    $container->make(Statuses::class),
));

$container->bind(StatusesController::class, fn (Container $container): StatusesController => new StatusesController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('Statuses.log'),
    $container->make(Defect::class),
    $container->make(Statuses::class),
));

$container->bind(InviteBoardController::class, fn (Container $container): InviteBoardController => new InviteBoardController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('Board.log'),
    $container->make(Boards::class),
    $container->make(BoardsMember::class),
    $container->make(Roles::class),
    $container->make(Notification::class),
    $container->make(InviteBoardMember::class),
    $container->make(User::class),
));

$container->bind(NotificationController::class, fn (Container $container): NotificationController => new NotificationController(
    $container->make(Request::class),
    $container->make(Response::class),
    new Logger('Notification.log'),
    $container->make(Notification::class),
    $container->make(InviteBoardMember::class),
    $container->make(BoardsMember::class),
    $container->make(User::class),
));

$container->bind(AuthMiddleware::class, fn (Container $container): AuthMiddleware => new AuthMiddleware(
    $container->make(Request::class),
    $container->make(Logger::class),
    $container->make(User::class),
));

$container->bind(BoardMiddleware::class, fn (Container $container): BoardMiddleware => new BoardMiddleware(
    $container->make(Request::class),
    $container->make(Logger::class),
    $container->make(User::class),
    $container->make(Boards::class),
    $container->make(BoardsMember::class),
));

$container->bind(Router::class, fn (Container $container): Router => new Router(
    $container->make(Auth::class),
    $container,
    $container->make(Logger::class),
    $container->make(Response::class),
));

return $container;
