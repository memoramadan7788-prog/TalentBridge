<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The admin organization/engagement endpoints (AdminCompanyResource, EngagementController,
 * CompanyManagementController@storeOrganization) distinguish a plain company from a recruiting
 * agency through companies.organization_type, but no earlier migration ever created that column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('companies', 'organization_type')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('organization_type', 20)->default('company')->after('name')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('companies', 'organization_type')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['organization_type']);
            $table->dropColumn('organization_type');
        });
    }
};
