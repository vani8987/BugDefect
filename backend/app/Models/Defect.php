<?php

namespace App\Models;
use Core\CRUD;
use Exception;
use PDO;

class Defect extends CRUD {

    function __construct(){
        parent::__construct('defects');
    }

    public function findLastPositionByBoardAndStatus(int $boardId, int $statusId): int
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT MAX(position) AS position
                FROM defects
                WHERE board_id = ?
                AND status_id = ?
            ");

            $statement->execute([$boardId, $statusId]);

            $defect = $statement->fetch(PDO::FETCH_ASSOC);

            return isset($defect['position']) ? (int) $defect['position'] : 0;
        } catch (Exception $err) {
            $this->logger->error('Find defect position failed: ' . $err->getMessage());
            return 0;
        }
    }

    public function findAllByBoardAndStatus(int $boardId, int $statusId): array
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT *
                FROM defects
                WHERE board_id = ?
                AND status_id = ?
                ORDER BY position ASC, id ASC
            ");

            $statement->execute([$boardId, $statusId]);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            $this->logger->error('Find defects by board and status failed: ' . $err->getMessage());
            return [];
        }
    }

    public function findByBoardAndId(int $boardId, int $defectId): ?array
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT *
                FROM defects
                WHERE board_id = ?
                AND id = ?
                LIMIT 1
            ");

            $statement->execute([$boardId, $defectId]);

            $defect = $statement->fetch(PDO::FETCH_ASSOC);

            return $defect === false ? null : $defect;
        } catch (Exception $err) {
            $this->logger->error('Find defect by board and id failed: ' . $err->getMessage());
            return null;
        }
    }

    public function updatePositionByBoardAndId(
        int $boardId,
        int $defectId,
        int $statusId,
        int $position
    ): bool {
        try {
            $statement = $this->pdo->prepare("
                UPDATE defects
                SET status_id = ?, position = ?
                WHERE board_id = ?
                AND id = ?
            ");

            $statement->execute([$statusId, $position, $boardId, $defectId]);

            return $statement->rowCount() > 0;
        } catch (Exception $err) {
            $this->logger->error('Update defect position failed: ' . $err->getMessage());
            return false;
        }
    }
}
