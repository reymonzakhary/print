<?php

namespace App\Foundation\ContractManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Contract create(array $data)
 * @method static \App\Models\Contract createBetween(string $requesterType, int $requesterId, string $receiverType, int $receiverId, array $additionalData = [])
 * @method static \App\Models\Contract createWithExternal(string $requesterType, int $requesterId, string $receiverType, int $receiverId, array $additionalData = [])
 * @method static \App\Models\Contract update(int|\App\Models\Contract $contractId, array $data)
 * @method static \App\Models\Contract|null updateBetween(string $requesterType, int $requesterId, string $receiverType, int $receiverId, array $data)
 * @method static \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection getMyContracts(?\App\Models\Hostname $hostname = null)
 * @method static \Illuminate\Database\Eloquent\Collection getContractsBetween(string $requesterType, int $requesterId, string $receiverType, int $receiverId)
 * @method static \Illuminate\Database\Eloquent\Collection getContractWithSupplier(string $receiverType, int $receiverId , ?\App\Models\Hostname $hostname = null)
 * @method static \App\Models\Contract|null getContractWithSupplierByConnection(string $receiverType, string $receiverConnection, ?\App\Models\Hostname $hostname = null)
 * @method static \App\Models\Contract|null getSupplierContractByTenantId(int $tenantId, ?\App\Models\Hostname $hostname = null)
 * @method static \App\Models\Contract updateOrMigrateSupplierContract(int $tenantId, string $tenantConnection, array $contractData, bool $canRequestQuotation , array $additionalData = [])
 * @method static bool hasContractWith(string $entityType, int $entityId, ?\App\Models\Hostname $hostname = null)
 * @method static bool exists(string $requesterType, int $requesterId, string $receiverType, int $receiverId)
 * @method static \Illuminate\Database\Eloquent\Collection active(?\App\Models\Hostname $hostname = null)
 * @method static \App\Models\Contract setActive(int $contractId, bool $active = true)
 * @method static \App\Models\Contract updateStatus(int $contractId, bool $active)
 * @method static \Illuminate\Database\Eloquent\Collection getContractWithSpecificJson(array $customFieldsFilter, ?\App\Models\Hostname $hostname = null)
 * @method static object|null getContractPolicy(string $entityType, int $entityId)
 * @method static object|null getContractWithCompany(string $entityType, int $entityId, int $requester_id)
 * @method static \App\Models\Contract createForCurrentTenant(string $receiverType, int $receiverId, array $additionalData = [])
 * @method static bool canContractWith(string $entityType, int $entityId)
 * @method static bool canRequestQuotation(string $requesterType, int $requesterId, string $supplierType, int $supplierId)
 * @method static \App\Models\Contract createSupplierContract(int $requesterId, string $requesterConnection, array $contractData, bool $canRequestQuotation , array $additionalData = [])
 * @method static bool isSupplier(string $entityType, int $entityId)
 * @method static object|null getSupplierContract(string $entityType, int $entityId)
 * @method static float calculateCommission(string $entityType, int $entityId, float $amount)
 * @method static \App\Models\Contract acceptSupplierContract(int|\App\Models\Contract $contractId, array $contractData, bool $canRequestQuotation = false)
 * @method static array getReceiverConnections(?\App\Models\Hostname $hostname = null, array $filters = [])
 * @method static object|null getMyPolicies(?\App\Models\Hostname $hostname = null)
 * @method static array getMyPartnerPolicies(?\App\Models\Hostname $hostname = null)
 * @method static object|null getMyCondensedPolicies(?\App\Models\Hostname $hostname = null)
 * @method static mixed getMyPolicyAspect(string $aspect, ?\App\Models\Hostname $hostname = null)
 *
 */

class ContractManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'contract-manager';
    }
}
