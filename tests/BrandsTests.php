<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Brands;


class BrandsTests extends TestCase
{

    public function testgetListBrandsSuccess()
    {
        $brands = Brands::getListBrands();
        $this->assertTrue($brands['success']);
    
    }


    public function testArray()
    {
        $brands = Brands::getListBrands();
        $this->assertIsArray($brands);

    }


    public function testArrayHasBrands()
    {
        $brands = Brands::getListBrands();
        $this->assertArrayHasKey('brands', $brands['data']);

    }

    public function testFetchBrandSuccess()
    {

        $brands = Brands::fetchBrand('nike');
        $this->assertTrue($brands['success']);

    }


    public function testThatIsFetchingRequestedBrand()
    {

        $brands = Brands::fetchBrand('nike');
        $this->assertSame('nike', $brands['data']['brand']['brand_code']);

    }




}
