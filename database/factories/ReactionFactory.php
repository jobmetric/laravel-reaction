<?php

namespace JobMetric\Reaction\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Reaction\Models\Reaction;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'liker_type' => null,
            'liker_id' => null,
            'reactable_type' => null,
            'reactable_id' => null,
            'reaction' => $this->faker->randomElement(['like', 'dislike', 'love', 'angry', 'sad']),
            'ip' => $this->faker->ipv4(),
            'device_id' => $this->faker->uuid(),
            'source' => $this->faker->randomElement(['web', 'mobile', 'api']),
        ];
    }

    /**
     * set liker
     *
     * @param string $liker_type
     * @param int $liker_id
     *
     * @return static
     */
    public function setLiker(string $liker_type, int $liker_id): static
    {
        return $this->state(fn(array $attributes) => [
            'liker_type' => $liker_type,
            'liker_id' => $liker_id
        ]);
    }

    /**
     * set reactable
     *
     * @param string $reactable_type
     * @param int $reactable_id
     *
     * @return static
     */
    public function setReactable(string $reactable_type, int $reactable_id): static
    {
        return $this->state(fn(array $attributes) => [
            'reactable_type' => $reactable_type,
            'reactable_id' => $reactable_id
        ]);
    }

    /**
     * set reaction type
     *
     * @param string $reaction
     *
     * @return static
     */
    public function setReaction(string $reaction): static
    {
        return $this->state(fn(array $attributes) => [
            'reaction' => $reaction
        ]);
    }

    /**
     * set ip
     *
     * @param string $ip
     *
     * @return static
     */
    public function setIp(string $ip): static
    {
        return $this->state(fn(array $attributes) => [
            'ip' => $ip
        ]);
    }

    /**
     * set device id
     *
     * @param string $device_id
     *
     * @return static
     */
    public function setDeviceId(string $device_id): static
    {
        return $this->state(fn(array $attributes) => [
            'device_id' => $device_id
        ]);
    }

    /**
     * set source
     *
     * @param string $source
     *
     * @return static
     */
    public function setSource(string $source): static
    {
        return $this->state(fn(array $attributes) => [
            'source' => $source
        ]);
    }
}
