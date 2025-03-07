<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $filipinoFirstNames = [
        // Traditional Names
        'Juan', 'Maria', 'Jose', 'Marianne', 'Mark', 'Lea', 'Pedro', 'Sophia', 'Ramon', 'Erika',
        'Draven', 'Dhea', 'Angelo', 'Angela', 'Hans', 'Dyrylle', 'Jaylord', 'Consuelo', 'Leandro', 'Rosario',
        'Diego', 'Lourdes', 'Nataniel', 'Nathalee', 'Andrei', 'Isabel', 'Manuel', 'Elena', 'Vicente', 'Victoria',
        'Luis', 'Dolores', 'Carlos', 'Imelda', 'Fernando', 'Cristina', 'Rafael', 'Cecilia', 'Emilio', 'Olive',
        'Excaltacion', 'Teressa', 'Marcel', 'Lourdes', 'Esteban', 'Liana', 'Julio', 'Mercedes', 'Joshua', 'Frances',

        // Modern Names
        'Elijah', 'Amara', 'Mateo', 'Zara', 'Gabriel', 'Aria', 'Enzo', 'Luna', 'Lucas', 'Mila',
        'Eduardo', 'Serena', 'Matias', 'Isabella', 'Diego', 'Athena', 'Sebastian', 'Ysabel', 'Matteo', 'Isabel',
        'Zephyr', 'Esme', 'Ashton', 'Nina', 'Levi', 'Aurora', 'Kai', 'Ivy', 'Milo', 'Lily',

        // Add more first names as needed
    ];

    protected $filipinoSurnames = [
        // Common Surnames
        'Santos', 'Reyes', 'Cruz', 'Lopez', 'Garcia', 'Torres', 'Ramos', 'Castillo', 'Flores', 'Diaz',
        'Rodriguez', 'Mendoza', 'Perez', 'Lim', 'Tan', 'Aquino', 'Villanueva', 'Manuel', 'Fernandez', 'Cordero',
        'Bautista', 'Balagtas', 'Santillan', 'Castro', 'Mariano', 'Legaspi', 'Pascual', 'Abad', 'Palacios', 'Velasco',
        'Gonzales', 'Panganiban', 'Basilio', 'Montemayor', 'Rizal', 'Carpio', 'Luna', 'Magbanua', 'Tolentino', 'Hernandez',
        'Alcantara', 'Zamora', 'Sison', 'Miranda', 'Malabanan', 'Villar', 'Mangahas', 'Maliksi', 'Ignacio', 'Madrigal',
        // Add more surnames as needed
    ];
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = 2019;
        $currentYear = Carbon::now()->year;

        $year = $this->faker->numberBetween($startYear, $currentYear);

        $name = $this->faker->randomElement($this->filipinoFirstNames);
        $surname = $this->faker->randomElement($this->filipinoSurnames);
        $email = strtolower($name) . '.' . strtolower($surname) . '.' . $this->faker->regexify('[0-9]{2}') . '@example.com';

        return [
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            // 'phone' => '9' . $this->faker->numerify('#########'),
            'schoolid' => $year . '-' . $this->faker->regexify('[0-9]{2}') . '-' . $this->faker->regexify('[0-9]{4}'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
