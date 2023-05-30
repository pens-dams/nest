<?php

namespace Tests\Feature;

use App\Supports\Geo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GeoTest extends TestCase
{
    public function test_geo_translation_is_reversible(): void
    {
        $this->markTestIncomplete();

        $lla = [
            "lat" => -7.279214756766409,
            "lng" => 112.79290837299482,
            "alt" => 20,
        ];

        $xyz = [
            "x" => -128.3477562015314,
            "y" => -370.36521029142096,
            "z" => 20,
        ];

        $this->assertEquals($lla, Geo::Vector3RelativeToLatLng($xyz));
        $this->assertEquals($xyz, Geo::latLngToVector3Relative($lla));
    }
}
