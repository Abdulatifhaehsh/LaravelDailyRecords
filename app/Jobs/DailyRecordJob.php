<?php

namespace App\Jobs;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class DailyRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $maleCount = Redis::get('male_count');
        $femaleCount = Redis::get('female_count');

        $maleAvgAge = User::where('gender', 'male')->avg('age');
        $femaleAvgAge = User::where('gender', 'female')->avg('age');

        Log::info('DailyRecordJob executed', [
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'male_avg_age' => $maleAvgAge,
            'female_avg_age' => $femaleAvgAge,
        ]);

        $record = DailyRecord::create([
            'date' => now()->toDateString(),
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'male_avg_age' => $maleAvgAge,
            'female_avg_age' => $femaleAvgAge,
        ]);

        Log::info('DailyRecord created', ['record' => $record]);

        Redis::del(['male_count', 'female_count']);
    }
}
