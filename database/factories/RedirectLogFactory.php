<?php

namespace Database\Factories;

use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RedirectLog>
 */
class RedirectLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        $referers = [
            'https://google.com',
            'https://facebook.com',
            'https://twitter.com',
            'https://instagram.com',
            'https://linkedin.com',
            'https://github.com',
            'https://stackoverflow.com',
            'https://medium.com',
            'https://dev.to',
            'https://laravel.com',
            null,
        ];

        $queryParams = [
            [],
            ['utm_source' => 'facebook', 'utm_campaign' => 'ads'],
            ['utm_source' => 'google', 'utm_medium' => 'cpc'],
            ['utm_source' => 'instagram', 'utm_campaign' => 'social'],
            ['ref' => 'newsletter', 'campaign' => 'winter2024'],
            ['utm_source' => 'email', 'utm_medium' => 'newsletter'],
        ];

        return [
            'redirect_id' => Redirect::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->randomElement($userAgents),
            'referer' => $this->faker->randomElement($referers),
            'query_params' => $this->faker->randomElement($queryParams),
        ];
    }
}
