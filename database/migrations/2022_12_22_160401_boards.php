<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->enum('state', ['livre', 'ocupada', ''])->default('livre')->index();
            $table->timestamps();
        });

        DB::table('boards')->insert([
            [
                'state' => 'ocupada',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [

                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'state' => 'livre',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boards');
    }
};
