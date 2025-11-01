<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create crm_accounts table.
     * Notes:
     * - Keep a stable minimal set of columns we can reliably map from Zoho Accounts.
     * - raw_payload keeps the original SDK/Zoho record for debugging and future remapping.
     */
    public function up(): void
    {
        Schema::create('crm_accounts', function (Blueprint $table) {
            $table->id();                                      // local PK
            $table->string('zoho_id', 50)->index();            // Zoho record id
            $table->string('owner_id', 50)->nullable();        // Zoho owner id

            $table->string('account_name', 255)->nullable();   // Accounts.Account_Name
            $table->string('phone', 50)->nullable();           // Accounts.Phone
            $table->string('website', 255)->nullable();        // Accounts.Website
            $table->string('industry', 191)->nullable();       // Accounts.Industry
            $table->string('rating', 50)->nullable();          // Accounts.Rating
            $table->string('account_type', 100)->nullable();   // Accounts.Account_Type
            $table->text('description')->nullable();           // Accounts.Description
            $table->string('status', 100)->nullable();         // Optional status-like field (if used)

            $table->timestamp('zoho_created_at')->nullable();  // record create time in Zoho
            $table->timestamp('zoho_modified_at')->nullable(); // record modified time in Zoho

            $table->json('raw_payload')->nullable();           // original payload for traceability

            $table->timestamps();                              // created_at / updated_at (local)
            $table->unique('zoho_id');                         // guard duplicates by zoho id
        });
    }

    /**
     * Drop crm_accounts table.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_accounts');
    }
};
