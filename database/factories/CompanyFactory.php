<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->company(),
            'business_name' => $this->faker->company() . ' SRL',
            'rnc' => (string) $this->faker->unique()->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('809#######'),
            'address' => $this->faker->address(),
            'logo_path' => null,
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
