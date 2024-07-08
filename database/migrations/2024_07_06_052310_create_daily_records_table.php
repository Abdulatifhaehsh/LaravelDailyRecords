<?php

use App\Models\DailyRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(DailyRecord::table, function (Blueprint $table) {
            $table->id();
            $table->date(DailyRecord::date)->unique();
            $table->integer(DailyRecord::maleCount)->default(0);
            $table->integer(DailyRecord::femaleCount)->default(0);
            $table->float(DailyRecord::maleAvgAge)->default(0);
            $table->float(DailyRecord::femaleAvgAge)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(DailyRecord::table);
    }
};
