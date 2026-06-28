<?php
namespace App\Controllers;

use Core\Controller;
use Core\Logger;
use Core\Response;
use Core\Request;

use App\Models\Defect;
use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\Statuses;

class DefectsController extends Controller {
    private Defect $defect;
    private Boards $boards;
    private BoardsMember $boardsMember;
    private Statuses $statuses;

    function __construct(){
        $this->defect = new Defect();
        $this->boards = new Boards();
        $this->boardsMember = new BoardsMember();
        $this->statuses = new Statuses();

        parent::__construct(new Logger('Defects.log'), new Response(), new Request());
    }

    public function createDefect(int $boardId) {
        $appointed_user_id = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($appointed_user_id, FILTER_VALIDATE_INT) !== false && (int) $appointed_user_id > 0,
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

        $boardsId = (int) $boardId;
        $board = $this->boards->find(['id'], $boardsId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $is_admin = $this->boardsMember->checkRoleAdmin($boardsId);

        if (!$this->validate($is_admin, 'Only the board administrator can create defects.', 403)) {
            return;
        }

        $statusId = $this->request->getDataJson('statusId');
        $executorID = $this->request->getDataJson('executorID');
        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';

        if (!$this->validate(
            filter_var($statusId, FILTER_VALIDATE_INT) !== false && (int) $statusId > 0,
            'Status id is invalid',
            422
        )) {
            return;
        }

        if (!$this->validate(
            filter_var($executorID, FILTER_VALIDATE_INT) !== false && (int) $executorID > 0,
            'Executor id is invalid',
            422
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
            is_string($description) &&
            mb_strlen(trim($description)) <= 5000,
            'Description must be at most 5000 characters.',
            422
        )) {
            return;
        }

        $appointed_user_id = (int) $appointed_user_id;
        $statusId = (int) $statusId;
        $executorID = (int) $executorID;
        $title = trim($title);
        $description = trim($description);

        $status = $this->statuses->findPositionByBoardAndStatus($boardsId, $statusId);

        if (!$this->validate($status !== null, 'Status was not found in this board.', 404)) {
            return;
        }

        $executor = $this->boardsMember->findBoardMember($boardsId, $executorID);

        if (!$this->validate($executor !== null, 'Executor is not a board member.', 422)) {
            return;
        }

        $nextPositionId = $this->defect->findLastPositionByBoardAndStatus($boardsId, $statusId) + 1;

        $createDefect = $this->defect->create(
            ['title', 'description', 'executer_id', 'board_id', 'status_id', 'appointed_id', 'position'],
            [$title, $description, $executor['user_id'], $boardsId, $statusId, $appointed_user_id, $nextPositionId]
        );

        if (!$this->validate($createDefect, 'Unable to create defect.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Defect created successfully.',
        ], 201);
    }

    public function getAllinStatus(int $boardId, int $statusId) {
        $appointed_user_id = $this->request->getDataSession('auth_user_id');

        if (!$this->validate(
            filter_var($appointed_user_id, FILTER_VALIDATE_INT) !== false && (int) $appointed_user_id > 0,
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

        if (!$this->validate(
            filter_var($statusId, FILTER_VALIDATE_INT) !== false && (int) $statusId > 0,
            'Status id is invalid',
            422
        )) {
            return;
        }

        $boardsId = (int) $boardId;
        $statusId = (int) $statusId;
        $board = $this->boards->find(['id'], $boardsId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $member = $this->boardsMember->findBoardMember($boardsId, (int) $appointed_user_id);

        if (!$this->validate($member !== null, 'Access to this board is denied.', 403)) {
            return;
        }

        $status = $this->statuses->findPositionByBoardAndStatus($boardsId, $statusId);

        if (!$this->validate($status !== null, 'Status was not found in this board.', 404)) {
            return;
        }

        $allDefect = $this->defect->findAllByBoardAndStatus($boardsId, $statusId);

        return $allDefect;
    }
}
