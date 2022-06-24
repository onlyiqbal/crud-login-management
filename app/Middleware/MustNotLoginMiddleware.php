<?php

namespace Iqbal\LoginManagement\Middleware;

use Iqbal\LoginManagement\App\View;
use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use Iqbal\LoginManagement\Service\SessionService;

class MustNotLoginMiddleware implements Middleware
{
     private SessionService $sessionService;

     public function __construct()
     {
          $sessionRepository = new SessionRepository(Database::getConnection());
          $userRepository = new UserRepository(Database::getConnection());
          $this->sessionService = new SessionService($sessionRepository, $userRepository);
     }

     public function before(): void
     {
          $user = $this->sessionService->current();
          if ($user != null) {
               View::redirect("/");
          }
     }
}
