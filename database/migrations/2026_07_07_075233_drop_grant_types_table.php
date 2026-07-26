<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ── Step 1: Transfer data from grant_types to grants ────────────────────
        // Update existing grants with description, funding_agency, and category from grant_types
        // matched by grant_code
        $grantTypes = DB::table('grant_types')->get();

        foreach ($grantTypes as $gt) {
            $updateData = [];

            if (!is_null($gt->description)) {
                $updateData['description'] = $gt->description;
            }

            if (!is_null($gt->funding_agency)) {
                $updateData['funding_agency'] = $gt->funding_agency;
            }

            // Map category values: grant_types uses 'Regular'/'Student', grants uses 'regular'/'student'
            if (!is_null($gt->category)) {
                $updateData['category'] = strtolower($gt->category);
            }

            if (!is_null($gt->isactive)) {
                $updateData['is_active'] = (bool) $gt->isactive;
            }

            // Also merge in grant_title as grant_name if the grant record's grant_name is empty or null
            if (!is_null($gt->grant_title)) {
                $existingGrant = DB::table('grants')
                    ->where('grant_code', $gt->grant_code)
                    ->first();

                if ($existingGrant && (empty($existingGrant->grant_name) || is_null($existingGrant->grant_name))) {
                    $updateData['grant_name'] = $gt->grant_title;
                }
            }

            if (!empty($updateData)) {
                DB::table('grants')
                    ->where('grant_code', $gt->grant_code)
                    ->update($updateData);
            }
        }

        // ── Step 2: Drop FK and column from programs ──────────────────────────
        // Use raw SQL to avoid errors if FK doesn't exist or has different name
        try {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropForeign(['grant_type_id']);
            });
        } catch (\Exception $e) {
            // FK may not exist, continue
        }

        try {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropIndex(['grant_type_id']);
            });
        } catch (\Exception $e) {
            // Index may not exist, continue
        }

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('grant_type_id');
        });

        // ── Step 3: Drop the grant_types table ────────────────────────────────
        Schema::dropIfExists('grant_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate grant_types table
        Schema::create('grant_types', function (Blueprint $table) {
            $table->id();
            $table->string('grant_code', 50)->unique();
            $table->enum('category', ['Regular', 'Student'])->default('Regular');
            $table->string('grant_title');
            $table->text('description')->nullable();
            $table->string('funding_agency', 255)->nullable();
            $table->string('duration', 100)->nullable();
            $table->boolean('isactive')->default(true);
            $table->timestamps();
        });

        // Re-populate grant_types from grants data
        $grants = DB::table('grants')->get();
        foreach ($grants as $g) {
            $existing = DB::table('grant_types')->where('grant_code', $g->grant_code)->first();
            if (!$existing) {
                DB::table('grant_types')->insert([
                    'grant_code' => $g->grant_code,
                    'grant_title' => $g->grant_name,
                    'category' => ucfirst($g->category ?? 'regular'),
                    'description' => $g->description,
                    'funding_agency' => $g->funding_agency,
                    'isactive' => $g->is_active,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Re-add grant_type_id FK to programs
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('grant_type_id')->nullable()->constrained('grant_types')->nullOnDelete();
        });
    }
};
