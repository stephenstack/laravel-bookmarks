<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
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
            'icon' => $this->faker->randomElement(['bookmark', 'palette', 'code', 'wrench', 'book-open', 'sparkles']),
            'color' => $this->faker->randomElement(['neutral', 'violet', 'blue', 'amber', 'emerald', 'pink']),
            'user_id' => \App\Models\User::factory(),
            'order' => $this->faker->randomNumber(),
        ];
    }
}
