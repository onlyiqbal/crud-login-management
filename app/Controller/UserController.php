<?php

namespace Iqbal\Belajar\PHP\MVC\Controller;

use Exception;
use Iqbal\Belajar\PHP\MVC\App\View;
use Iqbal\Belajar\PHP\MVC\Config\Database;
use Iqbal\Belajar\PHP\MVC\Exception\ValidationException;
use Iqbal\Belajar\PHP\MVC\Model\UserLoginRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserRegisterRequest;
use Iqbal\Belajar\PHP\MVC\Repository\SessionRepository;
use Iqbal\Belajar\PHP\MVC\Repository\UserRepository;
use Iqbal\Belajar\PHP\MVC\Service\SessionService;
use Iqbal\Belajar\PHP\MVC\Service\UserService;

class UserController
{
     private UserService $userService;
     private SessionService $sessionService;

     public function __construct()
     {
          $connection = Database::getConnection();
          $userRepository = new UserRepository($connection);
          $this->userService = new UserService($userRepository);

          $sessionRepository = new SessionRepository($connection);
          $this->sessionService = new SessionService($sessionRepository, $userRepository);
     }

     public function register()
     {
          View::render('User/register', [
               'title' => 'Register new User',
          ]);
     }

     public function postRegister()
     {
          $request = new UserRegisterRequest();
          $request->id = $_POST['id'];
          $request->name = $_POST['name'];
          $request->password = $_POST['password'];

          try {
               $this->userService->register($request);
               View::redirect('/users/login');
          } catch (ValidationException $exception) {
               View::render('User/register', [
                    'title' => 'Register new User',
                    'error' => $exception->getMessage(),
               ]);
          }
     }

     public function login()
     {
          View::render('User/login', [
               'title' => 'Login user'
          ]);
     }

     public function postLogin()
     {
          $request = new UserLoginRequest();
          $request->id = $_POST['id'];
          $request->password = $_POST['password'];

          try {
               $response = $this->userService->login($request);
               $this->sessionService->create($response->user->id);
               View::redirect('/');
          } catch (ValidationException $exception) {
               View::render('User/login', [
                    'title' => 'Login User',
                    'error' => $exception->getMessage(),
               ]);
          }
     }

     public function logout()
     {
          $this->sessionService->destroy();
          View::redirect("/");
     }

     public function updateProfile()
     {
          $user = $this->sessionService->current();
          View::render("User/profile", [
               'title' => "Update user profile",
               'user' => [
                    'id' => $user->id,
                    'name' => $user->name
               ]
          ]);
     }

     public function postUpdateProfile()
     {
          $user = $this->sessionService->current();

          $request = new UserProfileUpdateRequest();
          $request->id = $user->id;
          $request->name = $_POST['name'];

          try {
               $this->userService->updateProfile($request);
               View::redirect("/");
          } catch (ValidationException $exception) {
               View::render("User/profile", [
                    'title' => "Update user profile",
                    'error' => $exception->getMessage(),
                    'user' => [
                         'id' => $user->id,
                         'name' => $_POST['name']
                    ]
               ]);
          }
     }

     public function updatePassword()
     {
          $user = $this->sessionService->current();
          View::render("User/password", [
               'title' => "Upadate user password",
               'user' => [
                    'id' => $user->id,
               ]
          ]);
     }

     public function postUpdatePassword()
     {
          $user = $this->sessionService->current();
          $request = new UserPasswordUpdateRequest();
          $request->id = $user->id;
          $request->oldPassword = $_POST['oldPassword'];
          $request->newPassword = $_POST['newPassword'];

          try {
               $this->userService->updatePassword($request);
               View::redirect("/");
          } catch (ValidationException $exception) {
               View::render("User/password", [
                    'title' => "Update user password",
                    'error' => $exception->getMessage(),
                    'user' => [
                         'id' => $user->id,
                    ]
               ]);
          }
     }
}
