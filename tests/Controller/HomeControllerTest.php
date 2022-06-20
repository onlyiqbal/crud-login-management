<?php

namespace Iqbal\Belajar\PHP\MVC\Controller;

use Iqbal\Belajar\PHP\MVC\Config\Database;
use Iqbal\Belajar\PHP\MVC\Domain\Session;
use Iqbal\Belajar\PHP\MVC\Domain\User;
use Iqbal\Belajar\PHP\MVC\Repository\SessionRepository;
use Iqbal\Belajar\PHP\MVC\Repository\UserRepository;
use Iqbal\Belajar\PHP\MVC\Service\SessionService;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
     private HomeController $homeController;
     private SessionRepository $sessionRepository;
     private UserRepository $userRepository;

     protected function setUp(): void
     {
          $this->homeController = new HomeController();
          $this->sessionRepository = new SessionRepository(Database::getConnection());
          $this->userRepository = new UserRepository(Database::getConnection());

          $this->sessionRepository->deleteAll();
          $this->userRepository->deleteAll();
     }

     public function testGuest()
     {
          $this->homeController->index();

          $this->expectOutputRegex('[Login Management]');
     }

     public function testUserLogin()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";
          $this->userRepository->save($user);

          $session = new Session();
          $session->id = uniqid();
          $session->userId = $user->id;
          $this->sessionRepository->save($session);

          $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

          $this->homeController->index();

          $this->expectOutputRegex('[Hello Iqbal]');
     }
}
