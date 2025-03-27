<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchRandomUsersJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:random-users-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch random users from API and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        for ($i = 0; $i < 5; $i++) {
            $response = Http::get('https://randomuser.me/api/')->json();
            $userData = $response['results'][0];

            $user = User::create([
                'name'  => $userData['name']['first'] . ' ' . $userData['name']['last'],
                'email' => $userData['email']
            ]);

            if($user){
                $userDetails = UserDetail::create([
                    'user_id' => $user->id,
                    'gender'  => $userData['gender']
                ]);

                if($userDetails) {
                    Location::create([
                        'user_id' => $user->id,
                        'city'    => $userData['location']['city'],
                        'country' => $userData['location']['country']
                    ]);
                }
            }
        }

        $this->info('5 users fetched and stored successfully.');
    }
}
