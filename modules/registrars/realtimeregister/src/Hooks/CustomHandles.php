<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Actions\Domains\SmartyTrait;
use RealtimeRegister\App;
use RealtimeRegister\Entities\DataObject;
use RealtimeRegister\Enums\ScriptLocationType;
use RealtimeRegister\Models\Whmcs\Registrars;
use RealtimeRegister\Services\MetadataService;

class CustomHandles extends Hook
{
    use SmartyTrait;
    use CustomHandlesTrait;

    public function __invoke(DataObject $vars): bool | string
    {
        if (in_array('Configure Custom Client Fields', $vars->get('admin_perms'))) {
            // check if post, handle it, or add script & render template
            if ($_POST['action'] === 'propertiesMutate') {
                Registrars::updateOrCreate(
                    [
                        'registrar' => 'realtimeregister',
                        'setting' => 'customHandles',
                    ],
                    [
                        'value' => Encrypt(htmlentities(json_encode($_POST['prop']))),
                    ]
                );
                echo json_encode(['result' => 'success', 'message' => 'properties have been saved']);
                die;
            } elseif ($_POST['action'] === 'fetchProperties') {
                $metadata = $this->fetchPropertiesFromRealtimeRegister();

                $customHandles = $this->getCustomHandles();

                $data = [];

                foreach ($metadata as $metadatum) {
                    /** @noinspection PhpUndefinedFunctionInspection */
                    $value = null;
                    if ($customHandles) {
                        if (array_key_exists($metadatum['provider'], $customHandles)) {
                            if (array_key_exists($metadatum['for'], $customHandles[$metadatum['provider']])) {
                                if ($customHandles[$metadatum['provider']][$metadatum['for']] !== '') {
                                    $value = $customHandles[$metadatum['provider']][$metadatum['for']];
                                }
                            }
                        }
                    }
                    $data[] = [
                        'provider' => $metadatum['provider'],
                        'value' => $value,
                        'forType' => $metadatum['for'],
                    ];
                }
                echo json_encode($data);
                die;
            } else {
                // Base of form is rendered here
                App::assets()->addStyle('style.css');
                App::assets()->addScript('customHandles.js', ScriptLocationType::Footer);

                return $this->render(
                    __DIR__ . '/../Assets/Tpl/admin/custom_handles.tpl',
                    [
                        'vars' => $vars,
                    ]
                );
            }
        }
        return '';
    }

    private function fetchPropertiesFromRealtimeRegister(): array
    {
        $metaData = MetadataService::getBulkData();

        $specialMetadata = [];
        foreach ($metaData as $data) {
            $sp = [];

            $types = ['adminContacts', 'billingContacts', 'techContacts'];

            foreach ($types as $type) {
                if (
                    array_key_exists($type, $data['metadata'])
                    &&
                    (
                        array_key_exists('organizationRequired', $data['metadata'][$type])
                        && ($data['metadata'][$type]['organizationRequired'] === true)
                    )
                ) {
                    $sp[$type] = true;
                }

                if (
                    array_key_exists($type, $data['metadata'])
                    &&
                    (
                        array_key_exists('organizationAllowed', $data['metadata'][$type])
                        && $data['metadata'][$type]['organizationAllowed'] === false
                    )
                ) {
                    $sp[$type] = true;
                }
            }

            if ($sp) {
                foreach ($sp as $type => $value) {
                    $specialMetadata[] = [
                        'for' => $type,
                        'provider' => $data['provider'],
                        'metadata' => $data['metadata']
                    ];
                }
            }
        }
        return $specialMetadata;
    }
}
