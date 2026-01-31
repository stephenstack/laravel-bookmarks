<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'collection_id' => \App\Models\Collection::factory(),
            'title' => $this->faker->sentence(3),
            'url' => $this->faker->url(),
            'description' => $this->faker->sentence(),
            'favicon' => $this->faker->imageUrl(64, 64),
            'is_favorite' => $this->faker->boolean(),
        ];
    }
}
