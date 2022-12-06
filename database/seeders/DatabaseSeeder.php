<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Continent;
use App\Models\Country;
use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $index = 0;
        $continentsCode = ['AF', 'AN', 'AS', 'EU', 'NA', 'OC', 'SA'];

        $continents = Continent::factory(count($continentsCode))->make()->each(function ($continent) use (&$index,$continentsCode) {
           $continent->code = $continentsCode[$index];
           $continent->save();
           $index++;
        });

        $countries = Country::factory(20)->make()->each(function ($country) use ($continents) {
            $country->continent_id = $continents->random()->id;
            $country->save();
        });

        $users = User::factory(10)->make()->each(function ($user) use ($countries) {
           $user->country_id = $countries->random()->id;
           $user->save();
        });

        $projects = Project::factory(100)->make()->each(function ($project) use ($users) {
           $project->user_id = rand(1, count($users));
           $project->save();
        });

        $labels = Label::factory(200)->make()->each(function ($label) use ($users) {
            $label->user_id = rand(1, count($users));
            $label->save();
        });

        $projects->each(function ($project) use ($users, $labels) {
            $project->users()->attach($users->random(rand(1,3))->pluck('id'));
            $project->labels()->attach($labels->random(rand(1, 3))->pluck('id'));
        });

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
