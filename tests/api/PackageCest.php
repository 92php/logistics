<?php

class PackageCest
{

    public function _before(ApiTester $I)
    {
    }

    // tests
    public function tryForbiddenTest(ApiTester $I)
    {
        $I->sendGET('/wuliu/package/view', ['id' => 1]);
        $I->seeResponseContains("Forbidden");
        $response = $I->grabResponse();
        $response = json_decode($response, true);
        echo "Code = " . $response['error']['status'];
    }

    public function tryCreateTest(ApiTester $I)
    {
        $fake = new Faker\Provider\ar_JO\Address();
    }

}
