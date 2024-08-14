<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CreateContactMapping extends \Illuminate\Database\Migrations\Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        // the only way is up, baby
    }
}
