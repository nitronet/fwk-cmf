<?php
namespace Nitronet\Fwk\CMF\Events;

use Fwk\Events\Event;
use Nitronet\Fwk\CMF\CmfService;
use Nitronet\Fwk\CMF\Providers\PagesProvider;
use Symfony\Component\HttpFoundation\Response;

class AfterPageEvent extends Event
{
    const EVENT_NAME = 'afterPage';
    
    public function __construct($pageName, CmfService $service,
        PagesProvider $provider, Response $response, array $params
    ) {
        parent::__construct(self::EVENT_NAME, array(
            'pageName'  => $pageName,
            'service'   => $service,
            'provider'  => $provider,
            'response'  => $response,
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
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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