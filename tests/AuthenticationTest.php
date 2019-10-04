<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Giftbit;


class AuthenticationTest extends TestCase
{

  public function testAuthentication()
  {
      $brands = Giftbit::ping();
      $this->assertTrue($brands['success']);

  }



}
