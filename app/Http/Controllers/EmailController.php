<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\EmailAccount;
use App\Models\EmailForwarder;
use App\Models\EmailAlias;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EmailController extends Controller
{
    public function __construct(
        protected EmailService $emailService
    ) {}

    /**
     * Get all email accounts for a site.
     *
     * @OA\Get(
     *     path="/api/sites/{site_id}/email/accounts",
     *     tags={"Email"},
     *     summary="List email accounts",
     *     @OA\Parameter(name="site_id", in="path", required=true),
     *     @OA\Response(response=200, description="List of email accounts")
     * )
     */
    public function indexAccounts(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $accounts = $this->emailService->getAccountsBySite($site);

        return response()->json([
            'accounts' => $accounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'email' => $account->email,
                    'quota_mb' => $account->quota_mb,
                    'quota_usage' => $this->emailService->getQuotaUsage($account),
                    'spam_filter' => $account->spam_filter,
                    'antivirus' => $account->antivirus,
                    'active' => $account->active,
                    'aliases_count' => $account->aliases->count(),
                    'has_autoresponder' => $account->autoresponder?->enabled ?? false,
                    'created_at' => $account->created_at,
                ];
            }),
        ]);
    }

    /**
     * Create email account.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/accounts",
     *     tags={"Email"},
     *     summary="Create email account",
     *     @OA\RequestBody(required=true),
     *     @OA\Response(response=201, description="Email account created")
     * )
     */
    public function createAccount(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        try {
            $account = $this->emailService->createEmailAccount($site, $request->all());

            return response()->json([
                'message' => 'Email account created successfully',
                'account' => [
                    'id' => $account->id,
                    'email' => $account->email,
                    'quota_mb' => $account->quota_mb,
                    'webmail_url' => "https://mail.{$site->domain}",
                    'imap' => [
                        'server' => "mail.{$site->domain}",
                        'port' => 993,
                        'ssl' => true,
                    ],
                    'smtp' => [
                        'server' => "mail.{$site->domain}",
                        'port' => 587,
                        'ssl' => 'starttls',
                    ],
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update email account.
     *
     * @OA\Patch(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}",
     *     tags={"Email"},
     *     summary="Update email account",
     *     @OA\Response(response=200, description="Account updated")
     * )
     */
    public function updateAccount(Request $request, string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        try {
            $updated = $this->emailService->updateEmailAccount($account, $request->all());

            return response()->json([
                'message' => 'Email account updated successfully',
                'account' => $updated,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete email account.
     *
     * @OA\Delete(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}",
     *     tags={"Email"},
     *     summary="Delete email account",
     *     @OA\Response(response=200, description="Account deleted")
     * )
     */
    public function deleteAccount(string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        $this->emailService->deleteEmailAccount($account);

        return response()->json([
            'message' => 'Email account deleted successfully',
        ]);
    }

    /**
     * Get quota usage for email account.
     *
     * @OA\Get(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}/quota",
     *     tags={"Email"},
     *     summary="Get quota usage",
     *     @OA\Response(response=200, description="Quota information")
     * )
     */
    public function getQuota(string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        return response()->json($this->emailService->getQuotaUsage($account));
    }

    /**
     * List email forwarders.
     *
     * @OA\Get(
     *     path="/api/sites/{site_id}/email/forwarders",
     *     tags={"Email"},
     *     summary="List email forwarders",
     *     @OA\Response(response=200, description="List of forwarders")
     * )
     */
    public function indexForwarders(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $forwarders = $this->emailService->getForwardersBySite($site);

        return response()->json(['forwarders' => $forwarders]);
    }

    /**
     * Create email forwarder.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/forwarders",
     *     tags={"Email"},
     *     summary="Create email forwarder",
     *     @OA\Response(response=201, description="Forwarder created")
     * )
     */
    public function createForwarder(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        try {
            $forwarder = $this->emailService->createForwarder($site, $request->all());

            return response()->json([
                'message' => 'Email forwarder created successfully',
                'forwarder' => $forwarder,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete email forwarder.
     *
     * @OA\Delete(
     *     path="/api/sites/{site_id}/email/forwarders/{forwarder_id}",
     *     tags={"Email"},
     *     summary="Delete email forwarder",
     *     @OA\Response(response=200, description="Forwarder deleted")
     * )
     */
    public function deleteForwarder(string $site_id, int $forwarder_id): JsonResponse
    {
        $forwarder = EmailForwarder::where('id', $forwarder_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        $this->emailService->deleteForwarder($forwarder);

        return response()->json([
            'message' => 'Email forwarder deleted successfully',
        ]);
    }

    /**
     * List email aliases for account.
     *
     * @OA\Get(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}/aliases",
     *     tags={"Email"},
     *     summary="List email aliases",
     *     @OA\Response(response=200, description="List of aliases")
     * )
     */
    public function indexAliases(string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->with('aliases')
            ->firstOrFail();

        return response()->json(['aliases' => $account->aliases]);
    }

    /**
     * Create email alias.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}/aliases",
     *     tags={"Email"},
     *     summary="Create email alias",
     *     @OA\Response(response=201, description="Alias created")
     * )
     */
    public function createAlias(Request $request, string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        try {
            $alias = $this->emailService->createAlias($account, $request->input('alias'));

            return response()->json([
                'message' => 'Email alias created successfully',
                'alias' => $alias,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete email alias.
     *
     * @OA\Delete(
     *     path="/api/sites/{site_id}/email/aliases/{alias_id}",
     *     tags={"Email"},
     *     summary="Delete email alias",
     *     @OA\Response(response=200, description="Alias deleted")
     * )
     */
    public function deleteAlias(string $site_id, int $alias_id): JsonResponse
    {
        $alias = EmailAlias::where('id', $alias_id)
            ->whereHas('emailAccount.site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        $this->emailService->deleteAlias($alias);

        return response()->json([
            'message' => 'Email alias deleted successfully',
        ]);
    }

    /**
     * Setup DKIM for domain.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/dkim",
     *     tags={"Email"},
     *     summary="Setup DKIM",
     *     @OA\Response(response=200, description="DKIM setup initiated")
     * )
     */
    public function setupDKIM(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $selector = $request->input('selector', 'default');

        $dkimKey = $this->emailService->setupDKIM($site, $selector);

        return response()->json([
            'message' => 'DKIM setup initiated. Check back in a few minutes.',
            'dns_record' => [
                'type' => 'TXT',
                'name' => $dkimKey->dns_record_name,
                'value' => $dkimKey->dns_record_value,
            ],
        ]);
    }

    /**
     * Generate SPF record.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/spf",
     *     tags={"Email"},
     *     summary="Generate SPF record",
     *     @OA\Response(response=200, description="SPF record generated")
     * )
     */
    public function generateSPF(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $spf = $this->emailService->generateSPFRecord($site, $request->all());

        return response()->json([
            'dns_record' => [
                'type' => 'TXT',
                'name' => '@',
                'value' => $spf,
            ],
        ]);
    }

    /**
     * Generate DMARC record.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/dmarc",
     *     tags={"Email"},
     *     summary="Generate DMARC record",
     *     @OA\Response(response=200, description="DMARC record generated")
     * )
     */
    public function generateDMARC(Request $request, string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $dmarc = $this->emailService->generateDMARCRecord($site, $request->all());

        return response()->json([
            'dns_record' => [
                'type' => 'TXT',
                'name' => '_dmarc',
                'value' => $dmarc,
            ],
        ]);
    }

    /**
     * Set autoresponder.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}/autoresponder",
     *     tags={"Email"},
     *     summary="Set autoresponder",
     *     @OA\Response(response=200, description="Autoresponder configured")
     * )
     */
    public function setAutoresponder(Request $request, string $site_id, int $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->whereHas('site', fn($q) => $q->where('site_id', $site_id))
            ->firstOrFail();

        try {
            $autoresponder = $this->emailService->setAutoresponder($account, $request->all());

            return response()->json([
                'message' => 'Autoresponder configured successfully',
                'autoresponder' => $autoresponder,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get email statistics for site.
     *
     * @OA\Get(
     *     path="/api/sites/{site_id}/email/statistics",
     *     tags={"Email"},
     *     summary="Get email statistics",
     *     @OA\Response(response=200, description="Email statistics")
     * )
     */
    public function getStatistics(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();
        $stats = $this->emailService->getSiteStatistics($site);

        return response()->json($stats);
    }

    /**
     * Install Roundcube webmail.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/webmail/install",
     *     summary="Install Roundcube webmail",
     *     tags={"Email"},
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=202, description="Webmail installation queued"),
     *     @OA\Response(response=404, description="Site not found")
     * )
     */
    public function installWebmail(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->firstOrFail();

        $this->emailService->installRoundcube($site);

        return response()->json([
            'message' => 'Webmail installation queued',
            'webmail_url' => "https://{$site->domain}/webmail",
        ], 202);
    }

    /**
     * Get webmail auto-login URL.
     *
     * @OA\Post(
     *     path="/api/sites/{site_id}/email/accounts/{account_id}/webmail",
     *     summary="Get webmail auto-login URL",
     *     tags={"Email"},
     *     @OA\Parameter(name="site_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="account_id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", description="Account password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Webmail URL generated"),
     *     @OA\Response(response=404, description="Account not found")
     * )
     */
    public function getWebmailUrl(Request $request, string $site_id, string $account_id): JsonResponse
    {
        $account = EmailAccount::where('id', $account_id)
            ->where('site_id', $site_id)
            ->firstOrFail();

        $request->validate([
            'password' => 'required|string',
        ]);

        $webmailUrl = $this->emailService->getWebmailUrl($account, $request->password);

        return response()->json([
            'message' => 'Webmail URL generated',
            'data' => [
                'webmail_url' => $webmailUrl,
                'expires_in' => 300, // 5 minutes
            ],
        ]);
    }
}
