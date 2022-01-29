<?php

namespace Database\Factories\Meters;

use App\Models\Meters\Meter;
use App\Models\Meters\VirtualMeter;
use Illuminate\Database\Eloquent\Factories\Factory;

class VirtualMeterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VirtualMeter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(8),
        ];
    }
}
