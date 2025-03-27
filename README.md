## Laravel Scheduled Task & API for Random Users

This project sets up a scheduled task that fetches random users from an external API every 5 minutes and stores them in a database. Additionally, it provides a public API to retrieve users with filtering options.

## Features

- Scheduled Task: Fetches 5 random users every 5 minutes from 
`https://randomuser.me/api/`

- Database Structure:
    - `users` : table Stores user name and email.
    - `user_details` : table Stores user gender.
    - `locations` : table Stores city and country.

- Public API: Provides filtering by gender, city, country, and allows field selection.

## Setup Instructions

- Install Dependencies
`composer install`

- Set Up Environment
`cp .env.example .env`

`php artisan key:generate`

- Run Migrations
`php artisan migrate`

- Create the Scheduler Command
`php artisan make:command FetchRandomUsersJob`
Paste the following code in `app/Console/Commands/FetchRandomUsersJob.php`

` public function handle()
    {
        for ($i = 0; $i < 5; $i++) {
            $response = Http::get('https://randomuser.me/api/')->json();
            $userData = $response['results'][0];

            $user = User::create([
                'name'  => $userData['name']['first'] . ' ' . $userData['name']['last'],
                'email' => $userData['email']
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'gender'  => $userData['gender']
            ]);

            Location::create([
                'user_id' => $user->id,
                'city'    => $userData['location']['city'],
                'country' => $userData['location']['country']
            ]);
        }
        $this->info('5 users fetched and stored successfully.');
    }
`
- Schedule the Task
Edit `app/Console/Kernel.php` and add:

`protected function schedule(Schedule $schedule)
{
    $schedule->command('fetch:random-users-job')->everyFiveMinutes();
}`

- Run Scheduler
`php artisan schedule:work`

- Create API Endpoint
Edit  `routes/api.php` and add:
`Route::get('/users', [UserController::class, 'index']);`

Edit `app/Http/Controllers/API/UserController.php` and add:
`public function index(Request $request)
{
    $query = User::with(['details', 'location']);

    if ($request->has('gender')) {
        $query->whereHas('details', fn($q) => $q->where('gender', $request->gender));
    }
    if ($request->has('city')) {
        $query->whereHas('location', fn($q) => $q->where('city', $request->city));
    }
    if ($request->has('country')) {
        $query->whereHas('location', fn($q) => $q->where('country', $request->country));
    }

    $limit = $request->input('limit', 10);
    $users = $query->limit($limit)->get();

    if ($request->has('fields')) {
        $fields = explode(',', $request->fields);
        $users = $users->map(fn($user) => collect($user)->only($fields));
    }

    return response()->json($users);
}`

- Test the API
Run the Laravel development server:
`php artisan serve`
    - API Usage Examples
        - Get users filtered by gender:
            `GET /api/users?gender=male`
        - Get users filtered by city:
            `GET /api/users?city=New York`
        - Get users filtered by country:
            `GET /api/users?country=USA`
        - Get 20 users:
            `GET /api/users?limit=20`
        - Select specific fields:
            `GET /api/users?fields=name,email,city`

- This project is fetching random users every 5 minutes and exposing a public API with filtering options!
