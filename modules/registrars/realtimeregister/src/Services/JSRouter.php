<?php

namespace RealtimeRegisterDomains\Services;

use RealtimeRegisterDomains\Entities\DataObject;

class JSRouter
{
    public array $json = [];

    /**
     * Simple routing for WHMCS pages.
     */
    public function __construct($vars)
    {
        // Routes per filename.
        if (isset($vars['filename'])) {
            $method = 'file_' . $vars['filename'];
            if (method_exists($this, $method)) {
                // Call file_<filename> method.
                call_user_func([$this, $method], $vars);
            }
        }
    }

    /**
     * Set JS controller.
     */
    private function setController($controller): void
    {
        $this->json['controller'][] = $controller;
    }

    /**
     * file_<filename> callback.
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function file_clientsdomains(DataObject $params): void
    {
        $this->domainControllers($params);
    }

    /**
     * file_<filename> callback.
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function file_clientarea(DataObject $params): void
    {
        $this->domainControllers($params);
    }

    /**
     * Add no lock support and renew controller.
     */
    private function domainControllers(DataObject $params): void
    {
        // Skip controller in case no TLD is set.
        if (empty($params['domain'])) {
            return;
        }
        try {
            $metaData = (new MetadataService(MetadataService::getTld($params['domain'])))->getMetadata();
            if (in_array(12, $metaData->renewDomainPeriods) && count($metaData->renewDomainPeriods) === 1) {
                $this->setController('removeRenewButton');
            }
        } catch (\Exception $e) {
            LogService::logError($e);
        }
    }
}
