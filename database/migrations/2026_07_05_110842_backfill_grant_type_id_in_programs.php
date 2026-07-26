<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillGrantTypeIdInPrograms extends Migration
{
    public function up()
    {
        // First, ensure missing Grant Types exist (CG, NRPU, SRGP, TDF)
        $missingTypes = [
            [
                'grant_code'     => 'CG',
                'category'       => 'Regular',
                'grant_title'    => 'Conference Grant',
                'description'    => 'Funding support for attending and presenting at academic conferences.',
                'funding_agency' => 'Qatar University',
                'duration'       => '1 year',
                'isactive'       => true,
            ],
            [
                'grant_code'     => 'NRPU',
                'category'       => 'Regular',
                'grant_title'    => 'National Research Program for Universities',
                'description'    => 'National-level research funding program for Qatari universities.',
                'funding_agency' => 'Qatar Research Development and Innovation Council',
                'duration'       => '3 years',
                'isactive'       => true,
            ],
            [
                'grant_code'     => 'SRGP',
                'category'       => 'Regular',
                'grant_title'    => 'Startup Research Grant Program',
                'description'    => 'Seed funding for new research initiatives and early-stage projects.',
                'funding_agency' => 'Qatar University',
                'duration'       => '2 years',
                'isactive'       => true,
            ],
            [
                'grant_code'     => 'TDF',
                'category'       => 'Regular',
                'grant_title'    => 'Technology Development Fund',
                'description'    => 'Funding for technology development and commercialization projects.',
                'funding_agency' => 'Qatar Research Development and Innovation Council',
                'duration'       => '2 years',
                'isactive'       => true,
            ],
        ];

        foreach ($missingTypes as $type) {
            $exists = DB::table('grant_types')->where('grant_code', $type['grant_code'])->exists();
            if (!$exists) {
                DB::table('grant_types')->insert($type);
            }
        }

        // Backfill grant_type_id in programs by matching grant_code between grants and grant_types
        DB::statement("
            UPDATE programs p
            JOIN grants g ON p.grant_id = g.id
            JOIN grant_types gt ON g.grant_code = gt.grant_code
            SET p.grant_type_id = gt.id
            WHERE p.grant_type_id IS NULL
        ");
    }

    public function down()
    {
        // Reverse: set grant_type_id back to NULL for programs that had it auto-populated
        DB::statement("
            UPDATE programs p
            JOIN grants g ON p.grant_id = g.id
            JOIN grant_types gt ON g.grant_code = gt.grant_code
            SET p.grant_type_id = NULL
            WHERE p.grant_type_id IS NOT NULL
        ");

        // Remove the grant types that were created by this migration
        DB::table('grant_types')->whereIn('grant_code', ['CG', 'NRPU', 'SRGP', 'TDF'])->delete();
    }
}
