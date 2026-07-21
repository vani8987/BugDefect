<?php

namespace App\Models;
use Core\CRUD;
use Core\Logger;
use Exception;
use PDO;

class Statuses extends CRUD {

    public function __construct(?Logger $logger = null) {
        parent::__construct('statuses', $logger);
    }

    public function findPositionByBoardAndStatus(int $boardId, int $statusId): ?array
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT id, position
                FROM statuses
                WHERE board_id = ?
                AND id = ?
                LIMIT 1
            ");

            $statement->execute([$boardId, $statusId]);

            $status = $statement->fetch(PDO::FETCH_ASSOC);

            return $status === false ? null : $status;
        } catch (Exception $err) {
            $this->logger->error('Find status position failed: ' . $err->getMessage());
            return null;
        }
    }

    public function findAllByBoard(int $boardId): array
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT *
                FROM statuses
                WHERE board_id = ?
                ORDER BY position ASC, id ASC
            ");

            $statement->execute([$boardId]);

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            $this->logger->error('Find board statuses failed: ' . $err->getMessage());
            return [];
        }
    }

    public function updatePositionByBoardAndStatus(int $boardId, int $statusId, int $position): bool
    {
        try {
            $statement = $this->pdo->prepare("
                UPDATE statuses
                SET position = ?
                WHERE board_id = ?
                AND id = ?
            ");

            $statement->execute([$position, $boardId, $statusId]);

            return true;
        } catch (Exception $err) {
            $this->logger->error('Update status position failed: ' . $err->getMessage());
            return false;
        }
    }

    public function deleteByBoardAndStatus(int $boardId, int $statusId): bool
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM statuses
                WHERE board_id = ?
                AND id = ?
            ");

            $statement->execute([$boardId, $statusId]);

            return $statement->rowCount() > 0;
        } catch (Exception $err) {
            $this->logger->error('Delete status failed: ' . $err->getMessage());
            return false;
        }
    }
}
