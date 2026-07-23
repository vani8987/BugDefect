<?php

namespace App\Controllers;

use Core\Controller;

use App\Models\BoardsMember;

use Core\Logger;
use Core\Request;
use Core\Response;

interface BoardMemberControllerInterface {
    public function getUsers(string $boardId): void;
    public function deleteUser(string $boardId, string $userId): void;
}

class BoardMemberController extends Controller implements BoardMemberControllerInterface {
    private BoardsMember $boardMember;

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?BoardsMember $boardMember = null
    ) {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $logger = $logger ?? new Logger('BoardMember.log');
        $this->boardMember = $boardMember ?? new BoardsMember($request);

        parent::__construct($logger, $response, $request);
    }

    public function getUsers(string $boardId): void {
        if ($this->positiveId('Board ID must be a positive number.', $boardId) === null) return;
        $boardId = (int) $boardId;

        $members = $this->boardMember->getMembersWithRoles($boardId);

        $this->response->json([
            'members' => $members,
        ], 200);
    }

    public function deleteUser(string $boardId, string $userId): void {
        if ($this->positiveId('Board ID must be a positive number.', $boardId) === null) return;
        $boardId = (int) $boardId;

        if ($this->positiveId('User ID must be a positive number.', $userId) === null) return;
        $userId = (int) $userId;

        $member = $this->boardMember->findBoardMember($boardId, $userId);

        if (!$this->validate($member !== null, 'Board member was not found.', 404)) {
            return;
        }

        $memberDeleted = $this->boardMember->deleteBoardMember($boardId, $userId);

        if (!$this->validate($memberDeleted, 'Unable to remove board member.', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Board member removed successfully.',
        ], 200);
    }
}
