<?php

namespace App\Services\Providers;

interface CloudProviderInterface
{
    public function getName(): string;
    public function listRegions(): array;
    public function listSizes(): array;
    public function listImages(): array;
    public function createServer(string $name, string $region, string $size, string $image): array;
    public function deleteServer(string $providerId): bool;
    public function getServer(string $providerId): array;
    public function rebootServer(string $providerId): bool;
}
