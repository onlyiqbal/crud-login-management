<?php

namespace Iqbal\Belajar\PHP\MVC\Config;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
     public function testGetConnection()
     {
          $connection = Database::getConnection();
          self::assertNotNull($connection);
     }

     public function testGetConnectionSigleton()
     {
          $connection1 = Database::getConnection();
          $connection2 = Database::getConnection();
          self::assertSame($connection1, $connection2);
     }
}
