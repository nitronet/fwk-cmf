<?php
namespace Nitronet\Fwk\CMF\Providers;

use Fwk\Core\Context;
use Fwk\Di\Container;

interface PagesProvider
{
    public function has($pageName, array $config);
    
    public function getConfig($pageName, array $config);

    public function render($pageName, Container $services, Context $context, array $config, array $params = array());
    
    public function getLastModified($pageName, Context $context, array $config);
}