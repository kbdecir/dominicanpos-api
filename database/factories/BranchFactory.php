<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'company_id' => Company::factory(),
            'name' => 'Sucursal ' . $this->faker->city(),
            'code' => strtoupper($this->faker->unique()->lexify('BR-???')),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('809#######'),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'sector' => $this->faker->streetName(),
            'is_main' => false,
            'is_active' => true,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
