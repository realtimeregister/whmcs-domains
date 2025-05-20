<?php

namespace RealtimeRegisterDomains\Widget;

use RealtimeRegisterDomains\Actions\Tlds\GetPricesTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Services\TemplateService;

class PromoWidget extends BaseWidget
{
    use GetPricesTrait;

    protected $title = 'Realtime Register - Promotions';
    protected $description = 'List of promotions of Realtime Register';
    protected $weight = 150;
    protected $columns = 1;
    protected $height = 150;
    protected $cache = false;
    protected $cacheExpiry = 60 * 60 * 24; // One day
    protected $requiredPermission = '';

    public function getData(): array
    {
        try {
            $promotions = App::client()->customers->promoList(App::registrarConfig()->customerHandle());
        } catch (\Exception) {
            return [];
        }

        return ['promotions' => $promotions];
    }

    public function generateOutput($data): string
    {
        return TemplateService::renderTemplate(
            'admin' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'promotions.tpl',
            [
                'promotions' => array_key_exists('promotions', $data) ? $data['promotions'] : [],
            ]
        );
    }
}
