<?php

namespace Database\Factories;

use App\Enums\PlatformType;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Platform::class;

    /**
     * A list of platforms.
     *
     * @var string[] $platforms
     */
    protected array $platforms = [
        'Atari 2600',
        'Atari 5200',
        'Atari 7800',
        'Atari Jaguar',
        'Atari Jaguar CD',
        'Atari Lynx',
        'Bally Astrocade',
        'Bandai Playdia',
        'Bandai WonderSwan',
        'Bandai WonderSwan Color',
        'Casio PV 1000',
        'ColecoVision',
        'Emerson Arcadia 2001',
        'Epoch Cassette Vision',
        'Magnavox Odyssey',
        'Magnavox Odyssey 2',
        'Mattel Intellivision',
        'Microsoft Xbox',
        'Microsoft Xbox 360',
        'Microsoft Xbox One',
        'Microsoft Xbox Series X|S',
        'NEC PC-FX',
        'NEC Turbografx 16',
        'NEC Turbografx CD',
        'Neo Geo AES/MVS',
        'Neo Geo CD',
        'Neo Geo Pocket Color',
        'Nintendo 64',
        'Nintendo 64DD',
        'Nintendo 3DS',
        'Nintendo DS',
        'Nintendo Entertainment System',
        'Nintendo Famicom',
        'Nintendo Famicom Disk System',
        'Nintendo Game Boy',
        'Nintendo Game Boy Advance',
        'Nintendo Game Boy Color',
        'Nintendo GameCube',
        'Nintendo Switch',
        'Nintendo Virtual Boy',
        'Nintendo Wii',
        'Nintendo Wii U',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement($this->platforms);

        return [
            'slug'              => str($name)->slug(),
            'original_name'     => $name,
            'name'              => $name,
            'synonym_titles'    => $this->faker->sentences(),
            'about'             => $this->faker->realText(),
            'type'              => PlatformType::getRandomValue(),
            'generation'        => $this->faker->numberBetween(1, 10),
            'started_at'        => now()->subYears($this->faker->randomNumber(1)),
            'ended_at'          => now()->addYears($this->faker->randomNumber(1)),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
