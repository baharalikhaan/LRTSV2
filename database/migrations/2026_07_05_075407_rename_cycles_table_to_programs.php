<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameCyclesTableToPrograms extends Migration
{
    public function up()
    {
        // programs table already exists (was renamed manually/earlier)
        // This migration is kept for record-keeping only.
        if (Schema::hasTable('cycles')) {
            Schema::rename('cycles', 'programs');
        }
    }

    public function down()
    {
        // Do not rename back; this is a one-way rename
    }
}
