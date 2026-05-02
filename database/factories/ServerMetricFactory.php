<?php

namespace Database\Factories;

use App\Models\Server;
use App\Models\ServerMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerMetricFactory extends Factory
{
    protected $model = ServerMetric::class;

    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'cpu_percent' => $this->faker->randomFloat(2, 0, 100),
            'cpu_cores' => $this->faker->numberBetween(1, 32),
            'memory_total' => $this->faker->numberBetween(1024, 65536),
            'memory_used' => $this->faker->numberBetween(256, 32768),
            'memory_free' => $this->faker->numberBetween(256, 32768),
            'memory_percent' => $this->faker->randomFloat(2, 0, 100),
            'disk_total' => $this->faker->numberBetween(10240, 1024000),
            'disk_used' => $this->faker->numberBetween(1024, 512000),
            'disk_free' => $this->faker->numberBetween(1024, 512000),
            'disk_percent' => $this->faker->randomFloat(2, 0, 100),
            'load_1' => $this->faker->randomFloat(2, 0, 10),
            'load_5' => $this->faker->randomFloat(2, 0, 8),
            'load_15' => $this->faker->randomFloat(2, 0, 6),
            'network_bytes_sent' => $this->faker->randomNumber(),
            'network_bytes_recv' => $this->faker->randomNumber(),
            'network_packets_sent' => $this->faker->randomNumber(),
            'network_packets_recv' => $this->faker->randomNumber(),
            'uptime_seconds' => $this->faker->numberBetween(60, 86400 * 30),
            'measured_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
