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
    private string $adminRoleName;

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?Boards $boards = null,
        ?BoardsMember $boardsMember = null,
        ?Roles $roles = null
    ) {
        $this->request = $request ?? new Request();
        $this->response = $response ?? new Response();
        $this->boards = $boards ?? new Boards();
        $this->boardsMember = $boardsMember ?? new BoardsMember($this->request);
        $this->roles = $roles ?? new Roles();
        $this->adminRoleName = $_ENV['BOARD_ROLE_ADMIN'] ?? getenv('BOARD_ROLE_ADMIN') ?: 'admin';
        parent::__construct($logger ?? new Logger('Board.log'), $this->response, $this->request);
    }

    function createBoard() {
        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';
        $user_id = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Unauthorized.', $user_id, 401) === null) return;
        $user_id = (int) $user_id;

        if (!$this->validateStringLength($title, 'Title is required and must be at most 100 characters.', countSymbol: 100)) {
            return;
        }

        if (!$this->validateStringLength($description, 'Description must be at most 255 characters.', required: false)) {
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

        $role = $this->roles->findOneBy(['id'], 'name', $this->adminRoleName);

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
        if ($this->positiveId('Board ID must be a positive number.', $boardId) === null) return;
        $boardId = (int) $boardId;

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

        if ($this->positiveId('Board ID must be a positive number.', $boardId) === null) return;
        $boardId = (int) $boardId;

        $membership = $this->boardsMember->findBoardMember((int) $boardId, (int) $userId);

        if (!$this->validate($membership !== null, 'Board member was not found.', 404)) {
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
