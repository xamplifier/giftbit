<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Brands extends Giftbit
{

    protected const OBJECT_NAME = 'brands';



   /**
   * Returns Giftbit's gift card brands available to you. It provides complete search, filter, and paging functionality and is used in all use cases.
   *
   * @param array $data
   *
   * @return array
   */
    public static function getListBrands(array $data = []):array
    {
        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "GET", 'query');
    }


    /**
    * Retrieves a single brand
    *
    * @param array $data
    * @param string $brandCode
    *
    * @return array
    */
    public static function fetchBrand($brandCode, array $data = []):array
    {

        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME.'/'.$brandCode, $data, "GET", 'query');
    }





}
