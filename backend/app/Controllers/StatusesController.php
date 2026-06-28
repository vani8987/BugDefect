<?php
namespace App\Controllers;

use Core\Controller;
use Core\Logger;
use Core\Response;
use Core\Request;

use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\Statuses;

class StatusesController extends Controller {
    private Boards $boards;
    private BoardsMember $boardsMember;
    private Statuses $statuses;

    function __construct(){
        $this->boards = new Boards();
        $this->boardsMember = new BoardsMember();
        $this->statuses = new Statuses();

        parent::__construct(new Logger('Statuses.log'), new Response(), new Request());
    }

    public function createStatuses(string $boardId) {
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
            'Board id is invalid',
            422
        )) {
            return;
        }

        $boardId = (int) $boardId;
        $board = $this->boards->find(['id'], $boardId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';
        $position = $this->request->getDataJson('position');

        $is_admin = $this->boardsMember->checkRoleAdmin($boardId);
        if (!$this->validate($is_admin, 'Only the board administrator can create statuses.', 403)) {
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

        if (!$this->validate(
            filter_var($position, FILTER_VALIDATE_INT) !== false && (int) $position > 0,
            'Position must be a positive number.',
            422
        )) {
            return;
        }
        
        $title = trim($title);
        $description = trim($description);
        $position = (int) $position;

        $createStatus = $this->statuses->create(
            ['title', 'board_id', 'position', 'description'],
            [$title, $boardId, $position, $description]
        );

        if (!$this->validate($createStatus, 'Unable to create status.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Status created successfully.',
        ], 201);
    }

    public function getAll(string $boardId) {
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
            'Board id is invalid',
            422
        )) {
            return;
        }

        $boardId = (int) $boardId;
        $board = $this->boards->find(['id'], $boardId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $member = $this->boardsMember->findBoardMember($boardId, (int) $userId);

        if (!$this->validate($member !== null, 'Access to this board is denied.', 403)) {
            return;
        }

        $allStatuses = $this->statuses->findAllByBoard($boardId);

        $this->response->json([
            'statuses' => $allStatuses
        ], 200);
    }

    public function updatePosition(string $boardId) {
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
            'Board id is invalid',
            422
        )) {
            return;
        }

        $boardId = (int) $boardId;
        $board = $this->boards->find(['id'], $boardId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $is_admin = $this->boardsMember->checkRoleAdmin($boardId);
        if (!$this->validate($is_admin, 'Only the board administrator can update statuses.', 403)) {
            return;
        }

        $allStatuses = $this->request->getDataJson('statuses');

        if (!$this->validate(is_array($allStatuses), 'Statuses must be an array.', 422)) {
            return;
        }

        foreach ($allStatuses as $status) {
            if (!$this->validate(
                is_array($status)
                && isset($status['id'], $status['position'])
                && filter_var($status['id'], FILTER_VALIDATE_INT) !== false
                && (int) $status['id'] > 0
                && filter_var($status['position'], FILTER_VALIDATE_INT) !== false
                && (int) $status['position'] > 0,
                'Status id and position must be positive numbers.',
                422
            )) {
                return;
            }

            $idStatus = $status['id'];
            $positionStatus = $status['position'];

            $idStatus = (int) $idStatus;
            $positionStatus = (int) $positionStatus;
            $statusInSql = $this->statuses->findPositionByBoardAndStatus($boardId, $idStatus);

            if (!$this->validate($statusInSql !== null, 'Status was not found in this board.', 404)) {
                return;
            }

            if ((int) $statusInSql['position'] !== $positionStatus) {
                $updated = $this->statuses->updatePositionByBoardAndStatus($boardId, $idStatus, $positionStatus);

                if (!$this->validate($updated, 'Unable to update status position.', 500)) {
                    return;
                }
            }
        }

        $this->response->json([
            'message' => 'position updated'
        ], 200);
    }

    public function deleteStatus(string $boardId, string $statusId) {
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

        if (!$this->validate(
            filter_var($statusId, FILTER_VALIDATE_INT) !== false && (int) $statusId > 0,
            'Status ID must be a positive number.',
            422
        )) {
            return;
        }

        $boardId = (int) $boardId;
        $statusId = (int) $statusId;
        $board = $this->boards->find(['id'], $boardId);

        if (!$this->validate($board !== null, 'Board was not found.', 404)) {
            return;
        }

        $is_admin = $this->boardsMember->checkRoleAdmin($boardId);

        if (!$this->validate($is_admin, 'Only the board administrator can delete statuses.', 403)) {
            return;
        }

        $statusDeleted = $this->statuses->deleteByBoardAndStatus($boardId, $statusId);

        if (!$this->validate($statusDeleted, 'Unable to delete status.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Status deleted successfully.',
        ], 200);
    }
}
