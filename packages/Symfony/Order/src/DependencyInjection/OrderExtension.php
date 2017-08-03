<?php

namespace PetrKnap\Symfony\Order\DependencyInjection;

use PetrKnap\Symfony\Order\Service\OrderService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OrderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new OrderConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setDefinition(OrderConfiguration::class, new Definition(OrderConfiguration::class))
            ->setArguments([
                $config,
            ]);

        $container->setDefinition(OrderService::class, new Definition(OrderService::class))
            ->setArguments([
                $config['cookie']['name'],
                $config['cookie']['expire_after'],
                $config['item_provider'],
                $config['customer_provider'],
            ]);
    }
}
