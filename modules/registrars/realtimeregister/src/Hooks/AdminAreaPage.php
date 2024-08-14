<?php

namespace RealtimeRegister\Hooks;

use RealtimeRegister\Entities\DataObject;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use RealtimeRegister\Models\Cache;

class AdminAreaPage extends Hook
{
    /**
     * This is a dirty hack to be able to install/update database changes
     *
     * @param DataObject $vars
     * @return void
     */
    public function __invoke(DataObject $vars)
    {
        // TODO fix the code in https://gist.github.com/jaceju/cc53d2fbab6e828f69b2a3b7e267d1ed
        if (!Capsule::schema()->hasTable(\RealtimeRegister\Models\ContactMapping::TABLE_NAME)) {
            Capsule::schema()->create(\RealtimeRegister\Models\ContactMapping::TABLE_NAME, function (Blueprint $table) {
                $table->integer('userid');
                $table->integer('contactid');
                $table->char('handle', 40);
                $table->boolean('org_allowed');
                $table->unique(
                    ['userid', 'contactid', 'org_allowed'],
                    'mod_realtimeregister_contact_mapping_unique_contact'
                );
                $table->unique('handle', 'mod_realtimeregister_contact_mapping_unique_handle');
            });
        }

        if (!Capsule::schema()->hasTable(Cache::TABLE_NAME)) {
            Capsule::schema()->create(Cache::TABLE_NAME, function ($table) {
                $table->string('key')->unique();
                $table->text('value');
                $table->integer('expiration');
            });
        }
    }
}
