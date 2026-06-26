<?php
namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Logger;

use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\Roles;

interface BoardsControllerInterface {
    public function createBoard();
    public function getAllBoards();
    public function getBoard(string $boardId);
    public function deleteBoard(string $boardId);
}

class BoardsController extends Controller implements BoardsControllerInterface {
    private Boards $boards;
    private BoardsMember $boardsMember;
    private Roles $roles;

    function __construct(){
        $this->boards = new Boards();
        $this->request = new Request();
        $this->boardsMember = new BoardsMember();
        $this->roles = new Roles();
        parent::__construct(new Logger('Board.log'), new Response(), new Request());
    }

    function createBoard() {
        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';
        $user_id = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($user_id, FILTER_VALIDATE_INT) !== false && (int) $user_id > 0,
            'Unauthorized.',
            401
        )) {
            return;
        }

        if (!$this->validate(
            is_string($title) && trim($title) !== '' && mb_strlen(trim($title)) <= 100,
            'Title is required and must be at most 100 characters.',
            422
        )) {
            return;
        }

        if (!$this->validate(
            is_string($description) && mb_strlen(trim($description)) <= 255,
            'Description must be at most 255 characters.',
            422
        )) {
            return;
        }

        $title = trim($title);
        $description = trim($description);

        $boardCreate = $this->boards->create(['title', 'description', 'owner_id'], [$title, $description, $user_id]);

        if (!$this->validate($boardCreate, 'Unable to create board.', 500)) {
            return;
        }

        $board = $this->boards->findOneBy(['id'], 'title', $title);

        if (!$this->validate($board !== null, 'Created board was not found.', 500)) {
            return;
        }

        $role = $this->roles->findOneBy(['id'], 'name', 'admin');

        if (!$this->validate($role !== null, 'Administrator role was not found.', 500)) {
            return;
        }

        $boardsMemberCreate = $this->boardsMember->create(
            ['board_id', 'user_id', 'role_id'],
            [(int) $board['id'], (int) $user_id, (int) $role['id']]
        );

        if (!$this->validate($boardsMemberCreate, 'Unable to add board administrator.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'boards created'
        ], 201);
    }

    public function deleteBoard(string $boardId) {
        $user_id = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($user_id, FILTER_VALIDATE_INT) !== false && (int) $user_id > 0,
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

        $boardId = (int) $boardId;
        $board = $this->boards->find(['id'], $boardId);

        if (!$this->validate($board !== null, 'Board was not found.', 404)) {
            return;
        }

        $is_admin = $this->boardsMember->checkRoleAdmin($boardId);

        if (!$this->validate($is_admin, 'Only the board administrator can delete the board.', 403)) {
            return;
        }

        $allMembers = $this->boardsMember->findAll(['user_id'], 'board_id', $boardId);

        foreach($allMembers as $member) {
            $memberDeleted = $this->boardsMember->deleteBoardMember($boardId, (int) $member['user_id']);

            if (!$this->validate($memberDeleted, 'Unable to delete board member.', 500)) {
                return;
            }
        }

        $boardDeleted = $this->boards->delete($boardId);

        if (!$this->validate($boardDeleted, 'Unable to delete board.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Board deleted successfully.',
        ], 200);
    }

    public function getAllBoards() {
        $userId = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($userId, FILTER_VALIDATE_INT) !== false && (int) $userId > 0,
            'Unauthorized.',
            401
        )) {
            return;
        }

        
        $memberships = $this->boardsMember->findAll(
            ['board_id', 'role_id'],
            'user_id',
            (int) $userId
        );

        $countBoards = 0;
        $boards = [];
        
        foreach ($memberships as $membership) {
            $board = $this->boards->find(
                ['id', 'title', 'description', 'owner_id'],
                (int) $membership['board_id']
            );
            
            if ($board === null) {
                continue;
            }
            
            $role = $this->roles->find(['name'], (int) $membership['role_id']);
            
            if ($role === null) {
                continue;
            }
            
            $countMember = $this->boardsMember->findAll(['user_id'], 'board_id',(int) $membership['board_id']);

            $boards[] = [
                ...$board,
                'member_count' => count($countMember),
                'role' => $role['name'],
            ];

            $countBoards += 1;
        }

        return $this->response->json([
            'boards' => $boards,
            'stat' => [
                'boards_count' => $countBoards,
            ]
        ], 200);
    }

    public function getBoard(string $boardId) {
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

        $memberships = $this->boardsMember->findAll(
            ['board_id', 'role_id'],
            'user_id',
            (int) $userId
        );

        $membership = null;

        foreach ($memberships as $item) {
            if ((int) $item['board_id'] === (int) $boardId) {
                $membership = $item;
                break;
            }
        }

        if (!$this->validate($membership !== null, 'Board was not found.', 404)) {
            return;
        }

        $role = $this->roles->find(['name'], (int) $membership['role_id']);

        if (!$this->validate($role !== null, 'Board role was not found.', 500)) {
            return;
        }

        $board = $this->boards->find(['id', 'title', 'description', 'owner_id'], (int) $boardId);

        if (!$this->validate($board !== null, 'Board was not found.', 404)) {
            return;
        }

        $members = $this->boardsMember->findAll(['user_id'], 'board_id', (int) $boardId);

        return $this->response->json([
            'board' => [
                ...$board,
                'member_count' => count($members),
                'role' => $role['name'],
            ],
        ], 200);
    }
}
