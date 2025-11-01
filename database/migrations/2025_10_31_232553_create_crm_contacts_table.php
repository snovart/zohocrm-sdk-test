<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_contacts', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('zoho_id')->unique();                 // Zoho record id
            $t->string('owner_id')->nullable();              // Zoho owner id (user)
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('full_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('mobile')->nullable();
            $t->string('status')->nullable();                // e.g. Lead Source/Stage if mapped
            $t->timestamp('zoho_created_at')->nullable();    // Created_Time from Zoho
            $t->timestamp('zoho_modified_at')->nullable();   // Modified_Time from Zoho
            $t->json('raw_payload')->nullable();             // original Zoho fields snapshot
            $t->timestamps();

            $t->index('email');
            $t->index('phone');
            $t->index('zoho_modified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_contacts');
    }
};
