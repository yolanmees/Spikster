<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DnsRecord;
use App\Services\DomainService;
use App\Services\DnsService;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    protected $domainService;

    protected $dnsService;

    public function __construct(DomainService $domainService, DnsService $dnsService)
    {
        $this->domainService = $domainService;
        $this->dnsService = $dnsService;
    }

    /**
     * Display a listing of all domains.
     */
    public function index()
    {
        return view('domain.list');
    }

    /**
     * Display the specified domain with DNS records.
     */
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

    /**
     * Show the form for creating a new DNS record.
     */
    public function newDnsRecord($domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        return view('domain.dns.new', compact('domain'));
    }

    /**
     * Store a newly created DNS record.
     */
    public function createDnsRecord(Request $request, $domain_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);

        if (! $domain) {
            abort(404, 'Domain not found');
        }

        $request->validate([
            'zone' => 'required|string',
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,NS,SRV',
            'value' => 'required|string',
            'ttl' => 'nullable|integer|min:60|max:86400',
            'priority' => 'nullable|integer|min:0|max:65535',
        ]);

        try {
            $this->dnsService->addRecord(
                $domain->domain,
                $request->zone,
                $request->type,
                $request->value,
                $request->ttl ?? 3600
            );

            $dnsRecord = new DnsRecord;
            $dnsRecord->domain_id = $domain_id;
            $dnsRecord->site_id = $domain->site_id;
            $dnsRecord->ttl = $request->ttl ?? 3600;
            $dnsRecord->zone = $request->zone;
            $dnsRecord->type = $request->type;
            $dnsRecord->value = $request->value;
            $dnsRecord->priority = $request->priority;
            $dnsRecord->save();

            session()->flash('success', 'DNS record successfully created.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }

    /**
     * Show the form for editing the specified DNS record.
     */
    public function editDnsRecord($domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        return view('domain.dns.edit', compact('domain', 'dnsRecord'));
    }

    /**
     * Update the specified DNS record.
     */
    public function updateDnsRecord(Request $request, $domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        $request->validate([
            'zone' => 'required|string',
            'type' => 'required|string|in:A,AAAA,CNAME,MX,TXT,NS,SRV',
            'value' => 'required|string',
            'ttl' => 'nullable|integer|min:60|max:86400',
            'priority' => 'nullable|integer|min:0|max:65535',
        ]);

        try {
            // Delete old record
            $this->dnsService->deleteRecord(
                $domain->domain,
                $dnsRecord->zone,
                $dnsRecord->type,
                $dnsRecord->value
            );

            // Add new record
            $this->dnsService->addRecord(
                $domain->domain,
                $request->zone,
                $request->type,
                $request->value,
                $request->ttl ?? 3600
            );

            // Update database
            $dnsRecord->ttl = $request->ttl ?? 3600;
            $dnsRecord->zone = $request->zone;
            $dnsRecord->type = $request->type;
            $dnsRecord->value = $request->value;
            $dnsRecord->priority = $request->priority;
            $dnsRecord->save();

            session()->flash('success', 'DNS record successfully updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }

    /**
     * Remove the specified DNS record.
     */
    public function deleteDnsRecord($domain_id, $dns_id)
    {
        $domain = $this->domainService->getDomainById($domain_id);
        $dnsRecord = DnsRecord::where('domain_id', $domain_id)->where('id', $dns_id)->first();

        if (! $domain || ! $dnsRecord) {
            abort(404, 'Domain or DNS record not found');
        }

        try {
            $this->dnsService->deleteRecord(
                $domain->domain,
                $dnsRecord->zone,
                $dnsRecord->type,
                $dnsRecord->value
            );

            $dnsRecord->delete();

            session()->flash('success', 'DNS record successfully deleted.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete DNS record: '.$e->getMessage());
        }

        return redirect()->route('domain.show', $domain_id);
    }
}
