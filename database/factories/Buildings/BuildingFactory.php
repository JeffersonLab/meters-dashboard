<?php

namespace Database\Factories\Buildings;

use App\Models\Buildings\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

class BuildingFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Building::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'abbreviation' => $this->faker->word(),
            'jlab_name' => $this->faker->sentence(3),
            'building_num' => $this->faker->numberBetween(1,100),
            'square_footage' => $this->faker->randomFloat(1,0,1000),
        ];
    }
}
