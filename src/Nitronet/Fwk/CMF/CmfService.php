<?php
namespace Nitronet\Fwk\CMF;

use Fwk\Di\Container;
use Fwk\Di\Xml\ContainerBuilder;
use Nitronet\Fwk\CMF\Utils\ConfigUtils;
use Symfony\Component\Yaml\Yaml;
use Nitronet\Fwk\CMF\Exceptions\InvalidConfigFile;
use Fwk\Core\Context;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Fwk\Events\Dispatcher;
use Nitronet\Fwk\CMF\Events\BeforePageEvent;
use Nitronet\Fwk\CMF\Events\AfterPageEvent;

class CmfService extends Dispatcher
{
    /**
     *
     * @var array<PagesProvider>
     */
    protected $providers = array();

    protected $routesProviders = array();

    protected $configFile;
    
    private $config;
    private $loaded;
    
    public function __construct($configFile, array $providers, array $routesProviders)
    {
        $this->configFile       = $configFile;
        $this->providers        = $providers;
        $this->routesProviders  = $routesProviders;
    }

    public function getRoutes()
    {
        $routes = array();
        foreach ($this->routesProviders as $rp) {
            $routes += $rp->getRoutes($this);
        }

        return $routes;
    }
    public function hasPage($pageName)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($pageName, $this->getSiteConfig())) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Returns the PageProvider for a given page
     * 
     * @param string $pageName
     * 
     * @return PageProvider
     * @throws Exceptions\PageNotFound
     */
    public function getPageProvider($pageName)
    {
        if (!$this->hasPage($pageName)) {
            throw new Exceptions\PageNotFound($pageName);
        }
        
        foreach ($this->providers as $provider) {
            if ($provider->has($pageName, $this->getSiteConfig())) {
                return $provider;
            }
        }
    }
    
    public function getPageConfig($pageName)
    {
        if (!$this->hasPage($pageName)) {
            throw new Exceptions\PageNotFound($pageName);
        }
        
        return $this->getPageProvider($pageName)
                    ->getConfig($pageName, $this->getSiteConfig());
    }
    
    public function render($pageName, Container $container, Context $context, array $params = array())
    {
        if (!$this->hasPage($pageName)) {
            throw new Exceptions\PageNotFound($pageName);
        }
        
        $provider = $this->getPageProvider($pageName);
        $cfg = $provider->getConfig($pageName, $this->getSiteConfig());
        
        $this->notify(new BeforePageEvent($pageName, $this, $provider, $cfg, $params));
        
        if ($cfg['active'] !== true) {
            throw new Exceptions\PageNotFound($pageName);
        }
        
        if ($cfg['redirect'] != null && !empty($cfg['redirect'])) {
            return new RedirectResponse($cfg['redirect']);
        }
        
        $response = new Response();
        
        $this->handleCacheOptions($pageName, $context, $response, $cfg);
        
        if ($response->isNotModified($context->getRequest())) {
            $this->notify(new AfterPageEvent($pageName, $this, $provider, $response, $params));
            return $response;
        }
        
        $response->setContent($provider->render($pageName, $container, $context, $this->getSiteConfig(), $params));
        $this->notify(new AfterPageEvent($pageName, $this, $provider, $response, $params));
        
        return $response;
    }
    
    protected function handleCacheOptions($pageName, Context $context, Response $response, array $config)
    {
        $response->setExpires(new \DateTime());
        if ($config['cache']['enable'] !== true) {
            return $response;
        }
        
        $provider = $this->getPageProvider($pageName);
        $lastModified = $provider->getLastModified($pageName, $context, $this->getSiteConfig());
        if (null !== $lastModified) {
            $date = new \DateTime();
            $date->setTimestamp($lastModified);
            $response->setLastModified($date);
        }
        
        $response->setMaxAge($config['cache']['lifetime']);
        $response->setSharedMaxAge($config['cache']['lifetime']);
        
        if ($config['cache']['public'] === true) {
            $response->setPublic();
        } else {
            $response->setPrivate();
        }
        
        $response->setETag(md5($pageName . $lastModified));
        
        return $response;
    }
    
    /**
     * Parses and return the site.yml configuration file
     * 
     * @return array
     * @throws InvalidConfigFile
     */
    public function getSiteConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }
        
        try {
            $cfg = Yaml::parse(file_get_contents($this->configFile));
            $this->config = ConfigUtils::merge($cfg);
        } catch(\Exception $exp) {
            throw new InvalidConfigFile($this->configFile, null, $exp);
        }
        
        return $this->config;
    }
    
    public function initClassLoader()
    {
        if ($this->loaded) {
            return;
        }
        
        $cfg = $this->getSiteConfig();
        if (!isset($cfg['namespace']) || !isset($cfg['directories']['sources'])) {
            return;
        }
        
        $loader = new \Composer\Autoload\ClassLoader();
        $utils  = new Utils\PathUtils(dirname($this->configFile));
        $loader->add($cfg['namespace'], $utils->calculate(array($cfg['directories']['sources'])));
        $loader->register(true);
        
        $this->loaded = true;
    }

    public function initServices(Container $container)
    {
        $cfg = $this->getSiteConfig();
        if (!isset($cfg['services']) || !is_array($cfg['services'])) {
            return;
        }

        $builder    = new ContainerBuilder();
        $utils      = new Utils\PathUtils(dirname($this->configFile));
        foreach ($cfg['services'] as $servicesXml) {
            $builder->execute($utils->calculate(array($servicesXml)), $container);
        }
    }
}