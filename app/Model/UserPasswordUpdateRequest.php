<?php

namespace Iqbal\LoginManagement\Model;

class UserPasswordUpdateRequest
{
     public ?string $id = null;
     public ?string $oldPassword = null;
     public ?string $newPassword = null;
}
