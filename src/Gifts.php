<?php
declare(strict_types = 1);

namespace Xamplifier\Giftbit;


class Gifts extends Giftbit
{

    protected const OBJECT_NAME = 'gifts';


    /**
    * Retrieves a list of gifts
    *
    * @param array $parameters The filters
    *
    * @todo modulirize the filter array
    *
    * @return array
    */
    public static function getListGifts(array $parameters = []):array
    {

        $data = [];
        $data = [
            'uuid' => $parameters['uuid'] ?? '',
            'campaign_uuid' => $parameters['campaign_uuid'] ?? '',
            'campaign_id' => $parameters['campaign_id'] ?? '',
            'price_in_cents_greater_than' => $parameters['price_in_cents_greater_than'] ?? '',
            'price_in_cents_less_than' => $parameters['price_in_cents_less_than'] ?? '',
            'recipient_name' => $parameters['recipient_name'] ?? '',
            'recipient_email' => $parameters['recipient_email'] ?? '',
            'delivery_status' => $parameters['delivery_status'] ?? '',
            'status' => $parameters['status'] ?? '',
            'created_date_greater_than' => $parameters['created_date_greater_than'] ?? '',
            'delivery_date_greater_than' => $parameters['delivery_date_greater_than'] ?? '',
            'delivery_date_less_than' => $parameters['delivery_date_less_than'] ?? '',
            'redelivery_count_greater_than' => $parameters['redelivery_count_greater_than'] ?? '',
            'redelivery_count_less_than' => $parameters['redelivery_count_less_than'] ?? '',
            'redeemed_date_greater_than' => $parameters['redeemed_date_greater_than'] ?? '',
            'redeemed_date_less_than' => $parameters['redeemed_date_less_than'] ?? '',
            'limit' => $parameters['limit'] ?? '',
            'offset' => $parameters['offset'] ?? '',
            'sort'  => $parameters['sort'] ?? '',
            'order' => $parameters['order'] ?? '',
            'created_date_less_than' => $parameters['created_date_less_than'] ?? ''

        ];

        $gb = new Giftbit;

        return $gb->execute(self::OBJECT_NAME, $data, "GET", 'query');
    }


    /**
    * Retrieves a single gift
    *
    * @param string $uuid
    * @param array $data
    *
    *
    * @return array
    */
    public static function getGift(string $uuid, array $data = []):array
    {

      $gb = new Giftbit;

      return $gb->execute(self::OBJECT_NAME.'/'.$uuid, $data, "GET", 'query');

    }


    /**
    * Resends an individual gift offers in addition to retrieving their details
    *
    * @param string $uuid
    * @param array $data
    *
    *
    * @return array
    */
    public static function resendGift(string $uuid, array $data = []):array
    {

      $gb = new Giftbit;

      return $gb->execute(self::OBJECT_NAME.'/'.$uuid, $data, "PUT", 'json');

    }


    
    /**
    * Deletes an individual gift.
    * Note that once a gift offer has been claimed by the recipient it cannot be canceled
    *
    * @param string $uuid
    * @param array $data
    *
    *
    * @return array
    */
    public static function deleteGift(string $uuid, array $data = []):array
    {

      $gb = new Giftbit;

      return $gb->execute(self::OBJECT_NAME.'/'.$uuid, $data, "DELETE", 'query');

    }



}
