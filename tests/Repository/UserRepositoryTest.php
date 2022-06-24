<?php

namespace Iqbal\Belajar\PHP\MVC\Repository;

use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Domain\User;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
     private UserRepository $userRepository;
     private SessionRepository $sessionRepository;

     protected function setUp(): void
     {
          $this->sessionRepository = new SessionRepository(Database::getConnection());
          $this->sessionRepository->deleteAll();

          $this->userRepository = new UserRepository(Database::getConnection());
          $this->userRepository->deleteAll();
     }

     public function testSaveSuccess()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";

          $this->userRepository->save($user);

          $result = $this->userRepository->findById($user->id);

          self::assertEquals($user->id, $result->id);
          self::assertEquals($user->name, $result->name);
          self::assertEquals($user->password, $result->password);
     }

     public function testfindByIdNotFound()
     {
          $user = $this->userRepository->findById("not found");
          self::assertNull($user);
     }

     public function testUpdate()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";
          $this->userRepository->save($user);

          $user->name = "Budi";
          $this->userRepository->update($user);

          $result = $this->userRepository->findById($user->id);

          self::assertEquals($user->id, $result->id);
          self::assertEquals($user->name, $result->name);
          self::assertEquals($user->password, $result->password);
     }
}
