<?php

namespace Xamplifier\Giftbit\Tests;

use PHPUnit\Framework\TestCase;
use Xamplifier\Giftbit\Gifts;


class GiftsTests extends TestCase
{

    public function testGetListGiftsSuccess()
    {
        $data = [];
        $data = ['uuid' => 'abcy_219'];

        $gifts = Gifts::getListGifts($data);
        $this->assertTrue($gifts['success']);

    }


    public function testGetGiftSuccess()
    {
        $gift = Gifts::getGift('5a36f349b93a4de5934cbf36e41bcebd');
        $this->assertTrue($gift['success']);

    }


    // public function testResendGiftSuccess()
    // {
    //     $data = [];
    //     $data = ['resend' => true];
    //     $gift = Gifts::resendGift('5a36f349b93a4de5934cbf36e41bcebd', $data);
    //     $this->assertTrue($gift['success']);
    //
    // }


    public function testDeleteGiftSuccess()
    {
        $data = [];
        $data = ['resend' => true];
        $gift = Gifts::deleteGift('5a36f349b93a4de5934cbf36e41bcebd', $data);
        $this->assertTrue($gift['success']);

    }


}
