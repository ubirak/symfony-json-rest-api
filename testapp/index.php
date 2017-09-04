<?php

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

// require Composer's autoloader
require __DIR__.'/../vendor/autoload.php';

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', ['secret' => 'S0ME_SECRET']);
        $loader->load(__DIR__.'/config.yml');
        $loader->load(__DIR__.'/services.xml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        // kernel is a service that points to this class
        // optional 3rd argument is the route name
        $routes->add('/echo', 'kernel:echoAction')->setDefault('_jsonSchema', ['request' => 'schema.json']);
        $routes->add('/exception', 'kernel:exceptionAction');
    }

    public function echoAction(Request $request)
    {
        return new JsonResponse($request->request->all() + $request->attributes->all());
    }

    public function exceptionAction(Request $request)
    {
        if ($request->query->has('supported')) {
            throw new \Rezzza\SymfonyRestApiJson\Tests\Fixtures\MyOtherException('This is my app message');
        }

        throw new \RuntimeException('Something wrong');
    }
}

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
