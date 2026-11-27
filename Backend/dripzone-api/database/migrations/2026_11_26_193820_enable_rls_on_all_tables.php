<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    foreach ($tables as $row) {
        DB::statement('ALTER TABLE "' . $row->tablename . '" ENABLE ROW LEVEL SECURITY');
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
