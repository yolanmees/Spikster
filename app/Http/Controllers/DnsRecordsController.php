<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Site;

class DnsRecordsController extends Controller
{
    /**
     * Redirect legacy site DNS routes to the new domain DNS management.
     */
    public function index($site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        if (! $site) {
            abort(404, 'Site not found');
        }

        $primaryDomain = Domain::where('site_id', $site_id)
            ->where('is_primary', true)
            ->first();

        if (! $primaryDomain) {
            session()->flash('warning', 'No primary domain found for this site.');

            return redirect()->route('site.edit', $site_id);
        }

        return redirect()->route('domain.show', $primaryDomain->domain_id);
    }
}
