<?php

namespace Iqbal\LoginManagement\App {
     function header(string $value)
     {
          echo $value;
     }
}

namespace Iqbal\LoginManagement\Service {
     function setcookie(string $name, string $value)
     {
          echo "$name: $value";
     }
}
