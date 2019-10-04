<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Campaigns extends Giftbit
{

    protected const OBJECT_NAME = 'campaign';


    /**
    * Creates a single campaign
    *
    * @param array $data The required attributes
    *
    * @todo validate the required fields
    * @return array
    */
    public static function createCampaign(array $data = []):array
    {
        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "POST", 'json');
    }


    /**
    * Retrieves a single campaign
    *
    * @param array $data
    * @param string $campaignId
    * 
    * @return array
    */
    public static function getCampaign(string $campaignId, array $data = []):array
    {

        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME.'/'.$campaignId, $data, "GET", 'query');
    }




}
