<?php

namespace App\Middleware;

use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\User;
use Core\Logger;
use Core\Request;

class BoardMiddleware extends AuthMiddleware
{
    private Boards $boards;
    private BoardsMember $boardsMember;

    public function __construct(
        ?Request $request = null,
        ?Logger $logger = null,
        ?User $user = null,
        ?Boards $boards = null,
        ?BoardsMember $boardsMember = null
    )
    {
        parent::__construct($request, $logger, $user);
        $this->boards = $boards ?? new Boards();
        $this->boardsMember = $boardsMember ?? new BoardsMember();
    }

    public function boardAccess(string|int $boardId): bool
    {
        $boardId = $this->getPositiveId($boardId);
        $userId = $this->getCurrentUserId();

        if ($boardId === null || $userId === null) {
            $this->logger->warning('Board access failed: invalid board or user id.');
            return false;
        }

        if (!$this->boardExists($boardId)) {
            $this->logger->warning("Board access failed: board {$boardId} was not found.");
            return false;
        }

        $member = $this->boardsMember->findBoardMember($boardId, $userId);

        if ($member === null) {
            $this->logger->warning("Board access failed: user {$userId} is not a member of board {$boardId}.");
            return false;
        }

        $this->logger->info("Board access passed: user {$userId}, board {$boardId}.");
        return true;
    }

    public function boardAdmin(string|int $boardId): bool
    {
        $boardId = $this->getPositiveId($boardId);
        $userId = $this->getCurrentUserId();

        if ($boardId === null || $userId === null) {
            $this->logger->warning('Board admin check failed: invalid board or user id.');
            return false;
        }

        if (!$this->boardExists($boardId)) {
            $this->logger->warning("Board admin check failed: board {$boardId} was not found.");
            return false;
        }

        if (!$this->boardsMember->checkRoleAdmin($boardId)) {
            $this->logger->warning("Board admin check failed: user {$userId}, board {$boardId}.");
            return false;
        }

        $this->logger->info("Board admin check passed: user {$userId}, board {$boardId}.");
        return true;
    }

    private function getPositiveId(string|int $id): ?int
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int) $id <= 0) {
            return null;
        }

        return (int) $id;
    }

    private function getCurrentUserId(): ?int
    {
        $userId = $this->request->getDataSession('auth_user_id');

        if (filter_var($userId, FILTER_VALIDATE_INT) === false || (int) $userId <= 0) {
            return null;
        }

        return (int) $userId;
    }

    private function boardExists(int $boardId): bool
    {
        return $this->boards->find(['id'], $boardId) !== null;
    }
}
