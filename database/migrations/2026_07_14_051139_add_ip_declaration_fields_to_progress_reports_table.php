<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpDeclarationFieldsToProgressReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->string('has_ip_disclosure')->nullable()->comment('Yes/No — Have any IP Disclosure forms been submitted?');
            $table->string('has_provisional_patent')->nullable()->comment('Yes/No — Have any Provisional Patent been filed?');
            $table->string('has_granted_patent')->nullable()->comment('Yes/No — Have any Patents been granted?');
            $table->string('has_open_source_software')->nullable()->comment('Yes/No — Has Open Source Software been developed?');
            $table->string('has_startup')->nullable()->comment('Yes/No — Has a Start-Up been created?');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropColumn([
                'has_ip_disclosure',
                'has_provisional_patent',
                'has_granted_patent',
                'has_open_source_software',
                'has_startup',
            ]);
        });
    }
}
