<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DnsRecord;
use App\Models\Site;
use App\Services\DnsService;
use Illuminate\Http\Request;

class DnsRecordsController extends Controller
{
    protected $dnsService;

    public function __construct(DnsService $dnsService)
    {
        $this->dnsService = $dnsService;
    }

    /**
     * Display DNS records for a site (backward compatibility).
     * Redirects to primary domain DNS management.
     */
    public function index($site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            abort(404, 'Site not found');
        }

        // Get primary domain for this site
        $primaryDomain = Domain::where('site_id', $site_id)
            ->where('is_primary', true)
            ->first();

        // If no primary domain exists, redirect to site with message
        if (! $primaryDomain) {
            session()->flash('warning', 'No primary domain found for this site.');

            return redirect()->route('site.edit', $site_id);
        }

        // Redirect to domain DNS management
        return redirect()->route('domain.show', $primaryDomain->domain_id);
    }

    /**
     * All other methods redirect to domain controller.
     */
    public function new($site_id)
    {
        return $this->index($site_id);
    }

    public function create(Request $request, $site_id)
    {
        return $this->index($site_id);
    }

    public function edit($site_id, $dns_id)
    {
        return $this->index($site_id);
    }

    public function update(Request $request, $site_id, $dns_id)
    {
        return $this->index($site_id);
    }

    public function delete($site_id, $dns_id)
    {
        return $this->index($site_id);
    }
}
