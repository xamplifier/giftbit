<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Embedded extends Giftbit
{

    protected const OBJECT_NAME = 'embedded';


    /**
    * Produces a digital gift for immediate in-app delivery,
    * allowing your user to claim their gift within your own web or mobile application
    *
    * @param array $data
    *
    * @return array
    */
    public static function createEmbedded(array $data = []):array
    {
        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "POST", 'json');
    }




}
