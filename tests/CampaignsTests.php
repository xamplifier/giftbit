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
        "templateId" => 'PMKTNMETZUHB',
        "firstName" => "AposTolis",
        "lastName" => "Flessas",
        "email" => "abcy122@googlemail.com",
        "amount" => 5000,
        "expiry" => "2019-12-01",
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
