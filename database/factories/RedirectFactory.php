<?php

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $urls = [
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
        ];

        $queryParams = [
            null,
            'utm_source=facebook&utm_campaign=ads',
            'utm_source=google&utm_medium=cpc',
            'utm_source=instagram&utm_campaign=social',
            'ref=newsletter&campaign=winter2024',
        ];

        return [
            'destination_url' => $this->faker->randomElement($urls),
            'query_params' => $this->faker->randomElement($queryParams),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'last_accessed_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the redirect is active.
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    /**
     * Indicate that the redirect is inactive.
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
