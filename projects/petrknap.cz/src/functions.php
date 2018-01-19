<?php

namespace PetrKnapCz;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Cookie;
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
    $token = $request->get('token');
    if ($token) {
        $response = (new Response());
        $response->headers->setCookie(new Cookie('access_token', $token, new \DateTime("+1 day")));
        $response->sendHeaders();
    } else {
        $token = $request->cookies->get('access_token');
    }

    if (container()->getParameter('access_token') != $token) {
        (new Response(null, Response::HTTP_FORBIDDEN))->send();
        die;
    }
}

function done()
{
    (new Response('ok', Response::HTTP_OK, [
        'Content-Type' => 'text/plain; charset=utf-8'
    ]))->send();
}
