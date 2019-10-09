<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Funds extends Giftbit
{

    protected const OBJECT_NAME = 'funds';


    /**
    * Lists all available regions.
    *
    * @param array $data
    *
    * @return array
    */
    public static function getFunds(array $data = []):array
    {
        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "GET", 'query');
    }


}
