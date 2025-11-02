<?php
// app/Foundation/ContractManager/Contracts/ContractServiceInterface.php

namespace App\Foundation\ContractManager\Contracts;

use App\Models\Contract;
use App\Models\Hostname;
use Illuminate\Database\Eloquent\Collection;

interface ContractServiceInterface
{
    /**
     * Create a new contract
     */
    public function create(array $data): Contract;

    /**
     * Create a contract between two entities
     */
    public function createBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId,
        array $additionalData = []
    ): Contract;

    /**
     * Update a contract
     */
    public function update(int|Contract $contractId, array $data): Contract;

    /**
     * Update contract between two entities
     */
    public function updateBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId,
        array $data
    ): ?Contract;

    /**
     * Get contracts for current hostname with relationships
     */
    public function getMyContracts(?Hostname $hostname = null): Collection|\Illuminate\Support\Collection;

    /**
     * Get contracts between two entities
     */
    public function getContractsBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId
    ): Collection;

    /**
     * Get contract between requester based on supplier id
     */
    public function getContractWithSupplier(
        string $receiverType,
        int $receiverId,
        ?Hostname $hostname = null
    ): ?Contract;

    /**
     * Get contract between requester based on supplier connection
     */
    public function getContractWithSupplierByConnection(
        string $receiverType,
        string $receiverConnection,
        ?Hostname $hostname = null
    ): ?Contract;

    /**
     * Get supplier contract (type 1) with receiver connection 'cec' and requester ID as tenant ID
     */
    public function getSupplierContractByTenantId(
        int $tenantId,
        ?Hostname $hostname = null
    ): ?Contract;

    /**
     * Get a contract with receiver connection as tenant, receiver id as tenant, and requester id as company.
     */
    public function getContractWithCompany(
        $receiver_connection,
        $receiver_id,
        $requester_id,
    ): ?Contract;

    /**
     * Update or migrate tenant supplier contract with new structure
     */
    public function updateOrMigrateSupplierContract(
        int $tenantId,
        string $tenantConnection,
        array $contractData,
        bool $canRequestQuotation,
        array $additionalData = []
    ): Contract;

    /**
     * Check if current hostname has contract with another entity
     */
    public function hasContractWith(string $entityType, int $entityId, ?Hostname $hostname = null): bool;

    /**
     * Check if contract exists between two entities
     */
    public function exists(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId
    ): bool;

    /**
     * Get active contracts for current hostname
     */
    public function active(?Hostname $hostname = null): Collection;

    /**
     * Set contract active status
     */
    public function setActive(int $contractId, bool $active = true): Contract;

    /**
     * Update contract status (alias for setActive)
     */
    public function updateStatus(int $contractId, bool $active): Contract;

    /**
     * Get contracts with specific custom field values
     */
    public function getContractWithSpecificJson(array $customFieldsFilter, ?Hostname $hostname = null): Collection;

    /**
     * Get contract policy/rules for specific entity
     */
    public function getContractPolicy(string $entityType, int $entityId): ?object;

    /**
     * Create contract for current tenant (helper method)
     */
    public function createForCurrentTenant(
        string $receiverType,
        int $receiverId,
        array $additionalData = []
    ): Contract;

    /**
     * Check if current tenant can contract with target entity
     */
    public function canContractWith(string $entityType, int $entityId): bool;

    /**
     * Check if entity can request quotations from supplier
     */
    public function canRequestQuotation(
        string $requesterType,
        int $requesterId,
        string $supplierType,
        int $supplierId
    ): bool;

    /**
     * Create supplier contract (reseller becoming supplier)
     */
    public function createSupplierContract(
        int $requesterId,
        string $requesterConnection,
        array $contractData,
        bool $canRequestQuotation,
        array $additionalData = []
    ): Contract;


    /**
     * Check if entity is a supplier (has supplier contract)
     */
    public function isSupplier(string $entityType, int $entityId): bool;

    /**
     * Get supplier contract data
     */
    public function getSupplierContract(string $entityType, int $entityId): ?object;

    /**
     * Calculate commission for supplier based on runs
     */
    public function calculateCommission(string $entityType, int $entityId, float $amount): float;

    /**
     * Get contract policies for the current hostname/entity from their perspective
     */
    public function getMyPolicies(?Hostname $hostname = null): ?object;

    /**
     * Get contract policies for multiple entities that the current hostname has contracts with
     */
    public function getMyPartnerPolicies(?Hostname $hostname = null): array;

    /**
     * Get condensed policy information for the current entity
     */
    public function getMyCondensedPolicies(?Hostname $hostname = null): ?object;

    /**
     * Get specific policy aspects for the current entity
     */
    public function getMyPolicyAspect(string $aspect, ?Hostname $hostname = null): mixed;
}
