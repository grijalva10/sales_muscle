<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::import('application.components.X2Settings.*');

/**
 * @package application.tests.unit.components.x2flow.actions
 * @requires OS Linux 
 */
class X2FlowApiCallTest extends X2FlowTestBase {

    public $fixtures = array (
        'x2flow' => array ('X2Flow', '.X2FlowApiCallTest'),
        'contacts' => 'Contacts',
    );

    public static function setUpBeforeClass () {
        // replace token in flow with base 64 encoded auth info
        $fixtureDir = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','..','..','..','fixtures'));
        $template = $fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowApiCallTestTemplate.php';
        $file = $fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowApiCallTest.php';
        $username = 'admin';
        $userKey = User::model ()->findByPk (1)->userKey;
        X2_TEST_DEBUG_LEVEL > 1 && println ($userKey);
        $content = file_get_contents ($template);
        $content = preg_replace (
            '/ENCODED_AUTH_INFO/', base64_encode ($username.':'.$userKey), $content);
        $content = preg_replace (
            '/TEST_BASE_URL/', TEST_BASE_URL, $content);
        X2_TEST_DEBUG_LEVEL > 1 && print ($content);
        file_put_contents ($file, $content);
        parent::setUpBeforeClass ();
    }

    public static function tearDownAfterClass () {
        parent::tearDownAfterClass();
        $fixtureDir = implode(DIRECTORY_SEPARATOR, array(__DIR__,'..','..','..','..','fixtures'));
        if(file_exists($fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowApiCallTest.php')) {
            unlink($fixtureDir.DIRECTORY_SEPARATOR.'x2_flows.X2FlowApiCallTest.php');
        }
    }

    public function testConstantSet () {
        // must be set to true so that api requests don't actually get made and instead the HTTP
        // message data is written to the trigger log
        $this->assertEquals (true, YII_UNIT_TESTING);
        if (!YII_UNIT_TESTING) {
            self::$skipAllTests = true;
        }
    }

    public function setOffTrigger () {
        $contact = $this->contacts ('testAnyone');
        $contact->firstName = 'not test';
        $this->assertSaves ($contact);
    }

    public function makeRequest ($httpOptions) {
        $url = $httpOptions['url'];
        unset ($httpOptions['url']);
        $this->assertTrue (
            @file_get_contents ($url, false, stream_context_create ($httpOptions)) !== false);
    }

    public function setMakeRequest ($makeRequest) {
        TestingAuxLib::setPrivateProperty ('X2FlowApiCall', '_makeRequest', $makeRequest);
    }

    /**
     * Returns the api debug message generated by the X2FlowApiCall flow action class.  Assumes that
     * The api call action is the last item in the flow and that the entire flow executed.
     * @return string 
     */
    public function getApiCallDebugMessage ($flowKey) {
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($this->getTraceByFlowId ($this->x2flow ($flowKey)->id));
        $log = $this->flattenTrace ($this->getTraceByFlowId ($this->x2flow ($flowKey)->id));
        $lastEntry = array_pop ($log);
        return $lastEntry['message'];
    }

    public function testApiCallPOST () {
        $this->setMakeRequest (false);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $debugMessage = $this->getApiCallDebugMessage ('flow0');
        $this->assertEquals ('POST', $debugMessage['method']);
        $this->assertEquals (
            'paramName0=paramValue0&paramName1=paramValue1', $debugMessage['content']);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow0')->id);
        $this->assertTrue ($this->checkTrace ($log));
        /*$this->makeRequest ($debugMessage);

        $this->setMakeRequest (true);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $log = $this->getTraceByFlowId ($this->x2flow ('flow0')->id);
        $this->assertTrue ($this->checkTrace ($log)); */
    }   

    public function testApiCallJSONPOSTLegacy () {
        $this->setMakeRequest (false);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $debugMessage = $this->getApiCallDebugMessage ('flow1');
        $this->assertEquals ('POST', $debugMessage['method']);
        $data = CJSON::encode (array (
                'paramName0' => 'paramValue0',
                'paramName1' => 'paramValue1',
            ));
        $this->assertEquals (
            $data, $debugMessage['content']);
        $this->assertEquals (
            "Content-Type: application/json\r\nContent-Length: ".strlen ($data), 
            $debugMessage['header']);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($log));
        /*$this->makeRequest ($debugMessage);

        $this->setMakeRequest (true);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($log)); */
    }   

    public function testApiCallJSONPOST () {
        $this->setMakeRequest (false);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $debugMessage = $this->getApiCallDebugMessage ('flow4');
        $this->assertEquals ('POST', $debugMessage['method']);
        $data = CJSON::encode (array (
            'paramName0' => 'paramValue0',
            'paramName1' => 'paramValue1',
            'paramName2' => array (
                'nestedParamName0' => 'nestedParamValue0',
                'nestedParamName1' => 'nestedParamValue1',
            )
        ));
        $this->assertEquals (
            $data, $debugMessage['content']);
        $this->assertEquals (
            "Content-Type: application/json\r\nContent-Length: ".strlen ($data), 
            $debugMessage['header']);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow4')->id);
        $this->assertTrue ($this->checkTrace ($log));
        /*$this->makeRequest ($debugMessage);

        $this->setMakeRequest (true);
        $this->clearLogs ();
        $this->setOffTrigger ()
        $log = $this->getTraceByFlowId ($this->x2flow ('flow1')->id);
        $this->assertTrue ($this->checkTrace ($log)); */
    }   

    public function testApiCallGET () {
        $this->setMakeRequest (false);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $debugMessage = $this->getApiCallDebugMessage ('flow2');
        $this->assertEquals ('GET', $debugMessage['method']);
        $this->assertEquals (
            'http://localhost?paramName0=paramValue0&paramName1=paramValue1', $debugMessage['url']);
        $log = $this->getTraceByFlowId ($this->x2flow ('flow2')->id);
        $this->assertTrue ($this->checkTrace ($log));
        /*$this->makeRequest ($debugMessage);

        $this->setMakeRequest (true);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $log = $this->getTraceByFlowId ($this->x2flow ('flow2')->id);
        $this->assertTrue ($this->checkTrace ($log)); */
    }   

    /**
     * Ensure that requests can't be made to X2Engine when exact domain match is specified
     */
    public function testCallX2Api () {
        $parts = parse_url (TEST_BASE_URL);
        TestingAuxLib::loadControllerMock ($parts['host']);
        $validateUrl = TestingAuxLib::setPublic (new X2FlowApiCall, 'validateUrl');
        $this->assertFalse ($validateUrl (TEST_BASE_URL.'api2/Contacts'));
        $this->setMakeRequest (true);
        $this->clearLogs ();
        $this->setOffTrigger ();
        $log = $this->getTraceByFlowId ($this->x2flow ('flow3')->id);
        X2_TEST_DEBUG_LEVEL > 1 && print_r ($log); 
        $this->assertFalse ($this->checkTrace ($log));
        $debugMessage = $this->getApiCallDebugMessage ('flow3');
        $this->assertEquals (
            TEST_BASE_URL.'api2/Contacts', $debugMessage['url']);
        TestingAuxLib::restoreController();
    }

//    /**
//     * Test live API call using X2's API. Note that it is NOT advisable to perform API requests 
//     * from X2 since it can result in an infinite loop.
//     */
//    public function testLiveApiCall () {
//        $newContact = Contacts::model ()->findByAttributes (array (
//            'firstName' => 'testApiFlowAction',
//            'lastName' => 'testApiFlowAction',
//            'email' => 'test@test.com',
//        ));
//        $this->assertTrue ($newContact === null);
//
//        $this->setMakeRequest (true);
//        $this->clearLogs ();
//        $this->setOffTrigger ();
//        $log = $this->getTraceByFlowId ($this->x2flow ('flow3')->id);
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($log); 
//        $this->assertTrue ($this->checkTrace ($log));
//        $newContact = Contacts::model ()->findByAttributes (array (
//            'firstName' => 'testApiFlowAction',
//            'lastName' => 'testApiFlowAction',
//            'email' => 'test@test.com',
//        ));
//        $this->assertTrue ($newContact !== null);
//        $newContact->delete ();
//
//        // now repeat flow without actual request to ensure at least that request was formatted
//        // properly
//        $this->setMakeRequest (false);
//        $this->clearLogs ();
//        $this->setOffTrigger ();
//        $log = $this->getTraceByFlowId ($this->x2flow ('flow3')->id);
//        $debugMessage = $this->getApiCallDebugMessage ('flow3');
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($log); 
//        $this->assertTrue ($this->checkTrace ($log));
//        $this->assertEquals (
//            'http://127.0.0.1/index.php/api2/Contacts', $debugMessage['url']);
//    }
//
//    /**
//     * Test live API call using X2's API. Note that it is NOT advisable to perform API requests 
//     * from X2 since it can result in an infinite loop.
//     */
//    public function testLiveApiCallJSONPayload () {
//        $newContact = Contacts::model ()->findByAttributes (array (
//            'firstName' => 'testApiFlowAction2',
//            'lastName' => 'testApiFlowAction2',
//        ));
//        $this->assertTrue ($newContact === null);
//
//        $this->setMakeRequest (true);
//        $this->clearLogs ();
//        $this->setOffTrigger ();
//        $log = $this->getTraceByFlowId ($this->x2flow ('flow5')->id);
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($log); 
//        $this->assertTrue ($this->checkTrace ($log));
//        $newContact = Contacts::model ()->findByAttributes (array (
//            'firstName' => 'testApiFlowAction2',
//            'lastName' => 'testApiFlowAction2',
//        ));
//        $this->assertTrue ($newContact !== null);
//        $newContact->delete ();
//
//        // now repeat flow without actual request to ensure at least that request was formatted
//        // properly
//        $this->setMakeRequest (false);
//        $this->clearLogs ();
//        $this->setOffTrigger ();
//        $log = $this->getTraceByFlowId ($this->x2flow ('flow5')->id);
//        $debugMessage = $this->getApiCallDebugMessage ('flow5');
//        X2_TEST_DEBUG_LEVEL > 1 && print_r ($log); 
//        $this->assertTrue ($this->checkTrace ($log));
//        $this->assertEquals (
//            'http://127.0.0.1/index.php/api2/Contacts', $debugMessage['url']);
//
//    }
}

?>
