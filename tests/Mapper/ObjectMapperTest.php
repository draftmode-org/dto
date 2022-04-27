<?php
namespace Terrazza\Component\Dto\Tests;
use PHPUnit\Framework\TestCase;
use stdClass;
use Terrazza\Component\Dto\Mapper\ObjectMapper;
use Terrazza\Component\Dto\Tests\_Mocks\LoggerMock;

class ObjectMapperTest extends TestCase {
    private function getMapper(bool $log=false) {
        return new ObjectMapper(LoggerMock::get($log));
    }

    function testSimple() {
        $source 					                = new stdClass();
        $source->customer 			                = new stdClass();
        $source->customer->first 	                = $cFirstName = "Max";
        $source->customer->last 	                = $cLastName = "Mustermann";
        $source->address 			                = new stdClass();
        $source->address->street 	                = $aStreet = "street";
        $source->media 				                = [
            (object)['type' => 'logo', 'uri' => $mLogoUri = 'logoImg'],
            (object)['type' => 'location', 'uri' => $mLocationUri = 'locationImg'],
        ];
        $source->media_logo			                = $mLogo = 'logoImg';

        $mapToTarget = [
            'customer' 	=> [
                'firstName' => 'customer.first',
                'lastName' 	=> 'customer.last',
                'birthday'  => 'customer.bday'
            ],
            'phone' => [
                'private' => [
                    'mobile'  => 'customer.mobile'
                ]
            ],
            'address'	=> [
                'street'	=> [
                    'name' 	=> 'address.street'
                ]
            ],
            'media'		=> [
                [
                    'type*' => 'logo',
                    'uri' 	=> 'media_logo'
                ]
            ]
        ];

        $mapper 					= $this->getMapper(true);
        $target						= $mapper->map($source, $mapToTarget);
        $this->assertEquals((object)[
            'customer' => (object)[
                'firstName'         => $cFirstName,
                'lastName'          => $cLastName
                //birthday will not be created, not in source
            ],
            //phone will not be created, no content in source
            'address' => (object)[
                'street' => (object)[
                    'name' => $aStreet
                ]
            ],
            'media' => [
                (object)[
                    'type'  => 'logo',
                    'uri'   => $mLogoUri
                ]
            ]
        ], $target);
    }
}