<?php

namespace Iqbal\Belajar\PHP\MVC\Service;

require_once __DIR__ . "/../Helper/helper.php";

use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Domain\Session;
use Iqbal\LoginManagement\Domain\User;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use Iqbal\LoginManagement\Service\SessionService;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
     private SessionRepository $sessionRepository;
     private SessionService $sessionService;
     private UserRepository $userRepository;

     protected function setUp(): void
     {
          $this->sessionRepository = new SessionRepository(Database::getConnection());
          $this->userRepository = new UserRepository(Database::getConnection());
          $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

          $this->sessionRepository->deleteAll();
          $this->userRepository->deleteAll();

          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";
          $this->userRepository->save($user);
     }

     public function testCreate()
     {
          $session = $this->sessionService->create("iqbal");

          $this->expectOutputRegex("[X-IQBAL-SESSION: $session->id]");

          $result = $this->sessionRepository->findById($session->id);
          self::assertEquals("iqbal", $result->userId);
     }

     public function testDestroy()
     {
          $session = new Session();
          $session->id = uniqid();
          $session->userId = "iqbal";

          $this->sessionRepository->save($session);

          $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

          $this->sessionService->destroy();


          $this->expectOutputRegex("[X-IQBAL-SESSION: ]");

          $result = $this->sessionRepository->findById($session->id);

          self::assertNull($result);
     }

     public function testCurrent()
     {
          $session = new Session();
          $session->id = uniqid();
          $session->userId = "iqbal";

          $this->sessionRepository->save($session);
          $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

          $user = $this->sessionService->current();

          self::assertEquals($session->userId, $user->id);
     }
}
