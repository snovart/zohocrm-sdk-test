<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create crm_leads table mapped to Zoho Leads module.
     * Notes:
     * - Keep a pragmatic set of commonly used fields.
     * - raw_payload stores the original SDK/Zoho record for auditing and remapping.
     */
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();                                      // local PK
            $table->string('zoho_id', 50)->unique();           // Zoho record id (unique)
            $table->string('owner_id', 50)->nullable();        // Zoho owner id

            // Identity
            $table->string('first_name', 191)->nullable();
            $table->string('last_name', 191)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('company', 255)->nullable();

            // Contacts
            $table->string('email', 191)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('website', 255)->nullable();

            // Classification
            $table->string('lead_source', 191)->nullable();
            $table->string('lead_status', 191)->nullable();
            $table->string('industry', 191)->nullable();
            $table->string('rating', 50)->nullable();

            // Money-related
            $table->decimal('annual_revenue', 15, 2)->nullable();

            // Address (flat for simplicity)
            $table->string('street', 255)->nullable();
            $table->string('city', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('zip', 50)->nullable();
            $table->string('country', 191)->nullable();

            // Misc
            $table->text('description')->nullable();

            // Zoho timestamps (as returned by API)
            $table->timestamp('zoho_created_at')->nullable();
            $table->timestamp('zoho_modified_at')->nullable();

            // Raw payload for traceability
            $table->json('raw_payload')->nullable();

            // Local timestamps
            $table->timestamps();

            // Optional indexes for frequent filters
            $table->index(['lead_status']);
            $table->index(['lead_source']);
            $table->index(['email']);
        });
    }

    /**
     * Drop crm_leads table.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
