<?php
namespace App\Controllers;

use Core\Controller;
use Core\Logger;
use Core\Response;
use Core\Request;

use App\Models\Defect;
use App\Models\Statuses;

class StatusesController extends Controller {
    private Defect $defect;
    private Statuses $statuses;

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?Defect $defect = null,
        ?Statuses $statuses = null
    ) {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $this->defect = $defect ?? new Defect();
        $this->statuses = $statuses ?? new Statuses();

        parent::__construct($logger ?? new Logger('Statuses.log'), $response, $request);
    }

    public function createStatuses(string $boardId) {
        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        $boardId = (int) $boardId;

        $title = $this->request->getDataJson('title');
        $description = $this->request->getDataJson('description') ?? '';
        $position = $this->request->getDataJson('position');

        if (!$this->validateStringLength($title, 'Title is required and must be at most 100 characters.', countSymbol: 100)) {
            return;
        }

        if (!$this->validateStringLength($description, 'Description must be at most 255 characters.', required: false)) {
            return;
        }

        if ($this->positiveId('Position must be a positive number.', $position) === null) return;
        $position = (int) $position;
        
        $title = trim($title);
        $description = trim($description);

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
        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        $boardId = (int) $boardId;

        $allStatuses = array_map(function (array $status) use ($boardId) {
            $status['items'] = $this->defect->findAllByBoardAndStatus($boardId, (int) $status['id']);
            return $status;
        }, $this->statuses->findAllByBoard($boardId));

        $this->response->json([
            'statuses' => $allStatuses
        ], 200);
    }

    public function updatePosition(string $boardId) {
        if ($this->positiveId('Board id is invalid', $boardId) === null) return;
        $boardId = (int) $boardId;

        $allStatuses = $this->request->getDataJson('statuses');

        if (!$this->validate(is_array($allStatuses), 'Statuses must be an array.', 422)) {
            return;
        }

        foreach ($allStatuses as $status) {
            if (!$this->validate(is_array($status) && isset($status['id'], $status['position']), 'Status id and position must be positive numbers.', 422)) {
                return;
            }

            if ($this->positiveId('Status id and position must be positive numbers.', $status['id']) === null) return;
            $idStatus = (int) $status['id'];

            if ($this->positiveId('Status id and position must be positive numbers.', $status['position']) === null) return;
            $positionStatus = (int) $status['position'];

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
        if ($this->positiveId('Board ID must be a positive number.', $boardId) === null) return;
        $boardId = (int) $boardId;

        if ($this->positiveId('Status ID must be a positive number.', $statusId) === null) return;
        $statusId = (int) $statusId;

        $statusDeleted = $this->statuses->deleteByBoardAndStatus($boardId, $statusId);

        if (!$this->validate($statusDeleted, 'Unable to delete status.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Status deleted successfully.',
        ], 200);
    }
}
