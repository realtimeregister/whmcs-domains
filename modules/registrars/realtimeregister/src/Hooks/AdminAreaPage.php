<?php

namespace RealtimeRegisterDomains\Hooks;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;
use RealtimeRegisterDomains\Models\RealtimeRegister\InactiveDomains;

class AdminAreaPage extends Hook
{
    /**
     * This is a dirty hack to be able to install/update database changes, because don't have a hook for that kind of
     * things if we're a registry plugin, so we must do it here..
     *
     * @param  DataObject $vars
     * @return void
     */
    public function __invoke(DataObject $vars)
    {
        // TODO fix the code in https://gist.github.com/jaceju/cc53d2fbab6e828f69b2a3b7e267d1ed
        if (!Capsule::schema()->hasTable(ContactMapping::TABLE_NAME)) {
            Capsule::schema()->create(
                ContactMapping::TABLE_NAME,
                function (Blueprint $table) {
                    $table->integer('userid');
                    $table->integer('contactid');
                    $table->char('handle', 40);
                    $table->boolean('org_allowed');
                    $table->unique(
                        ['userid', 'contactid', 'org_allowed'],
                        'mod_realtimeregister_contact_mapping_unique_contact'
                    );
                    $table->unique('handle', 'mod_realtimeregister_contact_mapping_unique_handle');
                }
            );
        }

        if (!Capsule::schema()->hasTable(InactiveDomains::TABLE_NAME)) {
            Capsule::schema()->create(
                InactiveDomains::TABLE_NAME,
                function (Blueprint $table) {
                    $table->integer('id', true);
                    $table->string('domain_name', 255);
                    $table->dateTime('since');
                    $table->unique('domain_name');
                }
            );
        }

        if (file_exists(__DIR__ . '/../../../../addons/realtimeregister_tools/realtimeregister_tools.php')) {
            /*
             * It seems our old accompanying module is still on this system, it should be removed, but at a minimum,
             * it should be deactivated, which we do here
             */
            $moduleHookList = Capsule::table('tblconfiguration')->where('setting', '=', 'AddonModulesHooks')->first();
            if ($moduleHookList) {
                $moduleHooks = explode(',', $moduleHookList->value);

                foreach ($moduleHooks as $key => $module) {
                    if ($module === 'realtimeregister_tools') {
                        unset($moduleHooks[$key]);
                        break;
                    }
                }
                Capsule::table('tblconfiguration')
                    ->where('setting', '=', 'AddonModulesHooks')
                    ->update(['value' => implode(',', $moduleHooks)]);
            }

            $moduleAddonList = Capsule::table('tblconfiguration')->where('setting', '=', 'ActiveAddonModules')->first();
            if ($moduleAddonList) {
                $moduleHooks = explode(',', $moduleAddonList->value);

                foreach ($moduleHooks as $key => $module) {
                    if ($module === 'realtimeregister_tools') {
                        unset($moduleHooks[$key]);
                        break;
                    }
                }
                Capsule::table('tblconfiguration')
                    ->where('setting', '=', 'ActiveAddonModules')
                    ->update(['value' => implode(',', $moduleHooks)]);
            }

            $modulePermsList = Capsule::table('tblconfiguration')->where('setting', '=', 'AddonModulesPerms')->first();
            if ($modulePermsList) {
                $config = unserialize($modulePermsList->value);
                foreach ($config as $key => $value) {
                    foreach ($value as $k => $v) {
                        if ($k == 'realtimeregister_tools') {
                            unset($config[$key][$k]);
                        }
                    }
                }
                Capsule::table('tblconfiguration')
                    ->where('setting', '=', 'AddonModulesPerms')
                    ->update(['value' => serialize($config)]);
            }
        }
    }
}
