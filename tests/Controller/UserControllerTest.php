<?php

namespace Iqbal\Belajar\PHP\MVC\Controller {

     require_once __DIR__ . "/../Helper/helper.php";

     use Iqbal\LoginManagement\Config\Database;
     use Iqbal\LoginManagement\Controller\UserController;
     use Iqbal\LoginManagement\Domain\Session;
     use Iqbal\LoginManagement\Domain\User;
     use Iqbal\LoginManagement\Repository\SessionRepository;
     use Iqbal\LoginManagement\Repository\UserRepository;
     use Iqbal\LoginManagement\Service\SessionService;
     use PHPUnit\Framework\TestCase;

     class UserControllerTest extends TestCase
     {
          private UserController $userController;
          private UserRepository $userRepository;
          private SessionRepository $sessionRepository;

          protected function setUp(): void
          {
               $this->userController = new UserController();

               $this->sessionRepository = new SessionRepository(Database::getConnection());
               $this->sessionRepository->deleteAll();

               $this->userRepository = new UserRepository(Database::getConnection());
               $this->userRepository->deleteAll();

               putenv("mode=test");
          }

          public function testRegister()
          {
               $this->userController->register();

               $this->expectOutputRegex('[Register]');
               $this->expectOutputRegex('[Id]');
               $this->expectOutputRegex('[Name]');
               $this->expectOutputRegex('[Password]');
               $this->expectOutputRegex('[Register new User]');
          }

          public function testPostRegisterSuccess()
          {
               $_POST['id'] = 'iqbal';
               $_POST['name'] = 'Iqbal';
               $_POST['password'] = 'qwerty';

               $this->userController->postRegister();

               $this->expectOutputRegex("[Location: /users/login]");
          }

          public function testValidationError()
          {
               $_POST['id'] = '';
               $_POST['name'] = '';
               $_POST['password'] = '';

               $this->userController->postRegister();

               $this->expectOutputRegex('[Register]');
               $this->expectOutputRegex('[Id]');
               $this->expectOutputRegex('[Name]');
               $this->expectOutputRegex('[Password]');
               $this->expectOutputRegex('[Register new User]');
               $this->expectOutputRegex('[Id, nama, password tidak boleh kosong]');
          }

          public function testPostRegisterDuplicate()
          {
               $user = new User();
               $user->id = 'iqbal';
               $user->name = 'Iqbal';
               $user->password = 'qwerty';

               $this->userRepository->save($user);

               $_POST['id'] = 'iqbal';
               $_POST['name'] = 'Iqbal';
               $_POST['password'] = 'qwerty';

               $this->userController->postRegister();

               $this->expectOutputRegex('[Register]');
               $this->expectOutputRegex('[Id]');
               $this->expectOutputRegex('[Name]');
               $this->expectOutputRegex('[Password]');
               $this->expectOutputRegex('[Register new User]');
               $this->expectOutputRegex('[User yang Anda masukan sudah terdaftar]');
          }

          public function testLogin()
          {
               $this->userController->login();

               $this->expectOutputRegex("[Login user]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[Password]");
          }

          public function testLoginSuccess()
          {
               $user = new User();
               $user->id = 'iqbal';
               $user->name = 'Iqbal';
               $user->password = password_hash('qwerty', PASSWORD_BCRYPT);

               $this->userRepository->save($user);

               $_POST['id'] = 'iqbal';
               $_POST['password'] = 'qwerty';

               $this->userController->postLogin();

               $this->expectOutputRegex("[Location: /]");
               $this->expectOutputRegex("[X-IQBAL-SESSION: ]");
          }

          public function testLoginValidationError()
          {
               $_POST['id'] = '';
               $_POST['password'] = '';

               $this->userController->postLogin();

               $this->expectOutputRegex("[Login user]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id, password tidak boleh kosong]");
          }

          public function testLoginUserNotFound()
          {
               $_POST['id'] = 'not found';
               $_POST['password'] = 'not found';

               $this->userController->postLogin();

               $this->expectOutputRegex("[Login user]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id atau password salah]");
          }

          public function testLoginWrongPassword()
          {
               $user = new User();
               $user->id = 'iqbal';
               $user->name = 'Iqbal';
               $user->password = password_hash('qwerty', PASSWORD_BCRYPT);

               $this->userRepository->save($user);

               $_POST['id'] = 'iqbal';
               $_POST['password'] = 'salah';

               $this->userController->postLogin();

               $this->expectOutputRegex("[Login user]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id atau password salah]");
          }

          public function testlogout()
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

               $this->userController->logout();

               $this->expectOutputRegex("[Location: /]");
               $this->expectOutputRegex("[X-IQBAL-SESSION: ]");
          }

          public function testUpdateProfile()
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

               $this->userController->updateProfile();

               $this->expectOutputRegex("[Profile]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[iqbal]");
               $this->expectOutputRegex("[Name]");
               $this->expectOutputRegex("[Iqbal]");
          }

          public function testPostUpdateProfile()
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

               $_POST['name'] = "Budi";
               $this->userController->postUpdateProfile();

               $this->expectOutputRegex("[Location: /]");

               $result = $this->userRepository->findById('iqbal');
               self::assertEquals($result->name, "Budi");
          }

          public function testUpdateProfileValidationError()
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

               $_POST['name'] = "";
               $this->userController->postUpdateProfile();

               $this->expectOutputRegex("[Profile]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[iqbal]");
               $this->expectOutputRegex("[Name]");
               $this->expectOutputRegex("[Id, nama tidak boleh kosong]");
          }

          public function testUpdatePassword()
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

               $this->userController->updatePassword();

               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[iqbal]");
          }

          public function testPostUpdatePasswordSuccess()
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

               $_POST['oldPassword'] = "qwerty";
               $_POST['newPassword'] = "rahasia";

               $this->userController->postUpdatePassword();

               $this->expectOutputRegex("[Location: /]");

               $result = $this->userRepository->findById($user->id);

               $this->assertTrue(password_verify("rahasia", $result->password));
          }

          public function testPostUpdatePasswordValidationError()
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

               $_POST['oldPassword'] = "";
               $_POST['newPassword'] = "";

               $this->userController->postUpdatePassword();


               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[iqbal]");
               $this->expectOutputRegex("[Id,password lama, password baru tidak boleh kosong]");
          }

          public function testPostUpdatePasswordWrongOldPassword()
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

               $_POST['oldPassword'] = "salah";
               $_POST['newPassword'] = "new";

               $this->userController->postUpdatePassword();


               $this->expectOutputRegex("[Password]");
               $this->expectOutputRegex("[Id]");
               $this->expectOutputRegex("[iqbal]");
               $this->expectOutputRegex("[Password lama salah]");
          }
     }
}
