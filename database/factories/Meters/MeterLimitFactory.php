<?php

namespace Database\Factories\Meters;

use App\Models\Meters\Meter;
use App\Models\Meters\MeterLimit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterLimitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MeterLimit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'meter_id' => Meter::factory(),
            'source' => 'epics',
            'field' => 'gal',
        ];
    }
}
