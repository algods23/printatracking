<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Received', 'Cancelled') NOT NULL DEFAULT 'Pending'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->replaceSqliteStatusConstraint(
                "('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Cancelled')",
                "('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Received', 'Cancelled')"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('tasks')->where('status', 'Received')->update(['status' => 'Completed']);
            DB::statement("ALTER TABLE tasks MODIFY status ENUM('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending'");
        } elseif (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::table('tasks')->where('status', 'Received')->update(['status' => 'Completed']);
            $this->replaceSqliteStatusConstraint(
                "('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Received', 'Cancelled')",
                "('Pending', 'Designing', 'Printing', 'Installing', 'Completed', 'Cancelled')"
            );
        }
    }

    private function replaceSqliteStatusConstraint(string $oldValues, string $newValues): void
    {
        $sql = DB::table('sqlite_master')
            ->where('type', 'table')
            ->where('name', 'tasks')
            ->value('sql');

        $oldSql = '"status" varchar check ("status" in ' . $oldValues . ") not null default 'Pending'";
        $newSql = '"status" varchar check ("status" in ' . $newValues . ") not null default 'Pending'";

        if (! $sql || ! str_contains($sql, $oldSql)) {
            return;
        }

        $schemaVersion = DB::selectOne('PRAGMA schema_version')->schema_version;

        DB::statement('PRAGMA writable_schema = ON');
        DB::table('sqlite_master')
            ->where('type', 'table')
            ->where('name', 'tasks')
            ->update(['sql' => str_replace($oldSql, $newSql, $sql)]);
        DB::statement('PRAGMA schema_version = ' . ((int) $schemaVersion + 1));
        DB::statement('PRAGMA writable_schema = OFF');
    }
};
