<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Regions;


class RegionsTests extends TestCase
{

    public function testgetListBrandsSuccess()
    {
        $regions = Regions::getListRegions();
        $this->assertTrue($regions['success']);

    }





}
