<?php

namespace Iqbal\LoginManagement\Controller;

use Iqbal\LoginManagement\App\View;
use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use Iqbal\LoginManagement\Service\SessionService;

class HomeController
{
     private SessionService $sessionService;

     public function __construct()
     {
          $connection = Database::getConnection();
          $sessionRepository = new SessionRepository($connection);
          $userRepository = new UserRepository($connection);
          $this->sessionService = new SessionService($sessionRepository, $userRepository);
     }

     public function index()
     {
          $user = $this->sessionService->current();
          if ($user == null) {
               View::render("Home/index", [
                    "title" => "PHP Login Management"
               ]);
          } else {
               View::render("Home/dashboard", [
                    "title" => "Dashboard",
                    "user" => [
                         "name" => $user->name
                    ]
               ]);
          }
     }
}
