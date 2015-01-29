<?php
namespace Nitronet\Fwk\CMF\Events;

use Fwk\Events\Event;
use Nitronet\Fwk\CMF\CmfService;
use Nitronet\Fwk\CMF\Providers\PagesProvider;

class BeforePageEvent extends Event
{
    const EVENT_NAME = 'beforePage';
    
    public function __construct($pageName, CmfService $service,
        PagesProvider $provider, array &$config, array &$params
    ) {
        parent::__construct(self::EVENT_NAME, array(
            'pageName'  => $pageName,
            'service'   => $service,
            'provider'  => $provider,
            'config'    => $config,
            'parameters'    => $params
        ));
    }
    
    /**
     * 
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }
    
    /**
     * 
     * @return CmfService
     */
    public function getService()
    {
        return $this->service;
    }
    
    /**
     * 
     * @return PagesProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    /**
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}