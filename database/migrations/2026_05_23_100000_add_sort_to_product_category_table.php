<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSortToProductCategoryTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('product_category')) {
            return;
        }

        if (!Schema::hasColumn('product_category', 'sort')) {
            Schema::table('product_category', function (Blueprint $table) {
                $table->unsignedInteger('sort')->default(0)->after('status');
            });
        }

        $rows = DB::table('product_category')->orderBy('id')->pluck('id');
        foreach ($rows as $index => $id) {
            DB::table('product_category')->where('id', $id)->update(['sort' => $index + 1]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('product_category') && Schema::hasColumn('product_category', 'sort')) {
            Schema::table('product_category', function (Blueprint $table) {
                $table->dropColumn('sort');
            });
        }
    }
}
