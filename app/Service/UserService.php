<?php

namespace Iqbal\LoginManagement\Service;

use Iqbal\LoginManagement\Config\Database;
use Iqbal\LoginManagement\Domain\User;
use Iqbal\LoginManagement\Exception\ValidationException;
use Iqbal\LoginManagement\Model\UserLoginRequest;
use Iqbal\LoginManagement\Model\UserLoginResponse;
use Iqbal\LoginManagement\Model\UserPasswordUpdateRequest;
use Iqbal\LoginManagement\Model\UserPasswordUpdateResponse;
use Iqbal\LoginManagement\Model\UserProfileUpdateRequest;
use Iqbal\LoginManagement\Model\UserProfileUpdateResponse;
use Iqbal\LoginManagement\Model\UserRegisterRequest;
use Iqbal\LoginManagement\Model\UserRegisterResponse;
use Iqbal\LoginManagement\Repository\UserRepository;

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

               Database::commitTransaction();

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

     public function login(UserLoginRequest $request): UserLoginResponse
     {
          $this->validateUserLoginRequest($request);

          $user = $this->userRepository->findById($request->id);
          if ($user == null) {
               throw new ValidationException("Id atau password salah");
          }

          if (password_verify($request->password, $user->password)) {
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

               Database::commitTransaction();

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

               Database::commitTransaction();

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
