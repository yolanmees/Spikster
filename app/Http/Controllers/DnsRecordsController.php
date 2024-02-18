<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DnsRecord;
use App\Services\DnsService;
use App\Models\Site;

class DnsRecordsController extends Controller
{
    protected $dnsService;

    public function __construct(DnsService $dnsService)
    {
        $this->dnsService = $dnsService;
    }

    //
    public function index($site_id)
    {
        // $dns = $this->dnsService->getDnsRecords($site_id);
        $dnsRecords = DnsRecord::where('site_id', $site_id)->get();
        return view('site.dns.index', compact('site_id', 'dnsRecords'));
    }

    public function new($site_id)
    {
        return view('site.dns.new', compact('site_id'));
    }

    public function create(Request $request, $site_id)
    {
        $site = Site::where('site_id', $site_id)->first();

        $request->validate([
            'zone' => 'required',
            'type' => 'required',
            'value' => 'required',
        ]);

        try {
            $this->dnsService->addRecord($site->domain , $request->zone, $request->type, $request->value, $request->ttl);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $dnsRecord = new DnsRecord;
        $dnsRecord->site_id = $site_id;
        $dnsRecord->ttl = $request->ttl;
        $dnsRecord->zone = $request->zone;
        $dnsRecord->type = $request->type;
        $dnsRecord->value = $request->value;
        $dnsRecord->save();

        return redirect()->route('site.dns', $site_id);
    }

    public function edit($site_id, $dns_id)
    {
        $dnsRecord = DnsRecord::where('site_id', $site_id)->where('id', $dns_id)->first();
        return view('site.dns.edit', compact('site_id', 'dnsRecord'));
    }

    public function update(Request $request, $site_id, $dns_id)
    {
        $request->validate([
            'zone' => 'required',
            'type' => 'required',
            'value' => 'required',
        ]);

        try {
            $dnsRecord = DnsRecord::where('site_id', $site_id)->where('id', $dns_id)->first();
            $this->dnsService->deleteRecord($dnsRecord->zone, $dnsRecord->type, $dnsRecord->value);
            $this->dnsService->addRecord($request->zone, $request->type, $request->value, $request->ttl);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $dnsRecord->ttl = $request->ttl;
        $dnsRecord->zone = $request->zone;
        $dnsRecord->type = $request->type;
        $dnsRecord->value = $request->value;
        $dnsRecord->save();

        return redirect()->route('site.dns', $site_id);
    }

    public function delete($site_id, $dns_id)
    {
        try {
            $dnsRecord = DnsRecord::where('site_id', $site_id)->where('id', $dns_id)->first();
            $this->dnsService->deleteRecord($dnsRecord->zone, $dnsRecord->type, $dnsRecord->value);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        $dnsRecord->delete();

        return redirect()->route('site.dns', $site_id);
    }
}
