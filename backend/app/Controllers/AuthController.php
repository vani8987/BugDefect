<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use Core\Response;
use Core\Logger;
use Core\Auth;

use App\Models\Roles;
use App\Models\User;

interface interfaceAuth {
    public function register();
}


class AuthController extends Controller implements interfaceAuth {
    private Auth $auth;
    private User $user;
    private Roles $roles;

    
    public function __construct()
    {   
        $this->roles = new Roles();
        $this->user = new User();
        $this->request = new Request();
        parent::__construct(new Logger('User.log'), new Response(), new Request());
        $this->auth = new Auth($this->user);
    }

    public function register() {
        $name = $this->request->getDataJson('name');
        $email = $this->request->getDataJson('email');
        $password = $this->request->getDataJson('password');

        if (!$this->validate(
            is_string($name) && trim($name) !== '' &&
            is_string($email) && trim($email) !== '' &&
            is_string($password) && $password !== '',
            'user date empty',
            422
        )) {
            return;
        }

        $register = $this->auth->registerByEmail($password, $email);

        if (!$this->validate(
            $register,
            'there is already a user with such a post',
            409
        )) {
            return;
        }

        $user = $this->user->findOneBy(['id'], 'email', $email);

        if (!$this->validate(
            $user !== null,
            'Registered user was not found.',
            500
        )) {
            return;
        }

        $updated = $this->user->update(['name'], [$name], $user['id']);

        if (!$this->validate(
            $updated,
            "Unable to save the name for user {$user['id']}.",
            500
        )) {
            return;
        }

        return $this->response->json([
            'message' => 'User registered',
        ], 201);
    }

    public function login() {
        $email = $this->request->getDataJson('email');
        $password = $this->request->getDataJson('password');

        if (!$this->validate(
            is_string($email) && trim($email) !== '' &&
            is_string($password) && $password !== '',
            'user date empty',
            422
        )) {
            return;
        }

        $login = $this->auth->loginByEmail($password, $email);

        if (!$this->validate(
                    $login,
                    "user login error",
                    401
        )) {
            return;
        }

        return $this->response->json([
            'message' => 'User login',
        ], 200);
    }

    public function logout() {
        $logout = $this->auth->logout();

        if (!$this->validate(
                    $logout,
                    "user logout error",
                    500
        )) {
            return;
        }

        return $this->response->json([
            'message' => 'User logout',
        ], 200);
    }

    public function user() {
        $user = $this->auth->user(['id', 'name', 'email', 'role_id']);

        if (!$this->validate($user !== null, 'User was not found', 404)) {
            return;
        }

        $role = $this->roles->find(['name'], (int) $user['role_id']);

        if (!$this->validate($role !== null, 'Role was not found', 404)) {
            return;
        }

        return $this->response->json([
            'user' => [
                ...$user,
                'role' => $role['name'],
            ],
        ]);
    }

}
