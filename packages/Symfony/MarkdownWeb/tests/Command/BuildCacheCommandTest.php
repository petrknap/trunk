<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use const PetrKnap\Symfony\MarkdownWeb\BUILD_CACHE_COMMAND;
use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use PetrKnap\Symfony\MarkdownWeb\Command\BuildCacheCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BuildCacheCommandTest extends MarkdownWebTestCase
{
    public function testCommandIsRegistered()
    {
        $command = (new Application($this->getKernel()))->find(BUILD_CACHE_COMMAND);

        $this->assertInstanceOf(BuildCacheCommand::class, $command);

        return $command;
    }

    /**
     * @depends testCommandIsRegistered
     * @param BuildCacheCommand $command
     */
    public function testItWorksWithEnabledCaching(BuildCacheCommand $command)
    {
        $this->itWorks($command, true, '[OK] Pages for the "test" environment (debug=true) was successfully cached.');
    }

    /**
     * @depends testCommandIsRegistered
     * @param BuildCacheCommand $command
     */
    public function testItWorksWithDisabledCaching(BuildCacheCommand $command)
    {
        $this->itWorks($command, false, '[WARNING] Page caching is disabled in configuration.');
    }

    private function itWorks(BuildCacheCommand $command, $cached, $expectedMessage)
    {
        $input = new ArgvInput([]);
        $output = new BufferedOutput();
        $command->setContainer($this->getContainer());
        $config = $this->getContainer()->get(CONFIG);
        $config['cached'] = $cached;

        $this->invoke([$command, 'execute'], [$input, $output]);
        $this->assertContains($expectedMessage, $output->fetch());
    }
}
