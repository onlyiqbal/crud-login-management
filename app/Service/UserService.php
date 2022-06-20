<?php

namespace Iqbal\Belajar\PHP\MVC\Service;

use Exception;
use Iqbal\Belajar\PHP\MVC\Config\Database;
use Iqbal\Belajar\PHP\MVC\Domain\User;
use Iqbal\Belajar\PHP\MVC\Exception\ValidationException;
use Iqbal\Belajar\PHP\MVC\Model\UserLoginRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserLoginResponse;
use Iqbal\Belajar\PHP\MVC\Model\UserPasswordUpdateRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserPasswordUpdateResponse;
use Iqbal\Belajar\PHP\MVC\Model\UserProfileUpdateRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserProfileUpdateResponse;
use Iqbal\Belajar\PHP\MVC\Model\UserRegisterRequest;
use Iqbal\Belajar\PHP\MVC\Model\UserRegisterResponse;
use Iqbal\Belajar\PHP\MVC\Repository\UserRepository;

class UserService
{
     private UserRepository $userRepository;

     public function __construct(UserRepository $userRepository)
     {
          $this->userRepository = $userRepository;
     }

     public function register(UserRegisterRequest $request): UserRegisterResponse
     {
          $this->validateUserRegistrationRequest($request);

          $user = $this->userRepository->findById($request->id);
          if ($user != null) {
               throw new ValidationException("User yang Anda masukan sudah terdaftar");
          }

          try {
               Database::beginTransaction();
               $user = new User();
               $user->id = $request->id;
               $user->name = $request->name;
               $user->password = password_hash($request->password, PASSWORD_BCRYPT);

               $this->userRepository->save($user);

               $response = new UserRegisterResponse();
               $response->user = $user;

               Database::commitTranscation();

               return $response;
          } catch (\Exception $exception) {
               Database::rollbackTransaction();
               throw $exception;
          }
     }

     private function validateUserRegistrationRequest(UserRegisterRequest $request)
     {
          if ($request->id == null || $request->name == null || $request->password == null || trim($request->id) == "" || trim($request->name) == "" || trim($request->password) == "") {
               throw new ValidationException("Id, nama, password tidak boleh kosong");
          }
     }

     public function login(UserLoginRequest $requst): UserLoginResponse
     {
          $this->validateUserLoginRequest($requst);

          $user = $this->userRepository->findById($requst->id);
          if ($user == null) {
               throw new ValidationException("Id atau password salah");
          }

          if (password_verify($requst->password, $user->password)) {
               $response = new UserLoginResponse();
               $response->user = $user;
               return $response;
          } else {
               throw new ValidationException("Id atau password salah");
          }
     }

     private function validateUserLoginRequest(UserLoginRequest $request)
     {
          if ($request->id == null || $request->password == null || trim($request->id) == "" || trim($request->password) == "") {
               throw new ValidationException("Id, password tidak boleh kosong");
          }
     }

     public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
     {
          $this->validateUserProfileUpdateRequest($request);
          try {
               Database::beginTransaction();

               $user = $this->userRepository->findById($request->id);
               if ($user == null) {
                    throw new ValidationException("User tidak ditemukan");
               }

               $user->name = $request->name;
               $this->userRepository->update($user);

               Database::commitTranscation();

               $response = new UserProfileUpdateResponse();
               $response->user = $user;
               return $response;
          } catch (\Exception $exception) {
               Database::rollbackTransaction();
               throw $exception;
          }
     }

     private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request)
     {
          if ($request->id == null || $request->name == null || trim($request->id) == "" || trim($request->name) == "") {
               throw new ValidationException("Id, nama tidak boleh kosong");
          }
     }

     public function updatePassword(UserPasswordUpdateRequest $request): UserPasswordUpdateResponse
     {
          $this->validateUserPasswordUpdateRequest($request);

          try {
               Database::beginTransaction();

               $user = $this->userRepository->findById($request->id);
               if ($user == null) {
                    throw new ValidationException("User tidak boleh kosong");
               }

               if (!password_verify($request->oldPassword, $user->password)) {
                    throw new ValidationException("Password lama salah");
               }

               $user->password = password_hash($request->newPassword, PASSWORD_BCRYPT);
               $this->userRepository->update($user);

               Database::commitTranscation();

               $response = new UserPasswordUpdateResponse();
               $response->user = $user;
               return $response;
          } catch (\Exception $exception) {
               Database::rollbackTransaction();
               throw $exception;
          }
     }

     private function validateUserPasswordUpdateRequest(UserPasswordUpdateRequest $request)
     {
          if ($request->id == null || $request->oldPassword == null || $request->newPassword == null || trim($request->id) == "" || trim($request->oldPassword) == "" || trim($request->newPassword) == "") {
               throw new ValidationException("Id,password lama, password baru tidak boleh kosong");
          }
     }
}
