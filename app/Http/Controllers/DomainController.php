<?php

namespace App\Http\Controllers;

use App\Models\DnsRecord;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Site;
use App\Services\DaemonService;
use App\Services\DnsService;
use App\Services\DomainService;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    protected $domainService;
    protected $dnsService;
    protected $daemonService;

    public function __construct(
        DomainService $domainService,
        DnsService $dnsService,
        DaemonService $daemonService
    ) {
        $this->domainService = $domainService;
        $this->dnsService = $dnsService;
        $this->daemonService = $daemonService;
    }

    public function index()
    {
        $stats = [
            'total' => Domain::count(),
            'primary' => Domain::where('is_primary', true)->count(),
            'aliases' => Domain::where('is_primary', false)->count(),
            'total_dns_records' => DnsRecord::count(),
        ];

        return view('domain.list', compact('stats'));
    }

    public function create()
    {
        $servers = Server::all();
        $sites = Site::all();

        return view('domain.create', compact('servers', 'sites'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'server_id' => 'required|string|exists:servers,server_id',
            'site_id' => 'nullable|string|exists:sites,site_id',
            'is_primary' => 'nullable|boolean',
        ]);

        try {
            $data = [
                'domain' => $validated['domain'],
                'server_id' => $validated['server_id'],
                'site_id' => $validated['site_id'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
            ];
            $domain = $this->domainService->createDomain($data);

            // Sync with daemon for alias domains (non-primary)
            if (! $domain->is_primary && $domain->site) {
                $this->daemonService->createAlias(
                    $domain->domain,
                    $domain->site->username,
                    $domain->site->php,
                    $domain->site->basepath ?? ''
                );
            }

            session()->flash('success', 'Domain successfully created.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create domain: '.$e->getMessage());
        }

        return redirect()->route('domain.list');
    }

    public function show($domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        $dnsRecords = $this->domainService->getDnsRecords($domain_id);
        $stats = $this->domainService->getDomainStats($domain);

        return view('domain.edit', compact('domain', 'dnsRecords', 'stats'));
    }

    public function update(Request $request, $domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'server_id' => 'required|string|exists:servers,server_id',
            'site_id' => 'nullable|string|exists:sites,site_id',
            'is_primary' => 'nullable|boolean',
        ]);

        try {
            $this->domainService->updateDomain($domain, $validated);
            session()->flash('success', 'Domain updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update domain: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }

    public function destroy($domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        // Remove daemon alias if non-primary with a site
        if (! $domain->is_primary && $domain->site) {
            try {
                $this->daemonService->deleteAlias($domain->domain);
            } catch (\Exception $e) {
                // Continue with DB deletion even if daemon fails
            }
        }

        $this->domainService->deleteDomain($domain);

        session()->flash('success', 'Domain deleted.');

        return redirect()->route('domain.list');
    }

    public function newDnsRecord($domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        return view('domain.dns.new', compact('domain'));
    }

    public function createDnsRecord(Request $request, $domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        $request->validate([
            'zone' => 'required|string',
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,NS,SRV,CAA,PTR,CERT,SSHFP,TLSA,SOA',
            'value' => 'required|string',
            'ttl' => 'nullable|integer|min:60|max:86400',
            'priority' => 'nullable|integer|min:0|max:65535',
        ]);

        try {
            $dnsRecord = new DnsRecord;
            $dnsRecord->domain_id = $domain_id;
            $dnsRecord->site_id = $domain->site_id;
            $dnsRecord->ttl = $request->ttl ?? 3600;
            $dnsRecord->zone = $request->zone;
            $dnsRecord->type = $request->type;
            $dnsRecord->value = $request->value;
            $dnsRecord->priority = $request->priority;
            $dnsRecord->save();

            $this->dnsService->addRecord(
                $domain->domain,
                $request->zone,
                $request->type,
                $request->value,
                $request->ttl ?? 3600
            );

            session()->flash('success', 'DNS record created.');
        } catch (\Exception $e) {
            // Rollback DB record if zone sync failed
            if (isset($dnsRecord) && $dnsRecord->exists) {
                $dnsRecord->delete();
            }
            session()->flash('error', 'Failed to create DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }

    public function editDnsRecord($domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        return view('domain.dns.edit', compact('domain', 'dnsRecord'));
    }

    public function updateDnsRecord(Request $request, $domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        $request->validate([
            'zone' => 'required|string',
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,NS,SRV,CAA,PTR,CERT,SSHFP,TLSA,SOA',
            'value' => 'required|string',
            'ttl' => 'nullable|integer|min:60|max:86400',
            'priority' => 'nullable|integer|min:0|max:65535',
        ]);

        try {
            // Store old values for zone rollback
            $oldZone = $dnsRecord->zone;
            $oldType = $dnsRecord->type;
            $oldValue = $dnsRecord->value;

            // Update DB first
            $dnsRecord->ttl = $request->ttl ?? 3600;
            $dnsRecord->zone = $request->zone;
            $dnsRecord->type = $request->type;
            $dnsRecord->value = $request->value;
            $dnsRecord->priority = $request->priority;
            $dnsRecord->save();

            // Then update zone file
            try {
                $this->dnsService->deleteRecord($domain->domain, $oldZone, $oldType, $oldValue);
                $this->dnsService->addRecord(
                    $domain->domain,
                    $request->zone,
                    $request->type,
                    $request->value,
                    $request->ttl ?? 3600
                );
            } catch (\Exception $e) {
                // Zone sync failed — rollback DB to old values
                $dnsRecord->zone = $oldZone;
                $dnsRecord->type = $oldType;
                $dnsRecord->value = $oldValue;
                $dnsRecord->save();
                throw $e;
            }

            session()->flash('success', 'DNS record updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }

    public function deleteDnsRecord($domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        try {
            $zone = $dnsRecord->zone;
            $type = $dnsRecord->type;
            $value = $dnsRecord->value;

            // Delete from DB first
            $dnsRecord->delete();

            // Then remove from zone file
            $this->dnsService->deleteRecord($domain->domain, $zone, $type, $value);

            session()->flash('success', 'DNS record deleted.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }
}
