<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sync_states', function (Blueprint $t) {
            $t->id();
            $t->string('module')->unique();          // Module name, e.g. Contacts, Deals, Leads
            $t->timestamp('last_success_at')->nullable(); // Last successful sync timestamp
            $t->string('last_cursor')->nullable();   // Cursor or delta token for incremental sync
            $t->json('stats_json')->nullable();      // Statistics or service metadata
            $t->timestamp('last_error_at')->nullable(); // Last failed sync timestamp
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_states');
    }
};
