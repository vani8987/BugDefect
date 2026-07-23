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

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?Defect $defect = null,
        ?Boards $boards = null,
        ?BoardsMember $boardsMember = null,
        ?Statuses $statuses = null
    ) {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $this->defect = $defect ?? new Defect();
        $this->boards = $boards ?? new Boards();
        $this->boardsMember = $boardsMember ?? new BoardsMember($request);
        $this->statuses = $statuses ?? new Statuses();

        parent::__construct($logger ?? new Logger('Defects.log'), $response, $request);
    }

    public function createDefect(int $boardId) {
        $appointed_user_id = $this->request->getDataSession('auth_user_id');

        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        $boardsId = (int) $boardId;

        $statusId = $this->request->getDataJson('statusId');
        $executorID = $this->request->getDataJson('executorID');
        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';

        if ($this->positiveId('Status id is invalid', $statusId) === null) return;
        $statusId = (int) $statusId;

        if ($this->positiveId('Executor id is invalid', $executorID) === null) return;
        $executorID = (int) $executorID;

        if (!$this->validateStringLength($title, 'Title is required and must be at most 100 characters.', countSymbol: 100)) {
            return;
        }

        if (!$this->validateStringLength($description, 'Description must be at most 5000 characters.', countSymbol: 5000, required: false)) {
            return;
        }

        $appointed_user_id = (int) $appointed_user_id;
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

        if ($this->positiveId('Unauthorized.', $appointed_user_id, 401) === null) return;
        $appointed_user_id = (int) $appointed_user_id;

        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        $boardsId = (int) $boardId;

        if ($this->positiveId('Status id is invalid', $statusId) === null) return;
        $statusId = (int) $statusId;

        $board = $this->boards->find(['id'], $boardsId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $member = $this->boardsMember->findBoardMember($boardsId, $appointed_user_id);

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

    public function moveDefect(int $boardId, int $defectId) {
        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        if ($this->positiveId('Defect id is invalid', $defectId) === null) return;

        $statusId = $this->request->getDataJson('statusId');
        $position = $this->request->getDataJson('position');

        if ($this->positiveId('Status id is invalid', $statusId) === null) return;
        $statusId = (int) $statusId;

        if ($this->positiveId('Position is invalid', $position) === null) return;
        $position = (int) $position;

        $defect = $this->defect->findByBoardAndId($boardId, $defectId);

        if (!$this->validate($defect !== null, 'Defect was not found in this board.', 404)) {
            return;
        }

        $status = $this->statuses->findPositionByBoardAndStatus($boardId, $statusId);

        if (!$this->validate($status !== null, 'Status was not found in this board.', 404)) {
            return;
        }

        $updated = $this->defect->updatePositionByBoardAndId($boardId, $defectId, $statusId, $position);

        if (!$this->validate($updated, 'Defect was not moved.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Defect moved successfully.',
        ], 200);
    }

    public function deleteDefect(int $boardId, int $defectId) {
        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        if ($this->positiveId('Defect id is invalid', $defectId) === null) return;

        $statusId = $this->request->getDataJson('statusId');

        if ($this->positiveId('Status id is invalid', $statusId) === null) return;
        $statusId = (int) $statusId;

        $defect = $this->defect->findByBoardAndId($boardId, $defectId);

        if (!$this->validate($defect !== null, 'Defect was not found in this board.', 404)) {
            return;
        }

        $deleted = $this->defect->deleteDefectByBoard($boardId, $defectId, $statusId);

        if (!$this->validate($deleted, 'Defect was not deleted in this board.', 500)) {
            return;
        }

        $this->response->json([
            'message' => "Defect {$defect['title']} deleted",
        ], 200);
    }
}
