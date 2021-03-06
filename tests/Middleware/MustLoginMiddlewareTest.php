<?php

namespace Iqbal\Belajar\PHP\MVC\Middleware {

     require_once __DIR__ . "/../Helper/helper.php";

     use Iqbal\LoginManagement\Config\Database;
     use Iqbal\LoginManagement\Domain\Session;
     use Iqbal\LoginManagement\Domain\User;
     use Iqbal\LoginManagement\Middleware\MustLoginMiddleware;
     use Iqbal\LoginManagement\Repository\SessionRepository;
     use Iqbal\LoginManagement\Repository\UserRepository;
     use Iqbal\LoginManagement\Service\SessionService;
     use PHPUnit\Framework\TestCase;

     class MustLoginMiddlewareTest extends TestCase
     {
          private MustLoginMiddleware $mustLoginMiddleware;
          private UserRepository $userRepository;
          private SessionRepository $sessionRepository;

          protected function setUp(): void
          {
               $this->mustLoginMiddleware = new MustLoginMiddleware();
               putenv("mode=test");

               $this->userRepository = new UserRepository(Database::getConnection());
               $this->sessionRepository = new SessionRepository(Database::getConnection());

               $this->sessionRepository->deleteAll();
               $this->userRepository->deleteAll();
          }

          public function testBeforeGuest()
          {
               $this->mustLoginMiddleware->before();
               $this->expectOutputRegex("[Location: /users/login]");
          }

          public function testBeforeLoginUser()
          {
               $user = new User();
               $user->id = 'iqbal';
               $user->name = 'Iqbal';
               $user->password = password_hash('qwerty', PASSWORD_BCRYPT);
               $this->userRepository->save($user);

               $session = new Session();
               $session->id = uniqid();
               $session->userId = $user->id;
               $this->sessionRepository->save($session);

               $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

               $this->mustLoginMiddleware->before();
               $this->expectOutputString("");
          }
     }
}
