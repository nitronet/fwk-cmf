<?php
namespace Nitronet\Fwk\CMF\Providers\Routes;

use Nitronet\Fwk\CMF\CmfService;
use Nitronet\Fwk\CMF\Providers\RoutesProvider;
use Fwk\Core\Components\UrlRewriter\Route;
use Fwk\Core\Components\UrlRewriter\RouteParameter;

class ConfigRoutesProvider implements RoutesProvider
{
    public function getRoutes(CmfService $cms)
    {
        $routes = array();
        $cfg    = $cms->getSiteConfig();
        
        if (!isset($cfg['urls']) || !is_array($cfg['urls'])) {
            return array();
        }
        
        foreach ($cfg['urls'] as $uri => $infos) {
            
            $pageName       = (isset($infos['page']) ? $infos['page'] : null);
            $params         = (isset($infos['parameters']) ? $infos['parameters'] : array());
            $parameters     = array(
                new RouteParameter('page', $pageName, null, false, $pageName)
            );

            foreach ($params as $paramName => $data) {
                $parameters[] = new RouteParameter(
                    $paramName, 
                    (isset($data['default']) ? $data['default'] : null), 
                    (isset($data['regex']) ? $data['regex'] : null), 
                    (isset($data['required']) ? $data['required'] : false),
                    (isset($data['value']) ? $data['value'] : false)    
                );
            }

            $route = new Route('PageView', $uri, $parameters);
            $routes[] = $route;
        }
        
        return $routes;
    }
}