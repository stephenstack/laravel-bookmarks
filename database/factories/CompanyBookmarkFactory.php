<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyBookmark>
 */
class CompanyBookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->company(),
            'url' => $this->faker->url(),
            'description' => $this->faker->catchPhrase(),
            'favicon' => $this->faker->imageUrl(64, 64),
        ];
    }
}
