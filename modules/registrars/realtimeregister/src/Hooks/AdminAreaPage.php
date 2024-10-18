<?php

namespace RealtimeRegisterDomains\Hooks;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use RealtimeRegisterDomains\Entities\DataObject;
use RealtimeRegisterDomains\Models\RealtimeRegister\Cache;
use RealtimeRegisterDomains\Models\RealtimeRegister\ContactMapping;

class AdminAreaPage extends Hook
{
    /**
     * This is a dirty hack to be able to install/update database changes
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
    }
}
