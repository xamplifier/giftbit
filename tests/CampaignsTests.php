<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Campaigns;


class CampaignsTests extends TestCase
{

    public function testCreateCampaigns()
    {
        $data = [];

        $data = [
        'message' => "this is paok campaign",
        "subject" => "paok",
        "contacts" => [
             ["firstname" => "Perry",
             "lastname" => "Johnson",
             "email" => "pjohnson@giftbit.com"]
             ,
             ["firstname"=> "Rita",
             "lastname" => "Robson",
             "email" => "robson@giftbit.com"]
           ],
         "price_in_cents" => 5000,
         "brand_codes" => [
           "itunesus",
           "amazonus"
         ],
         "expiry" => "2019-12-01",
         "id" => "abcy_".rand(1,1000)

       ];

        $campaign = Campaigns::createCampaign($data);

        $this->assertTrue($campaign['success']);

    }


    public function testGetCampagn()
    {
        $campaignId = "abcy_219";
        $campaign = Campaigns::getCampaign($campaignId);

        $this->assertTrue($campaign['success']);

    }





}
