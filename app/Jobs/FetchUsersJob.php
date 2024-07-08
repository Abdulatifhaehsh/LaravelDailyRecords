<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FetchUsersJob implements ShouldQueue
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
        $response = Http::get('https://randomuser.me/api/?results=20');
        $users = $response->json()['results'];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['uuid' => $userData['login']['uuid']],
                [
                    'name' => $userData['name']['first'] . ' ' . $userData['name']['last'],
                    'age' => $userData['dob']['age'],
                    'gender' => $userData['gender'],
                ]
            );

            Log::info('useer created', ['record' => $user]);

            Redis::incr('user_count');
            Redis::incr($user->gender . '_count');
        }
    }
}
