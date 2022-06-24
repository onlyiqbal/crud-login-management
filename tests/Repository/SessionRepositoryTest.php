<?php

namespace Iqbal\Belajar\PHP\MVC\Repository;

use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Domain\Session;
use Iqbal\LoginManagement\Domain\User;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class SessionRepositoryTest extends TestCase
{
     private SessionRepository $sessionRepository;
     private UserRepository $userRepository;

     protected function setUp(): void
     {
          $this->userRepository = new UserRepository(Database::getConnection());
          $this->sessionRepository = new SessionRepository(Database::getConnection());

          $this->sessionRepository->deleteAll();
          $this->userRepository->deleteAll();

          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";
          $this->userRepository->save($user);
     }

     public function testSaveSuccess()
     {
          $session = new Session();
          $session->id = uniqid();
          $session->userId = "iqbal";

          $this->sessionRepository->save($session);
          $result = $this->sessionRepository->findById($session->id);

          self::assertEquals($session->id, $result->id);
          self::assertEquals($session->userId, $result->userId);
     }

     public function testDeleteByIdSuccess()
     {
          $session = new Session();
          $session->id = uniqid();
          $session->userId = "iqbal";

          $this->sessionRepository->save($session);
          $result = $this->sessionRepository->findById($session->id);

          self::assertEquals($session->id, $result->id);
          self::assertEquals($session->userId, $result->userId);

          $this->sessionRepository->deleteById($session->id);
          $result = $this->sessionRepository->findById($session->id);

          self::assertNull($result);
     }

     public function testDeleteByIdNotfound()
     {
          $result = $this->sessionRepository->findById("Not found");

          self::assertNull($result);
     }
}
