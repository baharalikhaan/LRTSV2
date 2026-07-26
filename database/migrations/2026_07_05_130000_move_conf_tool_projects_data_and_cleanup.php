<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveConfToolProjectsDataAndCleanup extends Migration
{
    public function up()
    {
        // 1. Get constraint name for conf_tool_project_id FK
        $fkName = null;
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = $sm->listTableForeignKeys('projects');
        foreach ($foreignKeys as $fk) {
            if (in_array('conf_tool_project_id', $fk->getLocalColumns())) {
                $fkName = $fk->getName();
                break;
            }
        }

        // 1b. Make user_id nullable first since conf_tool projects may have no matching user
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // 2. Copy data from conf_tool_projects to projects
        $confProjects = DB::table('conf_tool_projects')->get();
        foreach ($confProjects as $cp) {
            $existing = DB::table('projects')
                ->where('old_project_id', $cp->old_project_id)
                ->first();

            if ($existing) {
                DB::table('projects')->where('id', $existing->id)->update([
                    'email' => $cp->email,
                    'author' => $cp->author,
                    'pillars_text' => $cp->pillars,
                    'tags_text' => $cp->tags,
                    'proposal_filename' => $cp->proposal_filename,
                    'added' => $cp->added,
                    'updated_at' => now(),
                ]);
            } else {
                $lpiUser = DB::table('users')->where('email', $cp->email)->first();
                DB::table('projects')->insert([
                    'old_project_id' => $cp->old_project_id,
                    'title' => $cp->title,
                    'email' => $cp->email,
                    'author' => $cp->author,
                    'user_id' => $lpiUser ? $lpiUser->id : null,
                    'cycle_id' => $cp->cycle_id,
                    'grant_type' => $cp->grant_type,
                    'pillars_text' => $cp->pillars,
                    'tags_text' => $cp->tags,
                    'proposal_filename' => $cp->proposal_filename,
                    'added' => $cp->added,
                    'status' => $cp->added ? 'active' : 'pending',
                    'created_at' => $cp->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Drop FK and column
        Schema::table('projects', function (Blueprint $table) use ($fkName) {
            if ($fkName) {
                $table->dropForeign($fkName);
            }
            $table->dropColumn('conf_tool_project_id');
        });

        // 4. Drop conf_tool_projects table
        Schema::dropIfExists('conf_tool_projects');
    }

    public function down()
    {
        // Recreate conf_tool_projects table
        Schema::create('conf_tool_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->nullOnDelete();
            $table->string('old_project_id', 100)->nullable()->unique();
            $table->string('title', 500);
            $table->string('email', 255)->nullable();
            $table->string('author', 255)->nullable();
            $table->string('grant_type', 100)->nullable();
            $table->text('pillars')->nullable();
            $table->text('tags')->nullable();
            $table->string('proposal_filename', 255)->nullable();
            $table->boolean('added')->default(false);
            $table->timestamps();
        });

        // Re-add conf_tool_project_id to projects
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('conf_tool_project_id')->nullable()->unique()->constrained('conf_tool_projects')->nullOnDelete();
        });

        // Remove data that was migrated
        DB::table('projects')->whereNotNull('old_project_id')->update([
            'email' => null,
            'author' => null,
            'pillars_text' => null,
            'tags_text' => null,
            'proposal_filename' => null,
            'added' => false,
        ]);
    }
}
