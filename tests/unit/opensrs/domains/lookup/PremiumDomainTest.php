<?php

use OpenSRS\domains\lookup\PremiumDomain;
/**
 * @group lookup
 * @group PremiumDomain
 */
class PremiumDomainTest extends PHPUnit_Framework_TestCase
{
    protected $func = "lookupPremiumDomain";

    protected $validSubmission = array(
        'attributes' => array(
            'searchstring' => '',
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

        $data->attributes->searchstring = 'phptest' . time() . '.com';

        $ns = new PremiumDomain( 'array', $data );

        $this->assertTrue( $ns instanceof PremiumDomain );
    }

    /**
     * Data Provider for Invalid Submission test
     */
    function submissionFields() {
        return array(
            'missing searchstring' => array('searchstring'),
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

        $data->attributes->searchstring = 'phptest' . time() . '.com';

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

        $ns = new PremiumDomain( 'array', $data );
     }
}
