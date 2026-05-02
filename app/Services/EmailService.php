<?php

namespace App\Services;

use App\Jobs\Email\CreateAliasSSH;
use App\Jobs\Email\CreateEmailAccountSSH;
use App\Jobs\Email\CreateForwarderSSH;
use App\Jobs\Email\DeleteAliasSSH;
use App\Jobs\Email\DeleteEmailAccountSSH;
use App\Jobs\Email\DeleteForwarderSSH;
use App\Jobs\Email\InstallRoundcubeSSH;
use App\Jobs\Email\SetupDKIMSSH;
use App\Jobs\Email\UpdateAutoResponderSSH;
use App\Jobs\Email\UpdateEmailFiltersSSH;
use App\Jobs\Email\UpdateEmailPasswordSSH;
use App\Jobs\Email\UpdateEmailQuotaSSH;
use App\Models\EmailAccount;
use App\Models\EmailAlias;
use App\Models\EmailAutoresponder;
use App\Models\EmailDkimKey;
use App\Models\EmailForwarder;
use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmailService
{
    /**
     * Create a new email account.
     *
     * @throws ValidationException
     */
    public function createEmailAccount(Site $site, array $data): EmailAccount
    {
        // Validate input
        $validator = Validator::make($data, [
            'email' => 'required|email|unique:email_accounts,email',
            'password' => 'required|string|min:8',
            'quota_mb' => 'nullable|integer|min:0',
            'spam_filter' => 'nullable|boolean',
            'spam_score' => 'nullable|numeric|min:0|max:10',
            'antivirus' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate email domain belongs to site
        if (! $this->validateEmailDomain($data['email'], $site)) {
            throw ValidationException::withMessages([
                'email' => ['Email domain does not belong to this site.'],
            ]);
        }

        // Create email account
        $account = EmailAccount::create([
            'site_id' => $site->id,
            'email' => $data['email'],
            'password' => $data['password'], // Will be auto-hashed by model
            'quota_mb' => $data['quota_mb'] ?? 1024,
            'spam_filter' => $data['spam_filter'] ?? true,
            'spam_score' => $data['spam_score'] ?? 5.0,
            'antivirus' => $data['antivirus'] ?? true,
            'active' => true,
        ]);

        // Dispatch SSH job to create mailbox
        CreateEmailAccountSSH::dispatch($site->server, $account);

        return $account;
    }

    /**
     * Update email account settings.
     *
     * @throws ValidationException
     */
    public function updateEmailAccount(EmailAccount $account, array $data): EmailAccount
    {
        $validator = Validator::make($data, [
            'password' => 'nullable|string|min:8',
            'quota_mb' => 'nullable|integer|min:0',
            'spam_filter' => 'nullable|boolean',
            'spam_score' => 'nullable|numeric|min:0|max:10',
            'antivirus' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Update password if provided
        if (isset($data['password'])) {
            $account->password = $data['password'];
            UpdateEmailPasswordSSH::dispatch($account->site->server, $account);
        }

        // Update quota if changed
        if (isset($data['quota_mb']) && $data['quota_mb'] !== $account->quota_mb) {
            $account->quota_mb = $data['quota_mb'];
            UpdateEmailQuotaSSH::dispatch($account->site->server, $account);
        }

        // Propagate antispam/antivirus changes to the server
        $filterFields = ['spam_filter', 'spam_score', 'antivirus'];
        $filterChanged = collect($filterFields)->contains(
            fn ($field) => isset($data[$field]) && $data[$field] != $account->$field
        );

        // Update other settings
        $account->fill($data);
        $account->save();

        if ($filterChanged) {
            UpdateEmailFiltersSSH::dispatch($account->site->server, $account);
        }

        return $account;
    }

    /**
     * Delete email account.
     */
    public function deleteEmailAccount(EmailAccount $account): bool
    {
        // Dispatch SSH job to remove mailbox
        DeleteEmailAccountSSH::dispatch($account->site->server, $account);

        // Delete related records (cascade will handle this)
        return $account->delete();
    }

    /**
     * Create email forwarder.
     *
     * @throws ValidationException
     */
    public function createForwarder(Site $site, array $data): EmailForwarder
    {
        $validator = Validator::make($data, [
            'source' => 'required|string',
            'destination' => 'required|string',
            'keep_copy' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate source domain
        if (! str_starts_with($data['source'], '@')) {
            // Not a catch-all, validate full email
            if (! $this->validateEmailDomain($data['source'], $site)) {
                throw ValidationException::withMessages([
                    'source' => ['Source email domain does not belong to this site.'],
                ]);
            }
        }

        // Create forwarder
        $forwarder = EmailForwarder::create([
            'site_id' => $site->id,
            'source' => $data['source'],
            'destination' => $data['destination'],
            'keep_copy' => $data['keep_copy'] ?? false,
            'active' => true,
        ]);

        // Dispatch SSH job
        CreateForwarderSSH::dispatch($site->server, $forwarder);

        return $forwarder;
    }

    /**
     * Delete email forwarder.
     */
    public function deleteForwarder(EmailForwarder $forwarder): bool
    {
        DeleteForwarderSSH::dispatch($forwarder->site->server, $forwarder);

        return $forwarder->delete();
    }

    /**
     * Create email alias.
     *
     * @throws ValidationException
     */
    public function createAlias(EmailAccount $account, string $alias): EmailAlias
    {
        $validator = Validator::make(['alias' => $alias], [
            'alias' => 'required|email|unique:email_aliases,alias',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate alias domain belongs to site
        if (! $this->validateEmailDomain($alias, $account->site)) {
            throw ValidationException::withMessages([
                'alias' => ['Alias domain does not belong to this site.'],
            ]);
        }

        // Create alias
        $emailAlias = EmailAlias::create([
            'email_account_id' => $account->id,
            'alias' => $alias,
        ]);

        // Dispatch SSH job
        CreateAliasSSH::dispatch($account->site->server, $emailAlias);

        return $emailAlias;
    }

    /**
     * Delete email alias.
     */
    public function deleteAlias(EmailAlias $alias): bool
    {
        DeleteAliasSSH::dispatch($alias->emailAccount->site->server, $alias);

        return $alias->delete();
    }

    /**
     * Setup DKIM for domain.
     */
    public function setupDKIM(Site $site, ?string $selector = 'default'): EmailDkimKey
    {
        // Check if DKIM already exists
        $existingKey = EmailDkimKey::where('site_id', $site->id)
            ->where('domain', $site->domain)
            ->where('selector', $selector)
            ->first();

        if ($existingKey) {
            return $existingKey;
        }

        // Create placeholder (SSH job will generate actual keys)
        $dkimKey = EmailDkimKey::create([
            'site_id' => $site->id,
            'domain' => $site->domain,
            'selector' => $selector,
            'private_key' => '', // Will be filled by SSH job
            'public_key' => '', // Will be filled by SSH job
            'active' => false,
        ]);

        // Dispatch SSH job to generate keys
        SetupDKIMSSH::dispatch($site->server, $dkimKey);

        return $dkimKey;
    }

    /**
     * Generate SPF record for domain.
     */
    public function generateSPFRecord(Site $site, array $config = []): string
    {
        $ipAddresses = $config['ip_addresses'] ?? array_filter([$site->server?->ip]);
        $includeDomains = $config['include_domains'] ?? [];
        $policy = $config['policy'] ?? '~all';

        $spf = 'v=spf1';

        // Add IP addresses
        foreach ($ipAddresses as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $spf .= " ip4:{$ip}";
            } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $spf .= " ip6:{$ip}";
            }
        }

        // Add include domains
        foreach ($includeDomains as $domain) {
            $spf .= " include:{$domain}";
        }

        // Add policy
        $spf .= " {$policy}";

        return $spf;
    }

    /**
     * Generate DMARC record for domain.
     */
    public function generateDMARCRecord(Site $site, array $config = []): string
    {
        $policy = $config['policy'] ?? 'quarantine';
        $subdomainPolicy = $config['subdomain_policy'] ?? 'quarantine';
        $reportEmail = $config['report_email'] ?? null;
        $percentage = $config['percentage'] ?? 100;

        $dmarc = "v=DMARC1; p={$policy}; sp={$subdomainPolicy}; pct={$percentage}";

        if ($reportEmail) {
            $dmarc .= "; rua=mailto:{$reportEmail}";
        }

        return $dmarc;
    }

    /**
     * Set or update autoresponder.
     *
     * @throws ValidationException
     */
    public function setAutoresponder(EmailAccount $account, array $data): EmailAutoresponder
    {
        $validator = Validator::make($data, [
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Update or create autoresponder
        $autoresponder = $account->autoresponder()->updateOrCreate(
            ['email_account_id' => $account->id],
            $data
        );

        // Dispatch SSH job to update Sieve script
        UpdateAutoResponderSSH::dispatch($account->site->server, $autoresponder);

        return $autoresponder;
    }

    /**
     * Get quota usage for email account.
     */
    public function getQuotaUsage(EmailAccount $account): array
    {
        $usage = $account->quotaUsage;

        return [
            'quota_mb' => $account->quota_mb,
            'used_mb' => $usage?->used_mb ?? 0,
            'percentage' => $account->getQuotaPercentage(),
            'messages_count' => $usage?->messages_count ?? 0,
            'quota_warning' => $usage?->isWarning() ?? false,
            'quota_critical' => $usage?->isCritical() ?? false,
            'is_exceeded' => $account->isQuotaExceeded(),
        ];
    }

    /**
     * Validate email domain belongs to site.
     */
    protected function validateEmailDomain(string $email, Site $site): bool
    {
        $emailDomain = explode('@', $email)[1] ?? '';

        // Check main domain
        if ($emailDomain === $site->domain) {
            return true;
        }

        // Check aliases
        $aliases = $site->aliases()->pluck('domain')->toArray();

        return in_array($emailDomain, $aliases);
    }

    /**
     * Get all email accounts for a site.
     *
     * @return Collection
     */
    public function getAccountsBySite(Site $site)
    {
        return EmailAccount::where('site_id', $site->id)
            ->with(['aliases', 'autoresponder', 'quotaUsage'])
            ->orderBy('email')
            ->get();
    }

    /**
     * Get all forwarders for a site.
     *
     * @return Collection
     */
    public function getForwardersBySite(Site $site)
    {
        return EmailForwarder::where('site_id', $site->id)
            ->orderBy('source')
            ->get();
    }

    /**
     * Search email accounts.
     *
     * @return Collection
     */
    public function searchAccounts(Site $site, string $query)
    {
        return EmailAccount::where('site_id', $site->id)
            ->where('email', 'like', "%{$query}%")
            ->with(['aliases', 'quotaUsage'])
            ->get();
    }

    /**
     * Get email statistics for site.
     */
    public function getSiteStatistics(Site $site): array
    {
        $accounts = EmailAccount::where('site_id', $site->id);
        $forwarders = EmailForwarder::where('site_id', $site->id);

        return [
            'total_accounts' => $accounts->count(),
            'active_accounts' => $accounts->where('active', true)->count(),
            'total_forwarders' => $forwarders->count(),
            'total_quota_mb' => $accounts->sum('quota_mb'),
            'total_used_mb' => $accounts->with('quotaUsage')->get()
                ->sum(fn ($a) => $a->quotaUsage?->used_mb ?? 0),
            'accounts_over_quota' => $accounts->get()
                ->filter(fn ($a) => $a->isQuotaExceeded())
                ->count(),
        ];
    }

    /**
     * Install Roundcube webmail for site.
     */
    public function installRoundcube(Site $site): void
    {
        InstallRoundcubeSSH::dispatch($site);
    }

    /**
     * Generate Roundcube auto-login token.
     */
    public function generateWebmailToken(EmailAccount $account, string $password): string
    {
        $data = [
            'email' => $account->email,
            'password' => $password,
            'timestamp' => now()->timestamp,
        ];

        $token = base64_encode(json_encode($data));

        // Encrypt token for security
        return encrypt($token);
    }

    /**
     * Get webmail URL with auto-login token.
     */
    public function getWebmailUrl(EmailAccount $account, string $password): string
    {
        $token = $this->generateWebmailToken($account, $password);
        $domain = $account->site->domain;

        return "https://{$domain}/webmail?autologin={$token}";
    }
}
