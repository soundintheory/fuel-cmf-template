<?php

namespace Doctrine\Fuel\Spatial;
 
/**
 * Point object for spatial mapping
 */
class Point
{
    public $latitude;
    public $longitude;
 
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
 
    public function __toString()
    {
        //Output from this is used with POINT_STR in DQL so must be in specific format
        return sprintf('POINT(%f %f)', $this->latitude, $this->longitude);
    }
}