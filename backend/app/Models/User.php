<?php
namespace App\Models;
use Core\CRUD;
use Core\Logger;
use Core\UserProviderInterface;
use RuntimeException;

class User extends CRUD implements UserProviderInterface {
    public function __construct(?Logger $logger = null) {
        parent::__construct('users', $logger);
    }

    public function findByEmail(string $email): ?array {
        if ($email === '') {
            $this->logger->error('email is empty');
            throw new RuntimeException('email is empty');
        }

        return $this->findOneBy(['id', 'email', 'password'], 'email', $email);
    }
}
