<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->string('username')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedInteger('posts')->nullable();
            $table->dateTime('synced_at')->nullable();
            $table->dateTime('verified_at')->nullable();
        });

        DB::table('teams')
            ->update([
                'username' => DB::raw('slug')
            ]);
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('username');
            $table->dropColumn('posts');
            $table->dropColumn('synced_at');
            $table->dropColumn('verified_at');
        });
    }
};
