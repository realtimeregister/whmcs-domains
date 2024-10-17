<?php

namespace RealtimeRegisterDomains\Hooks;

use RealtimeRegisterDomains\Actions\Domains\SmartyTrait;
use RealtimeRegisterDomains\App;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Enums\ScriptLocationType;
use RealtimeRegisterDomains\Models\Whmcs\Registrars;
use RealtimeRegisterDomains\Services\MetadataService;
use RealtimeRegister\Exceptions\BadRequestException;

class CustomHandles extends Hook
{
    use SmartyTrait;
    use CustomHandlesTrait;

    public function __invoke(DataObject $vars): bool | string
    {
        if (in_array('Configure Custom Client Fields', $vars->get('admin_perms'))) {
            // check if post, handle it, or add script & render template
            if ($_POST['action'] === 'propertiesMutate') {
                $nonExistingHandles = self::checkHandleExists($_POST['prop']);
                if (!empty($nonExistingHandles)) {
                    echo json_encode(['result' => 'error', 'handles' => $nonExistingHandles]);
                    die;
                }

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

    private static function checkHandleExists(array $customHandles): array
    {
        $handles = array_reduce(
            array_values($customHandles),
            function ($acc, $contactHandles) {
                if ($contactHandles['techContacts'] && !in_array($contactHandles['techContacts'], $acc)) {
                    $acc[] = $contactHandles['techContacts'];
                }
                if ($contactHandles['adminContacts'] && !in_array($contactHandles['adminContacts'], $acc)) {
                    $acc[] = $contactHandles['adminContacts'];
                }
                if ($contactHandles['billingContacts'] && !in_array($contactHandles['billingContacts'], $acc)) {
                    $acc[] = $contactHandles['billingContacts'];
                }
                return $acc;
            },
            []
        );

        $nonExistingHandles = [];

        foreach ($handles as $handle) {
            try {
                App::client()->contacts->get(App::registrarConfig()->customerHandle(), $handle);
            } catch (BadRequestException $e) {
                $nonExistingHandles[] = $handle;
            }
        }
        return $nonExistingHandles;
    }
}
