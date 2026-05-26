<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }
        });

        Schema::table('medicines', function (Blueprint $table) {
            if (! Schema::hasColumn('medicines', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }
        });

        $firstUserId = DB::table('users')->min('id');

        if ($firstUserId) {
            DB::table('students')->whereNull('user_id')->update(['user_id' => $firstUserId]);
            DB::table('medicines')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }

        $this->replaceUniqueIndex('students', ['student_id'], ['user_id', 'student_id']);
        $this->replaceUniqueIndex('medicines', ['name'], ['user_id', 'name']);
    }

    public function down(): void
    {
        $this->replaceUniqueIndex('students', ['user_id', 'student_id'], ['student_id']);
        $this->replaceUniqueIndex('medicines', ['user_id', 'name'], ['name']);

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('medicines', function (Blueprint $table) {
            if (Schema::hasColumn('medicines', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }

    /**
     * Keep migration resilient across fresh and already-mutated local databases.
     */
    private function replaceUniqueIndex(string $tableName, array $dropColumns, array $addColumns): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($dropColumns) {
                $table->dropUnique($dropColumns);
            });
        } catch (Throwable) {
            // The index may not exist yet on some local databases.
        }

        try {
            Schema::table($tableName, function (Blueprint $table) use ($addColumns) {
                $table->unique($addColumns);
            });
        } catch (Throwable) {
            // The replacement index may already exist.
        }
    }
};
