<?php

namespace PetrKnapCz;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

const CONTAINER_TEST_FLAG = 'is_test';

function container(array $parameters = []) {
    static $container;

    if (null === $container) {
        $container = new ContainerBuilder();
        $container->setParameter(CONTAINER_TEST_FLAG, false);
        foreach ($parameters as $name => $value) {
            $container->setParameter($name, $value);
        }
        $container->setParameter('project_dir', realpath(__DIR__ . '/..'));
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('config' . ($container->getParameter(CONTAINER_TEST_FLAG) ? '_test.yml' : '.yml'));
        $loader->load('services.yml');
        $container->set(ContainerInterface::class, $container);
    } elseif (!empty($parameters)) {
        throw new RuntimeException('Can not change running container');
    }

    return $container;
}

function authorize(Request $request) {
    if (container()->getParameter('access_token') != $request->get('token')) {
        (new Response(null, Response::HTTP_FORBIDDEN))->send();
        die;
    }
}
