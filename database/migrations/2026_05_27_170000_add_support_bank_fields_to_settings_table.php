<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupportBankFieldsToSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'bank_bin')) {
                $table->string('bank_bin', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('settings', 'bank_number')) {
                $table->string('bank_number', 50)->nullable()->after('bank_bin');
            }
            if (!Schema::hasColumn('settings', 'bank_owner')) {
                $table->string('bank_owner', 150)->nullable()->after('bank_number');
            }
            if (!Schema::hasColumn('settings', 'support_content')) {
                $table->string('support_content', 255)->nullable()->after('bank_owner');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['bank_bin', 'bank_number', 'bank_owner', 'support_content'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $dropColumns[] = $col;
                }
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
}
