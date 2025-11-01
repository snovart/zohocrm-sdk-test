<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create crm_deals table mapped to Zoho Deals module.
     */
    public function up(): void
    {
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();                                      // Local PK
            $table->string('zoho_id', 50)->unique();           // Zoho record ID
            $table->string('owner_id', 50)->nullable();        // Zoho owner

            // Basic fields
            $table->string('deal_name', 255)->nullable();
            $table->string('account_name', 255)->nullable();
            $table->string('contact_name', 255)->nullable();

            // Pipeline info
            $table->string('pipeline', 191)->nullable();
            $table->string('stage', 191)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('probability', 10)->nullable();
            $table->date('closing_date')->nullable();

            // Metadata
            $table->string('type', 191)->nullable();
            $table->string('lead_source', 191)->nullable();
            $table->string('campaign_source', 191)->nullable();

            // Address and description
            $table->text('description')->nullable();

            // Timestamps from Zoho
            $table->timestamp('zoho_created_at')->nullable();
            $table->timestamp('zoho_modified_at')->nullable();

            // Raw Zoho payload
            $table->json('raw_payload')->nullable();

            // Local timestamps
            $table->timestamps();

            // Common indexes
            $table->index(['stage']);
            $table->index(['pipeline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_deals');
    }
};
