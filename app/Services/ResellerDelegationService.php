<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResellerDelegationService
{
    public function assignCustomerToReseller(User $reseller, User $customer): void
    {
        if (! $reseller->hasRole('Reseller')) {
            throw new \InvalidArgumentException('Target user is not a Reseller');
        }

        if (! $customer->hasRole('Customer')) {
            throw new \InvalidArgumentException('Target user is not a Customer');
        }

        DB::table('reseller_customer')->updateOrInsert([
            'reseller_id' => $reseller->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function removeCustomerFromReseller(User $reseller, User $customer): void
    {
        DB::table('reseller_customer')
            ->where('reseller_id', $reseller->id)
            ->where('customer_id', $customer->id)
            ->delete();
    }

    public function getResellerCustomers(User $reseller): array
    {
        $ids = DB::table('reseller_customer')
            ->where('reseller_id', $reseller->id)
            ->pluck('customer_id')
            ->toArray();

        return User::whereIn('id', $ids)->get()->toArray();
    }

    public function getCustomerResellers(User $customer): array
    {
        $ids = DB::table('reseller_customer')
            ->where('customer_id', $customer->id)
            ->pluck('reseller_id')
            ->toArray();

        return User::whereIn('id', $ids)->get()->toArray();
    }

    public function getResellerStats(User $reseller): array
    {
        $customerIds = DB::table('reseller_customer')
            ->where('reseller_id', $reseller->id)
            ->pluck('customer_id')
            ->toArray();

        $customers = User::whereIn('id', $customerIds)->get();
        $customerUserIds = $customers->pluck('id')->toArray();

        $serverCount = Server::whereIn('user_id', $customerUserIds)->count();
        $siteCount = Site::whereHas('server', fn ($q) => $q->whereIn('user_id', $customerUserIds))->count();

        return [
            'reseller_id' => $reseller->id,
            'reseller_name' => $reseller->name,
            'customer_count' => count($customerIds),
            'customers' => $customers->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
            'total_servers' => $serverCount,
            'total_sites' => $siteCount,
        ];
    }

    public function canResellerManageServer(User $reseller, Server $server): bool
    {
        $customerIds = DB::table('reseller_customer')
            ->where('reseller_id', $reseller->id)
            ->pluck('customer_id')
            ->toArray();

        return in_array($server->user_id, $customerIds);
    }

    public function canResellerManageSite(User $reseller, Site $site): bool
    {
        return $this->canResellerManageServer($reseller, $site->server);
    }
}
