<?php

use OpenSRS\domains\lookup\LookupDomain;
/**
 * @group lookup
 * @group LookupDomain
 */
class LookupDomainTest extends PHPUnit_Framework_TestCase
{
    protected $func = "lookupDomain";

    protected $validSubmission = array(
        'attributes' => array(
            'domain' => '',
            ),
        );

    /**
     * Valid submission should complete with no
     * exception thrown
     *
     * @return void
     *
     * @group validsubmission
     */
    public function testValidSubmission() {
        $data = json_decode( json_encode ($this->validSubmission) );

        $data->attributes->domain = 'phptest' . time() . '.com';

        $ns = new LookupDomain( 'array', $data );

        $this->assertTrue( $ns instanceof LookupDomain );
    }

    /**
     * Data Provider for Invalid Submission test
     */
    function submissionFields() {
        return array(
            'missing domain' => array('domain'),
            );
    }

    /**
     * Invalid submission should throw an exception
     *
     * @return void
     *
     * @dataProvider submissionFields
     * @group invalidsubmission
     */
    public function testInvalidSubmissionFieldsMissing( $field, $parent = 'attributes', $message = null ) {
        $data = json_decode( json_encode($this->validSubmission) );

        $data->attributes->domain = 'phptest' . time() . '.com';

        $this->setExpectedException( 'OpenSRS\Exception' );

        if(is_null($message)){
          $this->setExpectedExceptionRegExp(
              'OpenSRS\Exception',
              "/$field.*not defined/"
              );
        }
        else {
          $this->setExpectedExceptionRegExp(
              'OpenSRS\Exception',
              "/$message/"
              );
        }



        // clear field being tested
        if(is_null($parent)){
            unset( $data->$field );
        }
        else{
            unset( $data->$parent->$field );
        }

        $ns = new LookupDomain( 'array', $data );
     }
}
