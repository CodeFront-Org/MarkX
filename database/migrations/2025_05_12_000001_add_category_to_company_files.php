<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToCompanyFiles extends Migration
{
    public function up()
    {
        Schema::table('company_files', function (Blueprint $table) {
            $table->string('category')->nullable()->after('file_type');
        });
    }

    public function down()
    {
        Schema::table('company_files', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
