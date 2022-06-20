<?php

namespace Iqbal\Belajar\PHP\MVC\Middleware {

     require_once __DIR__ . "/../Helper/helper.php";

     use Iqbal\Belajar\PHP\MVC\Config\Database;
     use Iqbal\Belajar\PHP\MVC\Domain\Session;
     use Iqbal\Belajar\PHP\MVC\Domain\User;
     use Iqbal\Belajar\PHP\MVC\Repository\SessionRepository;
     use Iqbal\Belajar\PHP\MVC\Repository\UserRepository;
     use Iqbal\Belajar\PHP\MVC\Service\SessionService;
     use PHPUnit\Framework\TestCase;

     class MustNotLoginMiddlewareTest extends TestCase
     {
          private MustNotLoginMiddleware $mustNotLoginMiddleware;
          private UserRepository $userRepository;
          private SessionRepository $sessionRepository;

          protected function setUp(): void
          {
               $this->mustNotLoginMiddleware = new MustNotLoginMiddleware();
               putenv("mode=test");

               $this->userRepository = new UserRepository(Database::getConnection());
               $this->sessionRepository = new SessionRepository(Database::getConnection());

               $this->sessionRepository->deleteAll();
               $this->userRepository->deleteAll();
          }

          public function testBeforeGuest()
          {
               $this->mustNotLoginMiddleware->before();
               $this->expectOutputString("");
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

               $this->mustNotLoginMiddleware->before();
               $this->expectOutputRegex("[Location: /]");
          }
     }
}
