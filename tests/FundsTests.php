<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Funds;


class FundsTests extends TestCase
{

    public function testGetFundsSuccess()
    {
        $funds = Funds::getFunds();
        dd($funds);
        $this->assertTrue($funds['success']);

    }





}
