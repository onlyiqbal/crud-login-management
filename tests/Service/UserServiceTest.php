<?php

namespace Iqbal\Belajar\PHP\MVC\Service;

use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Domain\User;
use Iqbal\LoginManagement\Exception\ValidationException;
use Iqbal\LoginManagement\Model\UserLoginRequest;
use Iqbal\LoginManagement\Model\UserPasswordUpdateRequest;
use Iqbal\LoginManagement\Model\UserProfileUpdateRequest;
use Iqbal\LoginManagement\Model\UserRegisterRequest;
use Iqbal\LoginManagement\Repository\SessionRepository;
use Iqbal\LoginManagement\Repository\UserRepository;
use Iqbal\LoginManagement\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
     private UserService $userService;
     private UserRepository $userRepository;
     private SessionRepository $sessionRepository;

     protected function setUp(): void
     {
          $connection = Database::getConnection();
          $this->userRepository = new UserRepository($connection);
          $this->userService = new UserService($this->userRepository);
          $this->sessionRepository = new SessionRepository($connection);
          $this->sessionRepository->deleteAll();

          $this->userRepository->deleteAll();
     }

     public function testRegisterSuccess()
     {
          $request = new UserRegisterRequest();
          $request->id = "iqbal";
          $request->name = "Iqbal";
          $request->password = "qwerty";

          $response = $this->userService->register($request);

          self::assertEquals($request->id, $response->user->id);
          self::assertEquals($request->name, $response->user->name);
          self::assertNotEquals($request->password, $response->user->password);

          self::assertTrue(password_verify($request->password, $response->user->password));
     }

     public function testRegisterFaild()
     {
          $this->expectException(ValidationException::class);

          $request = new UserRegisterRequest();
          $request->id = "";
          $request->name = "";
          $request->password = "";

          $this->userService->register($request);
     }

     public function testRegisterDuplicate()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = "qwerty";

          $this->userRepository->save($user);
          $this->expectException(ValidationException::class);

          $request = new UserRegisterRequest();
          $request->id = "iqbal";
          $request->name = "Iqbal";
          $request->password = "qwerty";

          $this->userService->register($request);
     }

     public function testLoginNotFound()
     {
          $this->expectException(ValidationException::class);

          $request = new UserLoginRequest();
          $request->id = "eko";
          $request->password = "eko";

          $this->userService->login($request);
     }

     public function testLoginPasswordWrong()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = password_hash("iqbal", PASSWORD_BCRYPT);

          $this->expectException(ValidationException::class);

          $request = new UserLoginRequest();
          $request->id = "eko";
          $request->password = "salah";

          $this->userService->login($request);
     }

     public function testLoginSuccess()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = password_hash("qwerty", PASSWORD_BCRYPT);

          $this->expectException(ValidationException::class);

          $request = new UserLoginRequest();
          $request->id = "iqbal";
          $request->password = "qwerty";

          $response = $this->userService->login($request);

          self::assertEquals($request->id, $response->user->id);
          self::assertTrue(password_verify($request->password, $response->user->password));
     }

     public function testUpdateSuccess()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = password_hash("qwerty", PASSWORD_BCRYPT);
          $this->userRepository->save($user);

          $request = new UserProfileUpdateRequest();
          $request->id = "iqbal";
          $request->name = "Budi";

          $this->userService->updateProfile($request);

          $result = $this->userRepository->findById($user->id);

          self::assertEquals($request->name, $result->name);
     }

     public function testUpdateValidationError()
     {
          $this->expectException(ValidationException::class);

          $request = new UserProfileUpdateRequest();
          $request->id = "";
          $request->name = "";

          $this->userService->updateProfile($request);
     }

     public function testUpdateNotFound()
     {
          $this->expectException(ValidationException::class);

          $request = new UserProfileUpdateRequest();
          $request->id = "iqbal";
          $request->name = "Budi";

          $this->userService->updateProfile($request);
     }

     public function testUpdatePasswordSuccess()
     {
          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = password_hash("qwerty", PASSWORD_BCRYPT);
          $this->userRepository->save($user);

          $request = new UserPasswordUpdateRequest();
          $request->id = "iqbal";
          $request->oldPassword = "qwerty";
          $request->newPassword = "rahasia";

          $this->userService->updatePassword($request);

          $result = $this->userRepository->findById($user->id);
          self::assertTrue(password_verify($request->newPassword, $result->password));
     }

     public function testUpdatePasswordValidationError()
     {
          $this->expectException(ValidationException::class);

          $request = new UserPasswordUpdateRequest();
          $request->id = "iqbal";
          $request->oldPassword = "qwerty";
          $request->newPassword = "rahasia";

          $this->userService->updatePassword($request);
     }

     public function testUpdatePasswordWrongOldPassword()
     {
          $this->expectException(ValidationException::class);

          $user = new User();
          $user->id = "iqbal";
          $user->name = "Iqbal";
          $user->password = password_hash("qwerty", PASSWORD_BCRYPT);
          $this->userRepository->save($user);

          $request = new UserPasswordUpdateRequest();
          $request->id = "iqbal";
          $request->oldPassword = "salah";
          $request->newPassword = "rahasia";

          $this->userService->updatePassword($request);
     }

     public function testUpdatePasswordNotFound()
     {
          $this->expectException(ValidationException::class);

          $request = new UserPasswordUpdateRequest();
          $request->id = "iqbal";
          $request->oldPassword = "qwerty";
          $request->newPassword = "rahasia";

          $this->userService->updatePassword($request);
     }
}
