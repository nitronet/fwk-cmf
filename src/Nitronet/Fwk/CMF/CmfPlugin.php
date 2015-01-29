<?php
namespace Nitronet\Fwk\CMF;

use Fwk\Core\Action\ProxyFactory;
use Fwk\Core\Application;
use Fwk\Core\Components\ResultType\ResultTypeServiceLoadedEvent;
use Fwk\Core\Events\RequestEvent;
use Fwk\Core\Plugin;
use Fwk\Di\ClassDefinition;
use Fwk\Di\Container;
use Nitronet\Fwk\CMF\Listeners\CommandsListener;

class CmfPlugin implements Plugin
{
    protected $config = array();

    protected $providers;
    protected $routesProviders;

    public function __construct(array $config = array(), array $providers = array(), $routesProviders = array())
    {
        $this->config = array_merge(array(
            'serviceName'       => 'cmf',
            'config'            => ':packageDir/site/site.yml',
            'path'              => ':packageDir/site',
            'rewriterService'   => 'urlRewriter',
            'consoleService'    => 'console'
        ), $config);

        $this->providers = $providers;
        $this->routesProviders = $routesProviders;
    }

    /**
     * Adds Plugin's services to the existing Container
     *
     * @param Container $container App's Services Container
     *
     * @return void
     */
    public function loadServices(Container $container)
    {
        // service
        $defService = new ClassDefinition('Nitronet\Fwk\CMF\CmfService', array(
            $this->cfg('config', ':packageDir/site/site.yml'),
            $this->providers,
            $this->routesProviders
        ));

        $container->set($this->cfg('serviceName', 'cmf'), $defService);

        unset($this->providers, $this->routesProviders);
    }

    /**
     * Adds Actions and Listeners to the Application
     *
     * @param Application $app The running Application
     *
     * @return void
     */
    public function load(Application $app)
    {
        $app->register('PageView', ProxyFactory::factory('Nitronet\Fwk\CMF\Controllers\PageView:show'));

        $console = $this->cfg('consoleService', false);
        if ($console) {
            $app->addListener(new CommandsListener($this->cfg('consoleService', 'console'), $this->cfg('serviceName', 'cmf')));
        }
    }

    public function onRequest(RequestEvent $event)
    {
        $urlRewriter    = $event->getApplication()
            ->getServices()
            ->get($this->cfg('rewriterService', 'urlRewriter'));

        $cmf = $event->getApplication()
            ->getServices()
            ->get($this->cfg('serviceName', 'cmf'));

        $routes = $cmf->getRoutes();
        $urlRewriter->addRoutes($routes);

        $cmf->initServices($event->getApplication()->getServices());
    }

    public function onResultTypeServiceLoaded(ResultTypeServiceLoadedEvent $event)
    {
        $rts = $event->getResultTypeService();
        $rts->register('CmfPageView', 'cmf:page', 'twig', array('file' => ':template'));
    }

    protected function cfg($key, $default = false)
    {
        return (array_key_exists($key, $this->config) ? $this->config[$key] : $default);
    }
}