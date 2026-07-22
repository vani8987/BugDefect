<?php
namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Logger;

use App\Models\Boards;
use App\Models\BoardsMember;
use App\Models\User;
use App\Models\Roles;
use App\Models\Notification;
use App\Models\InviteBoardMember;

interface InviteBoardControllerInterface {
    public function addUser(string $boardsId);
}

class InviteBoardController extends Controller implements InviteBoardControllerInterface {
    private Boards $boards;
    private BoardsMember $boardsMember;
    private Roles $roles;
    private Notification $notification;
    private InviteBoardMember $inviteBoardMember;
    private User $user;

    public function __construct(
        ?Request $request = null,
        ?Response $response = null,
        ?Logger $logger = null,
        ?Boards $boards = null,
        ?BoardsMember $boardsMember = null,
        ?Roles $roles = null,
        ?Notification $notification = null,
        ?InviteBoardMember $inviteBoardMember = null,
        ?User $user = null
    ) {
        $request = $request ?? new Request();
        $response = $response ?? new Response();
        $this->boards = $boards ?? new Boards();
        $this->user = $user ?? new User();
        $this->boardsMember = $boardsMember ?? new BoardsMember($request);
        $this->roles = $roles ?? new Roles();
        $this->notification = $notification ?? new Notification();
        $this->inviteBoardMember = $inviteBoardMember ?? new InviteBoardMember();
        parent::__construct($logger ?? new Logger('Board.log'), $response, $request);
    }

    public function addUser(string $boardsId) {
        $boardsId = $this->positiveId('Board id is invalid', $boardsId);
        if ($boardsId === null) {
            return;
        }

        $board = $this->boards->find(['id'], $boardsId);

        if (!$this->validate($board !== null, 'Board not found', 404)) {
            return;
        }

        $is_admin = $this->boardsMember->checkRoleAdmin($boardsId);

        if (!$this->validate($is_admin, 'Only the board administrator can invite members.', 403)) {
            return;
        }

        $email = $this->request->getDataJson('email');
        $role = $this->request->getDataJson('role');
        $inviter_user_id = $this->request->getDataSession('auth_user_id');

        $inviter_user_id = $this->positiveId('Unauthorized', $inviter_user_id, 401);
        if ($inviter_user_id === null) {
            return;
        }

        if (!$this->validate(
            is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            'Email is invalid',
            422
        )) {
            return;
        }

        if (!$this->validateStringLength($role, 'Role is empty')) {
            return;
        }

        $email = trim($email);
        $role = trim($role);

        $invited_user = $this->user->findByEmail($email);

        if (!$this->validate($invited_user !== null, 'Invited user not found', 404)) {
            return;
        }

        if (!$this->validate(
            (int) $invited_user['id'] !== $inviter_user_id,
            'You cannot invite yourself',
            422
        )) {
            return;
        }

        $role_data = $this->roles->findOneBy(['id'], 'name', $role);

        if (!$this->validate($role_data !== null, 'Role not found', 404)) {
            return;
        }

        $user_memberships = $this->boardsMember->findAll(['id', 'board_id'], 'user_id', (int) $invited_user['id']);
        $already_member = false;

        foreach ($user_memberships as $membership) {
            if ((int) $membership['board_id'] === $boardsId) {
                $already_member = true;
                break;
            }
        }

        if (!$this->validate(
            !$already_member,
            'User is already a board member',
            422
        )) {
            return;
        }

        $user_invites = $this->inviteBoardMember->findAll(
            ['id', 'board_id', 'status'],
            'invited_user_id',
            (int) $invited_user['id']
        );
        $has_pending_invite = false;

        foreach ($user_invites as $invite) {
            if ((int) $invite['board_id'] === $boardsId && $invite['status'] === 'pending') {
                $has_pending_invite = true;
                break;
            }
        }

        if (!$this->validate(
            !$has_pending_invite,
            'User already has a pending invite',
            422
        )) {
            return;
        }

        $inviteCreate = $this->inviteBoardMember->create(
            ['board_id', 'invited_user_id', 'inviter_user_id', 'role_id'],
            [$boardsId, (int) $invited_user['id'], $inviter_user_id, (int) $role_data['id']]
        );

        if (!$this->validate(
            $inviteCreate,
            'invite not created',
            500
        )) {
            return;
        }

        $titleBoard = $this->boards->find(['title'], $boardsId);

        if (!$this->validate($titleBoard !== null, 'Board title not found', 500)) {
            return;
        }

        $createdInvites = $this->inviteBoardMember->findAll(
            ['id', 'board_id', 'status'],
            'invited_user_id',
            (int) $invited_user['id']
        );
        $createdInviteId = null;

        foreach ($createdInvites as $invite) {
            if ((int) $invite['board_id'] === $boardsId && $invite['status'] === 'pending') {
                $createdInviteId = (int) $invite['id'];
                break;
            }
        }

        if (!$this->validate($createdInviteId !== null, 'Created invite not found', 500)) {
            return;
        }
        
        $notificationData = json_encode([
            'invite_id' => $createdInviteId,
            'board_id' => $boardsId,
        ]);

        if (!$this->validate($notificationData !== false, 'Notification data is invalid', 500)) {
            return;
        }

        $notificationCreate = $this->notification->create(
            ['title', 'type', 'data', 'description', 'user_id'],
            [
                "Приглашение в доску {$titleBoard['title']}",
                'invite',
                $notificationData,
                "Вас приглашают присоединиться к доске с ролью {$role}.",
                (int) $invited_user['id']
            ],
        );

        if (!$this->validate($notificationCreate, 'notification not created', 500)) {
            return;
        }

        $this->response->json([
            'message' => 'Приглашение создано'
        ], 201);

    }

}
