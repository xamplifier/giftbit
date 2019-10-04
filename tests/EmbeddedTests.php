<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Embedded;


class EmbeddedTests extends TestCase
{

    public function testCreateEmbedded()
    {
        $data = [];
        $data = [
          "brand_code" => "itunesus",
          "price_in_cents" =>  2500,
          "id" => "myGift".rand(1,1000)
        ];

        $embedded = Embedded::createEmbedded($data);
        $this->assertTrue($embedded['success']);

    }





}
