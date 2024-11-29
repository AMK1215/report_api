<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

     public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('password')->default(Hash::make('delightmyanmar'))->change();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('password')->default(null)->change();
    });
}

//     public function up()
// {
//     Schema::table('users', function (Blueprint $table) {
//         $table->string('password')->nullable()->change();
//     });
// }

// public function down()
// {
//     Schema::table('users', function (Blueprint $table) {
//         $table->string('password')->nullable(false)->change();
//     });
// }

};