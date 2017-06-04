<?php

namespace PetrKnap\Symfony\MarkdownWeb;

use PetrKnap\Symfony\MarkdownWeb\DependencyInjection\MarkdownWebExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MarkdownWebBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new MarkdownWebExtension();
    }
}
