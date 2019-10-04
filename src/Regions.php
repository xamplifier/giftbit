<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Regions extends Giftbit
{

    protected const OBJECT_NAME = 'regions';


    /**
    * Lists all available regions.
    *
    * @param array $data
    *
    * @return array
    */
    public static function getListRegions(array $data = []):array
    {
        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "GET", 'query');
    }


}
