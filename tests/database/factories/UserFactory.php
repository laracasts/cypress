<?php

namespace Laracasts\Cypress\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laracasts\Cypress\Tests\Support\TestUser;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TestUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'plan' => 'monthly',
            'password' => 'foopassword',
        ];
    }

    public function guest()
    {
        return $this->state(
            fn() => [
                'plan' => 'guest'
            ]
        );
    }
}
