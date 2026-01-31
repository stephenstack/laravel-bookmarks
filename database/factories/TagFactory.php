<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'color' => $this->faker->randomElement(['bg-blue-500/10 text-blue-500', 'bg-violet-500/10 text-violet-500']),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
