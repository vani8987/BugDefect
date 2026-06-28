<?php

namespace App\Models;
use Core\CRUD;
use Exception;
use PDO;

use Core\Request;

class BoardsMember extends CRUD {
    private Request $request;
    private string $adminRoleName;

    function __construct(){
        $this->request = new Request();
        parent::__construct('board_member');
        $this->adminRoleName = $_ENV['BOARD_ROLE_ADMIN'] ?? getenv('BOARD_ROLE_ADMIN') ?: 'admin';
    }

    public function getMembersWithRoles(int $boardId): array
        {
            try {
                $statement = $this->pdo->prepare("
                    SELECT
                        users.id,
                        users.name,
                        users.email,
                        roles.name AS role
                    FROM board_member
                    INNER JOIN users ON users.id = board_member.user_id
                    INNER JOIN roles ON roles.id = board_member.role_id
                    WHERE board_member.board_id = ?
                ");

                $statement->execute([$boardId]);

                return $statement->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $err) {
                $this->logger->error('Get board members failed: ' . $err->getMessage());
                return [];
            }
        }
    
    public function checkRoleAdmin(int $boardId): bool
    {
        $userId = (int) $this->request->getDataSession('auth_user_id');

        $statement = $this->pdo->prepare("
            SELECT roles.name
            FROM board_member
            INNER JOIN roles ON roles.id = board_member.role_id
            WHERE board_member.board_id = ?
            AND board_member.user_id = ?
            LIMIT 1
        ");

        $statement->execute([$boardId, $userId]);

        $role = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            return false;
        }

        return $role['name'] === $this->adminRoleName;
    }

    public function findBoardMember(int $boardId, int $userId): ?array
    {
        try {
            $statement = $this->pdo->prepare("
                SELECT id, board_id, user_id, role_id
                FROM board_member
                WHERE board_id = ?
                AND user_id = ?
                LIMIT 1
            ");

            $statement->execute([$boardId, $userId]);

            $member = $statement->fetch(PDO::FETCH_ASSOC);

            return $member === false ? null : $member;
        } catch (Exception $err) {
            $this->logger->error('Find board member failed: ' . $err->getMessage());
            return null;
        }
    }

    public function deleteBoardMember(int $boardId, int $userId): bool
    {
        try {
            $statement = $this->pdo->prepare("
                DELETE FROM board_member
                WHERE board_id = ?
                AND user_id = ?
            ");

            $statement->execute([$boardId, $userId]);

            return $statement->rowCount() > 0;
        } catch (Exception $err) {
            $this->logger->error('Delete board member failed: ' . $err->getMessage());
            return false;
        }
    }
}
