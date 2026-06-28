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
}
