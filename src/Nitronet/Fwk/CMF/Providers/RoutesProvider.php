<?php
namespace Nitronet\Fwk\CMF\Providers;

use Nitronet\Fwk\CMF\CmfService;

interface RoutesProvider
{
    public function getRoutes(CmfService $cms);
}