<?php

namespace App\Services;

use App\Models\DnsRecord;
use App\Models\Domain;
use App\Models\Site;
use Illuminate\Support\Str;

class DomainService
{
    /**
     * Get all domains query.
     */
    public function getAllDomainsQuery()
    {
        return Domain::with(['site', 'server', 'dnsRecords']);
    }

    /**
     * Get all domains.
     */
    public function getAllDomains()
    {
        return $this->getAllDomainsQuery()->get();
    }

    /**
     * Get domain by ID.
     */
    public function getDomainById(string $domainId): ?Domain
    {
        return Domain::where('domain_id', $domainId)
            ->with(['site', 'server', 'dnsRecords'])
            ->first();
    }

    /**
     * Get domains by site.
     */
    public function getDomainsBySite(string $siteId)
    {
        return Domain::where('site_id', $siteId)
            ->with(['dnsRecords'])
            ->get();
    }

    /**
     * Get domains by server.
     */
    public function getDomainsByServer(string $serverId)
    {
        return Domain::where('server_id', $serverId)
            ->with(['site', 'dnsRecords'])
            ->get();
    }

    /**
     * Create a new domain.
     */
    public function createDomain(array $data): Domain
    {
        $domain = Domain::create([
            'domain_id' => Str::uuid(),
            'site_id' => $data['site_id'] ?? null,
            'server_id' => $data['server_id'],
            'domain' => $data['domain'],
            'is_primary' => $data['is_primary'] ?? false,
        ]);

        return $domain;
    }

    /**
     * Update a domain.
     */
    public function updateDomain(Domain $domain, array $data): Domain
    {
        $domain->update([
            'domain' => $data['domain'] ?? $domain->domain,
            'site_id' => $data['site_id'] ?? $domain->site_id,
        ]);

        return $domain->fresh();
    }

    /**
     * Delete a domain.
     */
    public function deleteDomain(Domain $domain): bool
    {
        // Delete all DNS records for this domain
        DnsRecord::where('domain_id', $domain->domain_id)->delete();

        return $domain->delete();
    }

    /**
     * Get DNS records for a domain.
     */
    public function getDnsRecords(string $domainId)
    {
        return DnsRecord::where('domain_id', $domainId)
            ->orderBy('type')
            ->orderBy('zone')
            ->get();
    }

    /**
     * Get domain statistics.
     */
    public function getDomainStats(Domain $domain): array
    {
        return [
            'dns_records_count' => $domain->dnsRecords()->count(),
            'site_name' => $domain->site?->domain ?? 'N/A',
            'server_name' => $domain->server?->name ?? 'N/A',
            'server_ip' => $domain->server?->ip ?? 'N/A',
        ];
    }

    /**
     * Check if domain exists on server.
     */
    public function domainExistsOnServer(string $domain, string $serverId, ?string $excludeDomainId = null): bool
    {
        $query = Domain::where('domain', $domain)
            ->where('server_id', $serverId);

        if ($excludeDomainId) {
            $query->where('domain_id', '!=', $excludeDomainId);
        }

        return $query->exists();
    }
}
