<?php

namespace Database\Factories\Meters;

use App\Models\Buildings\Building;
use App\Models\Meters\Meter;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Meter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'epics_name' => $this->faker->word(),
            'name_alias' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(['power', 'water', 'gas']),
            'building_id' => Building::factory(),
        ];
    }
}
