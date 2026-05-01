<?php

namespace App\Http\Controllers\Server;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\Fail2banService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Fail2banController extends Controller
{
    public function __construct(
        protected Fail2banService $fail2banService
    ) {}

    /**
     * Get all Fail2ban jails with statistics
     */
    public function jails(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $jails = $this->fail2banService->getJails($server);

            return response()->json(['jails' => $jails, 'total' => count($jails)]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban jails error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to fetch Fail2ban jails', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Get status of a specific Fail2ban jail
     */
    public function jailStatus(string $server_id, string $jail)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $status = $this->fail2banService->getJailStatus($server, $jail);

            return response()->json(['jail' => $jail, 'status' => $status]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban jail status error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to fetch jail status', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Ban an IP address
     */
    public function banIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'jail' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $ip = $request->input('ip');
            $jail = $request->input('jail', 'sshd');
            $result = $this->fail2banService->banIp($server, $ip, $jail);

            return response()->json([
                'message' => "IP {$ip} banned successfully in jail {$jail}",
                'success' => $result,
            ]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban ban IP error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to ban IP', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Unban an IP address
     */
    public function unbanIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'jail' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $ip = $request->input('ip');
            $jail = $request->input('jail');
            $result = $this->fail2banService->unbanIp($server, $ip, $jail);

            $message = $jail
                ? "IP {$ip} unbanned successfully from jail {$jail}"
                : "IP {$ip} unbanned successfully from all jails";

            return response()->json(['message' => $message, 'success' => $result]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban unban IP error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to unban IP', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Check if IP is banned
     */
    public function checkIp(string $server_id, string $ip)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $banned = $this->fail2banService->isIpBanned($server, $ip);

            return response()->json(['ip' => $ip, 'is_banned' => ! empty($banned), 'ban_details' => $banned]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban check IP error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to check IP status', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Get Fail2ban statistics
     */
    public function stats(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $stats = $this->fail2banService->getStatistics($server);
            $serviceStatus = $this->fail2banService->getServiceStatus($server);

            return response()->json(['statistics' => $stats, 'service' => $serviceStatus]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban stats error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to fetch Fail2ban statistics', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Get Fail2ban logs
     */
    public function logs(Request $request, string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $lines = $request->input('lines', 100);
            $logs = $this->fail2banService->getLogs($server, $lines);

            return response()->json(['logs' => $logs, 'total' => count($logs)]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban logs error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to fetch Fail2ban logs', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Whitelist an IP address
     */
    public function whitelistIp(Request $request, string $server_id)
    {
        $validator = Validator::make($request->all(), ['ip' => 'required|ip']);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $ip = $request->input('ip');
            $result = $this->fail2banService->whitelistIp($server, $ip);

            return response()->json(['message' => "IP {$ip} whitelisted successfully", 'success' => $result]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban whitelist IP error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to whitelist IP', 'errors' => $th->getMessage()], 500);
        }
    }

    /**
     * Get whitelisted IPs
     */
    public function getWhitelist(string $server_id)
    {
        $server = Server::where('server_id', $server_id)->where('status', 1)->first();

        if (! $server) {
            return response()->json(['message' => 'Server not found', 'errors' => 'Not found'], 404);
        }

        try {
            $whitelist = $this->fail2banService->getWhitelistedIps($server);

            return response()->json(['whitelist' => $whitelist, 'total' => count($whitelist)]);
        } catch (\Throwable $th) {
            Log::error('Fail2ban get whitelist error: '.$th->getMessage());

            return response()->json(['message' => 'Failed to fetch whitelist', 'errors' => $th->getMessage()], 500);
        }
    }
}
