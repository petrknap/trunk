<?php

namespace PetrKnap\Php\Profiler\Test;

use PetrKnap\Php\Profiler\Exception\MissingProfilerException;
use PetrKnap\Php\Profiler\Exception\UnsupportedProfilerException;
use PetrKnap\Php\Profiler\Profile;

class ProfileTest extends \PHPUnit_Framework_TestCase
{
    private function getProfileAsArray(Profile $profile)
    {
        return json_decode(json_encode($profile), true);
    }

    public function testJsonSerializable()
    {
        $profile = $this->getProfileAsArray(new Profile());

        $this->assertArrayHasKey(Profile::ABSOLUTE_DURATION, $profile);
        $this->assertArrayHasKey(Profile::DURATION, $profile);
        $this->assertArrayHasKey(Profile::ABSOLUTE_MEMORY_USAGE_CHANGE, $profile);
        $this->assertArrayHasKey(Profile::MEMORY_USAGE_CHANGE, $profile);
    }

    public function testMetaConflict()
    {
        $absoluteDuration = 100;

        $profile = new Profile();
        $profile->meta[Profile::ABSOLUTE_DURATION] = $absoluteDuration;
        $profile->meta["meta_" . Profile::ABSOLUTE_DURATION] = $absoluteDuration;

        $profile = $this->getProfileAsArray($profile);

        $this->assertNotEquals($absoluteDuration, $profile[Profile::ABSOLUTE_DURATION]);
        $this->assertEquals($absoluteDuration, $profile["meta_" . Profile::ABSOLUTE_DURATION]);
    }

    /**
     * @dataProvider dataCanSetProfiler
     * @param string $profilerClassName
     * @param \Exception $expectedException
     */
    public function testCanSetProfiler($profilerClassName, \Exception $expectedException = null)
    {
        if ($expectedException) {
            $this->setExpectedException(get_class($expectedException));
        }
        Profile::setProfiler($profilerClassName);

        call_user_func([$profilerClassName, "enable"]);

        Profile::start();
        $this->assertInstanceOf(get_class(new Profile()), Profile::finish());
    }

    public function dataCanSetProfiler()
    {
        return [
            ["PetrKnap\\Php\\Profiler\\Test\\ProfileTest\\SupportedProfiler", null],
            ["PetrKnap\\Php\\Profiler\\Test\\ProfileTest\\UnsupportedProfiler", new UnsupportedProfilerException()],
            ["PetrKnap\\Php\\Profiler\\Test\\ProfileTest\\MissingProfiler", new MissingProfilerException()]
        ];
    }
}
