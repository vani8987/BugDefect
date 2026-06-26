<?php

namespace App\Controllers;

use Core\Controller;

use App\Models\BoardsMember;
use App\Models\User;
use App\Models\Roles;

use Core\Logger;
use Core\Request;
use Core\Response;

interface BoardMemberControllerInterface {
    public function getUsers(string $boardId): void;
}

class BoardMemberController extends Controller implements BoardMemberControllerInterface {
    private BoardsMember $boardMember;
    private User $user;
    private Roles $roles;

    public function __construct() {
        $this->boardMember = new BoardsMember();
        $this->user = new User();
        $this->roles = new Roles();

        parent::__construct(new Logger('BoardMember.log'), new Response(), new Request());
    }

    public function getUsers(string $boardId): void {
        $userId = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($userId, FILTER_VALIDATE_INT) !== false && (int) $userId > 0,
            'Unauthorized.',
            401
        )) {
            return;
        }

        if (!$this->validate(
            filter_var($boardId, FILTER_VALIDATE_INT) !== false && (int) $boardId > 0,
            'Board ID must be a positive number.',
            422
        )) {
            return;
        }

        $userBoards = $this->boardMember->findAll(['board_id'], 'user_id', (int) $userId);
        $hasAccess = false;

        foreach ($userBoards as $userBoard) {
            if ((int) $userBoard['board_id'] === (int) $boardId) {
                $hasAccess = true;
                break;
            }
        }

        if (!$this->validate($hasAccess, 'Access to this board is denied.', 403)) {
            return;
        }

        $members = $this->boardMember->getMembersWithRoles((int) $boardId);

        $this->response->json([
            'members' => $members,
        ], 200);
    }
}
