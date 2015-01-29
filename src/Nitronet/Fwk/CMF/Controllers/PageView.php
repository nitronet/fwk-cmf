<?php
namespace Nitronet\Fwk\CMF\Controllers;;

use Fwk\Core\Action\Result;
use Fwk\Core\Action\Controller;
use Fwk\Core\Preparable;
use Nitronet\Fwk\CMF\DataSourceFactory;
use Nitronet\Fwk\CMF\Exceptions\PageNotFound;

class PageView extends Controller implements Preparable
{
    public $page;
    
    protected $config;
   
    protected $datasources = array();
    
    public function prepare()
    {
        $service    = $this->getCmfService();
        $cfg        = $service->getSiteConfig();
        
        if (empty($this->page)) {
            $this->page = $cfg['homepage'];
        }
    }
    
    public function show()
    {
        if (empty($this->page) || !$this->getCmfService()->hasPage($this->page)) {
            throw new PageNotFound($this->page);
        }
        
        try {
            $this->config = $this->getPageConfig();
            if ($this->config['active'] !== true) {
                throw new PageNotFound($this->page);
            }
        }
        catch(PageNotFound $exp) {
            throw $exp;
        }
        catch(\Exception $exp) {
            echo $exp;
            return Result::ERROR;
        }
        
        try {
            $this->datasources = $this->loadDataSources();
        } catch (\Exception $exp) {
            echo $exp;
            return Result::ERROR;
        }
        
        return $this->getCmfService()->render($this->page, $this->getServices(), $this->getContext(), array_merge(array(
            '_helper'   => $this->getServices()->get('viewHelper'),
            'query'     => $this->getContext()->getRequest()->query->all(),
            'request'   => $this->getContext()->getRequest()->request->all()
        ), $this->datasources));
    }
    
    protected function getPageConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }
        
        $this->config = $this->getCmfService()->getPageConfig($this->page);
        
        return $this->config;
    }
    
    /**
     * 
     * @return \Nitronet\Fwk\CMF\CmsService
     */
    protected function getCmfService()
    {
        return $this->getServices()->get('cmf');
    }
    
    /**
     * 
     * @return array
     */
    protected function loadDataSources()
    {
        if (!isset($this->config['datasources'])) {
            return;
        }
        
        $this->getCmfService()->initClassLoader();
        $container  = $this->getServices();
        
        $factory    = new DataSourceFactory($container);
        $factory->load($this->config['datasources']);
        
        return $factory->factoryAll();
    }
}