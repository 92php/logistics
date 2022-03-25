<?php

class CreateUserCest
{

    public function _before(ApiTester $I)
    {

    }

    public function createUserViaAPI(\ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
        $I->sendGet('/wuliu/package/index?access_token=Lcw40PdMqbZ1vxfu5JYuLeHDd6SPLWKC&package_number=&order_number=&waybill_number=&shop_name=&page=1&delivery_begin_datetime=&delivery_end_datetime=&line_id=&status=100&expand=country,line,routes,line.company', [
            'name' => 'davert',
            'email' => 'davert@codeception.com'
        ]);
        $I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK); // 200
        $I->seeResponseIsJson();
//        $I->seeResponseIsHello();
//        $I->seeResponseContains('{"result":"ok"}');
    }

    // tests
    public function tryToTest(ApiTester $I)
    {
    }
}
