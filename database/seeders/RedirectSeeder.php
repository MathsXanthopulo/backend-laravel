<?php

namespace Database\Seeders;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RedirectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create some redirects
        $redirects = [
            [
                'destination_url' => 'https://google.com',
                'query_params' => 'utm_source=facebook&utm_campaign=ads',
                'is_active' => true,
            ],
            [
                'destination_url' => 'https://github.com',
                'query_params' => null,
                'is_active' => true,
            ],
            [
                'destination_url' => 'https://laravel.com',
                'query_params' => 'utm_source=newsletter&utm_medium=email',
                'is_active' => false,
            ],
        ];

        foreach ($redirects as $redirectData) {
            $redirect = Redirect::create($redirectData);
            
            // Create some logs for each redirect
            RedirectLog::factory()->count(rand(5, 15))->create([
                'redirect_id' => $redirect->id
            ]);
        }
    }
}
