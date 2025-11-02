<?php

namespace App\Foundation\ContractManager;

use App\Enums\ContractType;
use App\Enums\Status;
use App\Foundation\ContractManager\Contracts\ContractServiceInterface;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Hostname;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContractService implements ContractServiceInterface
{

    const string SYSTEM_CONNECTION = 'cec';
    /**
     * Get contract policies for the current hostname/entity from their perspective
     *
     * @param Hostname|null $hostname The hostname entity to get policies for (optional)
     * @return object|null The policy details from the current entity's perspective, null if no contracts
     */
    public function getMyPolicies(?Hostname $hostname = null): ?object
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();

        if (!$currentHostname) {
            return null;
        }

        return $this->getContractPolicy(Hostname::class, $currentHostname->id);
    }

    /**
     * Get contract policies for multiple entities that the current hostname has contracts with
     *
     * @param Hostname|null $hostname The hostname entity to get policies for (optional)
     * @return array Array of policies grouped by partner entity
     */
    public function getMyPartnerPolicies(?Hostname $hostname = null): array
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();

        if (!$currentHostname) {
            return [];
        }

        $myContracts = $this->getMyContracts($currentHostname);
        $partnerPolicies = [];

        foreach ($myContracts as $contract) {
            // Determine the partner (the entity we have a contract with)
            if ($contract->requester_type === Hostname::class && $contract->requester_id === $currentHostname->id) {
                // We are the requester, so the receiver is our partner
                $partnerType = $contract->receiver_type;
                $partnerId = $contract->receiver_id;
                $ourRole = 'requester';
                $partnerRole = 'receiver';
            } else {
                // We are the receiver, so the requester is our partner
                $partnerType = $contract->requester_type;
                $partnerId = $contract->requester_id;
                $ourRole = 'receiver';
                $partnerRole = 'requester';
            }

            $partnerKey = "{$partnerType}:{$partnerId}";

            // Get the partner's policy if we haven't already
            if (!isset($partnerPolicies[$partnerKey])) {
                $partnerPolicy = $this->getContractPolicy($partnerType, $partnerId);

                if ($partnerPolicy) {
                    $partnerPolicies[$partnerKey] = [
                        'partner_entity_type' => $partnerType,
                        'partner_entity_id' => $partnerId,
                        'our_role' => $ourRole,
                        'partner_role' => $partnerRole,
                        'policy' => $partnerPolicy,
                        'contracts' => []
                    ];
                }
            }

            // Add this contract to the partner's policy
            if (isset($partnerPolicies[$partnerKey])) {
                $partnerPolicies[$partnerKey]['contracts'][] = [
                    'contract_id' => $contract->id,
                    'contract_type' => $contract->type,
                    'active' => $contract->active,
                    'created_at' => $contract->created_at,
                    'activated_at' => $contract->activated_at,
                    'our_role_in_contract' => $ourRole
                ];
            }
        }

        return array_values($partnerPolicies);
    }

    /**
     * Get condensed policy information for the current entity
     *
     * @param Hostname|null $hostname The hostname entity to get policies for (optional)
     * @return object|null Condensed policy information focusing on capabilities and restrictions
     */
    public function getMyCondensedPolicies(?Hostname $hostname = null): ?object
    {
        $myPolicies = $this->getMyPolicies($hostname);

        if (!$myPolicies) {
            return null;
        }

        // Create a condensed version focusing on what the current entity can do
        $condensed = [
            'entity_info' => [
                'entity_type' => $myPolicies->entity_type,
                'entity_id' => $myPolicies->entity_id,
                'total_contracts' => $myPolicies->total_contracts,
                'active_contracts' => $myPolicies->active_contracts
            ],
            'capabilities' => [
                'can_request_quotations' => $myPolicies->request_open_quotation ?? false,
                'is_supplier' => isset($myPolicies->supplier_contract) && $myPolicies->supplier_contract['is_supplier'] ?? false,
                'has_supplier_contract' => isset($myPolicies->supplier_contract),
                'contract_types' => $myPolicies->contract_types ?? []
            ],
            'permissions' => $myPolicies->permissions ?? [],
            'restrictions' => $myPolicies->restrictions ?? [],
            'partners_summary' => [
                'total_partners' => count($myPolicies->partners ?? []),
                'partner_types' => collect($myPolicies->partners ?? [])->pluck('type')->unique()->values()->toArray(),
                'roles' => collect($myPolicies->partners ?? [])->pluck('role')->unique()->values()->toArray()
            ],
            'discount_info' => [
                'has_general_discounts' => !empty($myPolicies->discount['general'] ?? []),
                'has_category_discounts' => !empty($myPolicies->discount['categories'] ?? []),
                'total_discount_contracts' => count($myPolicies->discount['general'] ?? []) + count($myPolicies->discount['categories'] ?? [])
            ],
            'categories_info' => [
                'total_categories' => count($myPolicies->categories ?? []),
                'category_ids' => collect($myPolicies->categories ?? [])->pluck('category_id')->unique()->values()->toArray()
            ]
        ];

        // Add supplier-specific information if applicable
        if ($condensed['capabilities']['has_supplier_contract'] && isset($myPolicies->supplier_contract)) {
            $supplierContract = $myPolicies->supplier_contract;
            $condensed['supplier_info'] = [
                'contract_id' => $supplierContract['contract_id'] ?? null,
                'payment_terms' => $supplierContract['payment_terms'] ?? null,
                'has_commission_runs' => !empty($supplierContract['runs'] ?? []),
                'commission_runs_count' => count($supplierContract['runs'] ?? []),
                'has_exchange_rates' => !empty($supplierContract['exchange_rate'] ?? []),
                'contract_created_at' => $supplierContract['created_at'] ?? null,
                'contract_activated_at' => $supplierContract['activated_at'] ?? null
            ];
        }

        return (object) $condensed;
    }

    /**
     * Get specific policy aspects for the current entity
     *
     * @param string $aspect The aspect to retrieve (permissions, restrictions, discounts, categories, partners, supplier)
     * @param Hostname|null $hostname The hostname entity to get policies for (optional)
     * @return mixed The specific aspect data or null if not found
     */
    public function getMyPolicyAspect(string $aspect, ?Hostname $hostname = null): mixed
    {
        $myPolicies = $this->getMyPolicies($hostname);

        if (!$myPolicies) {
            return null;
        }

        return match($aspect) {
            'permissions' => $myPolicies->permissions ?? [],
            'restrictions' => $myPolicies->restrictions ?? [],
            'discounts', 'discount' => $myPolicies->discount ?? [],
            'categories' => $myPolicies->categories ?? [],
            'partners' => $myPolicies->partners ?? [],
            'supplier', 'supplier_contract' => $myPolicies->supplier_contract ?? null,
            'capabilities' => [
                'can_request_quotations' => $myPolicies->request_open_quotation ?? false,
                'is_supplier' => isset($myPolicies->supplier_contract) && $myPolicies->supplier_contract['is_supplier'] ?? false,
            ],
            default => null
        };
    }

    /**
     * Create a new contract based on the provided data
     *
     * @param array $data The data for creating the contract
     * @return Contract The created contract object
     * @throws ValidationException
     * @throws Exception
     */
    public function create(
        array $data
    ): Contract
    {
        $this->validateContractData($data);
        $this->validateContractRules($data);
        $this->checkContractExists($data);

        // Set default type if not provided
        if (!isset($data['type'])) {
            $data['type'] = ContractType::EXTERNAL;
        }

        // Set connections if provided
        if (isset($data['requester_connection'])) {
            $data['requester_connection'] = $this->getTenantConnection($data['requester_connection']);
        }

        if (isset($data['receiver_connection']) && $data['receiver_connection'] !== self::SYSTEM_CONNECTION) {
            $data['receiver_connection'] = $this->getTenantConnection($data['receiver_connection']);
        }

        return Contract::create($data);
    }

    /**
     * Create a contract between the current tenant and a target tenant
     *
     * @param string $receiverType The type of the target tenant
     * @param int $receiverId The ID of the target tenant
     * @param array $additionalData Additional data for the contract
     * @return Contract The created contract
     * @throws ValidationException If no current tenant is found
     */
    public function createForCurrentTenant(
        string $receiverType,
        int $receiverId,
        array $additionalData = []
    ): Contract
    {
        $currentHostname = $this->getCurrentHostname();

        if (!$currentHostname) {
            throw ValidationException::withMessages([
                'current_tenant' => ['No current tenant found. Cannot create contract.']
            ]);
        }

        return $this->createBetween(
            Hostname::class,
            $currentHostname->id,
            $receiverType,
            $receiverId,
            $additionalData
        );
    }

    /**
     * Determines if a contract can be established with a given entity.
     *
     * @param string $entityType The type of the entity
     * @param int $entityId The ID of the entity
     * @return bool Whether a contract can be established or not
     */
    public function canContractWith(
        string $entityType,
        int $entityId
    ): bool
    {
        $currentHostname = $this->getCurrentHostname();

        if (!$currentHostname) {
            return false;
        }

        try {
            $this->validateContractData([
                'requester_type' => Hostname::class,
                'requester_id' => $currentHostname->id,
                'receiver_type' => $entityType,
                'receiver_id' => $entityId,
            ]);

            $this->validateContractRules([
                'requester_type' => Hostname::class,
                'requester_id' => $currentHostname->id,
                'receiver_type' => $entityType,
                'receiver_id' => $entityId,
            ]);

            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * Creates a contract between two entities.
     *
     * @param string $requesterType The type of the requester entity
     * @param int $requesterId The ID of the requester entity
     * @param string $receiverType The type of the receiver entity
     * @param int $receiverId The ID of the receiver entity
     * @param array $additionalData Additional data for the contract creation
     * @return Contract The created contract
     * @throws ValidationException
     */
    public function createBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId,
        array $additionalData = []
    ): Contract {
        $data = array_merge([
            'requester_type' => $requesterType,
            'requester_id' => $requesterId,
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'type' => ContractType::INTERNAL,
            'custom_fields' => [
                'supplier' => false,
                'contract' => [
                    'discount' => [
                        'general' => [
                            'mode' => 'run',
                            'slots' => [],
                            'status' => false
                        ],
                        'categories' => [],
                    ],
                    'categories' => [],
                ],
                'permissions' => [],
                'canRequestQuotation' => false // TODO Change This After Discussion
            ]
        ], $additionalData);

        return $this->create($data);
    }



    /**
     * Creates a contract With An External Supplier.
     *
     * @param string $requesterType The type of the requester entity
     * @param int $requesterId The ID of the requester entity
     * @param string $receiverType The type of the receiver entity
     * @param int $receiverId The ID of the receiver entity
     * @param array $additionalData Additional data for the contract creation
     * @return Contract The created contract
     * @throws ValidationException
     */
    public function createWithExternal(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId,
        array $additionalData = []
    ): Contract {
        $payload = [
            'requester_type' => $requesterType,
            'requester_id' => $requesterId,
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'st' => Status::ACCEPTED->value,
            'active' => true,
            'activated_at' => Carbon::now()->toDateTimeString(),
            'type' => ContractType::EXTERNAL,
            'custom_fields' => [
                'supplier' => false,
                'contract' => [
                    'discount' => [
                        'general' => [
                            'mode' => 'run',
                            'slots' => [],
                            'status' => false
                        ],
                        'categories' => [],
                    ],
                    'categories' => [],
                ],
                'permissions' => [],
                'canRequestQuotation' => false,
                'auth' => []
            ]
        ];

        $data = array_merge_recursive_distinct($payload, $additionalData);

        return $this->create($data);
    }



    /**
     * Update a contract based on the provided contract ID or Contract object.
     *
     * @param int|Contract $contractId The ID of the contract or Contract object
     * @param array $data The data to update in the contract
     * @return Contract The updated contract
     * @throws Exception
     */
    public function update(
        int|Contract $contractId,
        array $data
    ): Contract
    {
        if ($contractId instanceof Contract) {
            $contract = $contractId;
        } else {
            $contract = Contract::findOrFail($contractId);
        }

        // Handle connections if provided
        if (isset($data['requester_connection'])) {
            $data['requester_connection'] = $this->getTenantConnection($data['requester_connection']);
        }

        if (isset($data['receiver_connection'])) {
            $data['receiver_connection'] = $this->getTenantConnection($data['receiver_connection']);
        }

        $contract->update($data);
        return $contract->fresh();
    }

    /**
     * Updates a contract between a requester and a receiver.
     *
     * @param string $requesterType The type of the requester
     * @param int $requesterId The ID of the requester
     * @param string $receiverType The type of the receiver
     * @param int $receiverId The ID of the receiver
     * @param array $data The data to update the contract with
     *
     * @return Contract|null The updated contract if found, null otherwise
     * @throws Exception
     */
    public function updateBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId,
        array $data
    ): ?Contract {
        $contract = $this->getContractsBetween($requesterType, $requesterId, $receiverType, $receiverId)->first();

        if (!$contract) {
            return null;
        }

        return $this->update($contract->id, $data);
    }

    /**
     * Get contracts where the current hostname is involved as a requester or receiver.
     *
     * @param Hostname|null $hostname The hostname entity to filter contracts on
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getMyContracts(
        ?Hostname $hostname = null
    ): Collection|\Illuminate\Support\Collection
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();

        if (!$currentHostname) {
            return collect();
        }

        // Get all unique entity types used in contracts to build dynamic joins
        $entityTypes = DB::connection($this->getSystemConnection())
            ->table('contracts')
            ->select('requester_type as type')
            ->union(
                DB::connection($this->getSystemConnection())
                    ->table('contracts')
                    ->select('receiver_type as type')
            )
            ->distinct()
            ->whereNotNull('type')
            ->pluck('type')
            ->toArray();

        // Build the query with dynamic joins
        $query = Contract::with(['requester', 'receiver'])
            ->select('contracts.*');

        $selectFields = [];

        // Add dynamic joins for each entity type
        foreach ($entityTypes as $entityType) {
            if (!$entityType) continue;

            $modelInfo = $this->getModelInfo($entityType);

            if ($modelInfo) {
                $tableName = $modelInfo['table'];
                $fields = $modelInfo['fields'];

                // Create unique aliases
                $requesterAlias = "req_{$tableName}";
                $receiverAlias = "rec_{$tableName}";

                // Add left joins for requester
                $query->leftJoin("{$tableName} as {$requesterAlias}", function($join) use ($entityType, $requesterAlias) {
                    $join->on('contracts.requester_id', '=', "{$requesterAlias}.id")
                        ->where('contracts.requester_type', '=', $entityType);
                });

                // Add left joins for receiver
                $query->leftJoin("{$tableName} as {$receiverAlias}", function($join) use ($entityType, $receiverAlias) {
                    $join->on('contracts.receiver_id', '=', "{$receiverAlias}.id")
                        ->where('contracts.receiver_type', '=', $entityType);
                });

                // Add select fields
                foreach ($fields as $field) {
                    $selectFields[] = "{$requesterAlias}.{$field} as requester_{$tableName}_{$field}";
                    $selectFields[] = "{$receiverAlias}.{$field} as receiver_{$tableName}_{$field}";
                }
            }
        }

        // Add the select fields
        if (!empty($selectFields)) {
            $query->addSelect($selectFields);
        }

        // Filter contracts where current hostname is involved (as requester or receiver)
        $query->where(function ($q) use ($currentHostname) {
            $q->where(function($subQuery) use ($currentHostname) {
                $subQuery->where('contracts.requester_id', $currentHostname->id)
                    ->where('contracts.requester_type', Hostname::class);
            })->orWhere(function ($subQuery) use ($currentHostname) {
                $subQuery->where('contracts.receiver_id', $currentHostname->id)
                    ->where('contracts.receiver_type', Hostname::class);
            });
        });

        return $query->get()->map(function ($contract) use ($currentHostname) {
            return $contract->setHostnameContext($currentHostname);
        });
    }

    /**
     * Get contracts between a requester and a receiver based on their types and IDs.
     *
     * @param string $requesterType The type of the requester
     * @param int $requesterId The ID of the requester
     * @param string $receiverType The type of the receiver
     * @param int $receiverId The ID of the receiver
     *
     * @return Collection A collection of contracts matching the criteria
     * @throws Exception
     */
    public function getContractsBetween(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId
    ): Collection
    {
        return Contract::where(function ($query) use ($requesterType, $requesterId, $receiverType, $receiverId) {
            // Direct match
            $query->where('requester_type', $requesterType)
                ->where('requester_id', $requesterId)
                ->where('receiver_type', $receiverType)
                ->where('receiver_id', $receiverId);
            // TODO Discuss if we want to allow bidirectional contracts , i think bidirectional is not needed to check because in this case we create 2 contracts

//                // Reverse match (bidirectional contracts)
//                ->orWhere(function ($subQuery) use ($requesterType, $requesterId, $receiverType, $receiverId) {
//                    $subQuery->where('requester_type', $receiverType)
//                        ->where('requester_id', $receiverId)
//                        ->where('receiver_type', $requesterType)
//                        ->where('receiver_id', $requesterId);
//                });
        })->get();
    }


    /**
     * Get contract between a requester and a receiver based on receiver ID.
     *
     * @param string $receiverType The type of the receiver
     * @param int $receiverId The ID of the receiver
     * @param Hostname|null $hostname The hostname entity to filter contracts on
 *
     * @return ?Contract A contract matching the criteria
     * @throws Exception
     */
    public function getContractWithSupplier(
        string $receiverType,
        int $receiverId,
        ?Hostname $hostname = null
    ): ?Contract
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();
        return Contract::where(function ($query) use ($receiverType, $receiverId , $currentHostname) {
            // Direct match
            $query->where('requester_type', get_class($currentHostname))
                ->where('requester_id', $currentHostname->id)
                ->where('receiver_type', $receiverType)
                ->where('receiver_id', $receiverId);
        })->first();
    }

    /**
     * Get contract between a requester and a receiver based on receiver connection.
     *
     * @param string $receiverType The type of the receiver
     * @param string $receiverConnection The connection/UUID of the receiver
     * @param Hostname|null $hostname The hostname entity to filter contracts on
     *
     * @return ?Contract A contract matching the criteria
     * @throws Exception
     */
    public function getContractWithSupplierByConnection(
        string $receiverType,
        string $receiverConnection,
        ?Hostname $hostname = null
    ): ?Contract
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();
        return Contract::where(function ($query) use ($receiverType, $receiverConnection, $currentHostname) {
            // Direct match using connection instead of ID
            $query->where('requester_type', get_class($currentHostname))
                ->where('requester_id', $currentHostname->id)
                ->where('receiver_type', $receiverType)
                ->where('receiver_connection', $receiverConnection);
        })->first();
    }

    /**
     * Get supplier contract (type 1) with receiver connection 'cec' and requester ID as tenant ID.
     *
     * @param int $tenantId The tenant ID (requester ID)
     * @param Hostname|null $hostname The hostname entity to filter contracts on
     *
     * @return ?Contract A supplier contract matching the criteria
     * @throws Exception
     */
    public function getSupplierContractByTenantId(
        int $tenantId,
        ?Hostname $hostname = null
    ): ?Contract
    {
        return Contract::where(function ($query) use ($tenantId) {
            $query->where('requester_type', Hostname::class)
                ->where('requester_id', $tenantId)
                ->where('receiver_connection', self::SYSTEM_CONNECTION);
        })->first();
    }

    /**
     * Get a contract with receiver connection as tenant, receiver id as tenant, and requester id as company.
     *
     * @param $receiver_connection
     * @param $receiver_id
     * @param $requester_id
     * @return ?Contract A supplier contract matching the criteria
     */
    public function getContractWithCompany(
        $receiver_connection,
        $receiver_id,
        $requester_id,
    ): ?Contract
    {
        return Contract::where([
            ['receiver_type', Hostname::class],
            ['receiver_connection', $receiver_connection],
            ['receiver_id', $receiver_id],
            ['requester_type', Company::class],
            ['requester_id', $requester_id],
        ])->first();
    }

    /**
     * Update or migrate tenant supplier contract with new structure.
     * Checks if contract exists and has new structure, updates it.
     * If exists with old structure, migrates to new structure with updated data.
     * If doesn't exist, creates new contract.
     *
     * @param int $tenantId The tenant ID
     * @param string $tenantConnection The tenant connection UUID
     * @param array $contractData The contract data to update/set
     * @param array $additionalData Additional data for the contract
     * @return Contract The updated or created contract
     * @throws Exception
     */
    public function updateOrMigrateSupplierContract(
        int $tenantId,
        string $tenantConnection,
        array $contractData,
        bool $canRequestQuotation,
        array $additionalData = [],

    ): Contract {
        $existingContract = $this->getSupplierContractByTenantId($tenantId);

        if ($existingContract) {
            // Check if contract has new structure (contains all required fields)
            $customFields = $existingContract->custom_fields ? $existingContract->custom_fields->toArray() : [];
            $hasNewStructure = $this->hasNewContractStructure($customFields);

            if ($hasNewStructure) {
                // Contract has new structure, just update the contract data
                $updatedCustomFields = array_merge($customFields, [
                    'contract' => $contractData,
                    'canRequestQuotation' => $canRequestQuotation
                ]);

                return $this->update($existingContract, array_merge([
                    'custom_fields' => $updatedCustomFields,
                    'activated_at' => $existingContract->activated_at ?? Carbon::now(),
                ], $additionalData));
            } else {
                // Contract has old structure, migrate to new structure
                $newCustomFields = [
                    'supplier' => true,
                    'contract' => $contractData,
                    'permissions' => [
                        'canManageProducts',
                        'canReceiveOrders',
                        'canProcessPayments',
                        'canAccessReports'
                    ],
                    'canRequestQuotation' => $canRequestQuotation
                ];

                // Preserve any existing data that doesn't conflict with new structure
                if (isset($customFields['supplier'])) {
                    $newCustomFields['supplier'] = $customFields['supplier'];
                }
                if (isset($customFields['canRequestQuotation'])) {
                    $newCustomFields['canRequestQuotation'] = $customFields['canRequestQuotation'];
                }
                if (isset($customFields['permissions']) && is_array($customFields['permissions'])) {
                    $newCustomFields['permissions'] = array_unique(array_merge(
                        $newCustomFields['permissions'],
                        $customFields['permissions']
                    ));
                }

                return $this->update($existingContract, array_merge([
                    'custom_fields' => $newCustomFields,
                    'activated_at' => $existingContract->activated_at ?? Carbon::now(),
                ], $additionalData));
            }
        } else {
            // No existing contract, create new one with new structure
            return $this->createSupplierContract(
                $tenantId,
                $tenantConnection,
                $contractData,
                $canRequestQuotation,
                $additionalData
            );
        }
    }

    /**
     * Check if contract custom fields have the new structure.
     * New structure should contain: supplier, contract, permissions, canRequestQuotation
     *
     * @param array $customFields The custom fields to check
     * @return bool True if has new structure, false if old structure
     */
    protected function hasNewContractStructure(array $customFields): bool
    {
        $requiredFields = ['supplier', 'contract', 'permissions', 'canRequestQuotation'];

        foreach ($requiredFields as $field) {
            if (!isset($customFields[$field])) {
                return false;
            }
        }

        // Additional check: permissions should be an array with expected values
        if (!is_array($customFields['permissions']) || empty($customFields['permissions'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current entity has a contract with a specified entity.
     *
     * @param string $entityType The type of the entity
     * @param int $entityId The ID of the entity
     * @param Hostname|null $hostname The hostname entity (optional)
     *
     * @return bool True if a contract exists, false otherwise
     * @throws Exception
     */
    public function hasContractWith(
        string $entityType,
        int $entityId,
        ?Hostname $hostname = null
    ): bool
    {
        $currentHostname = $hostname ?: $this->getCurrentHostname();

        if (!$currentHostname) {
            return false;
        }

        return $this->getContractsBetween(
            Hostname::class,
            $currentHostname->id,
            $entityType,
            $entityId
        )->isNotEmpty();
    }

    /**
     * Checks if a contract exists between a requester and a receiver.
     *
     * @param string $requesterType The type of the requester
     * @param int $requesterId The ID of the requester
     * @param string $receiverType The type of the receiver
     * @param int $receiverId The ID of the receiver
     *
     * @return bool True if a contract exists, false otherwise
     * @throws Exception
     */
    public function exists(
        string $requesterType,
        int $requesterId,
        string $receiverType,
        int $receiverId
    ): bool {
        return $this->getContractsBetween($requesterType, $requesterId, $receiverType, $receiverId)->isNotEmpty();
    }

    /**
     * Retrieve active contracts for a given hostname.
     *
     * @param Hostname|null $hostname The hostname to filter contracts by (optional)
     *
     * @return Collection A collection of active contracts for the specified hostname
     */
    public function active(
        ?Hostname $hostname = null
    ): Collection
    {
        return $this->getMyContracts($hostname)->where('active', true);
    }

    /**
     * Set the active status of a contract.
     *
     * @param int $contractId The ID of the contract to update
     * @param bool $active The status to set the contract to (default: true)
     *
     * @return Contract The updated contract
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function setActive(
        int $contractId,
        bool $active = true
    ): Contract
    {
        $contract = Contract::findOrFail($contractId);
        $contract->update([
            'active' => $active,
            'activated_at' => $active ? now() : null,
        ]);

        return $contract->fresh();
    }

    /**
     * Updates the status of a contract.
     *
     * @param int $contractId The ID of the contract to update
     * @param bool $active The new status of the contract
     *
     * @return Contract The updated contract
     * @throws Exception
     */
    public function updateStatus(
        int $contractId,
        bool $active
    ): Contract
    {
        return $this->setActive($contractId, $active);
    }

    /**
     * Gets contracts with specific custom fields.
     *
     * @param array $customFieldsFilter The filter for custom fields
     * @param Hostname|null $hostname The hostname to search contracts for (optional)
     *
     * @return Collection The contracts that match the custom fields filter
     */
    public function getContractWithSpecificJson(
        array $customFieldsFilter,
        ?Hostname $hostname = null
    ): Collection
    {
        $contracts = $this->getMyContracts($hostname);

        return $contracts->filter(function ($contract) use ($customFieldsFilter) {
            if (!$contract->custom_fields) {
                return false;
            }

            $customFields = $contract->custom_fields->toArray();

            foreach ($customFieldsFilter as $key => $value) {
                if (!isset($customFields[$key]) || $customFields[$key] !== $value) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Retrieves the contract policy for a given entity based on contracts.
     *
     * @param string $entityType The type of the entity
     * @param int $entityId The ID of the entity
     *
     * @return object|null The policy details if contracts exist, null otherwise
     */
    public function getContractPolicy(
        string $entityType,
        int $entityId
    ): ?object
    {
        // Get contracts for the entity to determine policy
        $contracts = Contract::where(function ($query) use ($entityType, $entityId) {
            $query->where('requester_type', $entityType)
                ->where('requester_id', $entityId)
                ->orWhere(function ($subQuery) use ($entityType, $entityId) {
                    $subQuery->where('receiver_type', $entityType)
                        ->where('receiver_id', $entityId);
                });
        })->where('active', true)->get();

        if ($contracts->isEmpty()) {
            return null;
        }

        // Build policy based on contracts
        $policy = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('active', true)->count(),
            'contract_types' => $contracts->pluck('type')->unique()->values()->toArray(),
            'partners' => [],
            'permissions' => [],
            'restrictions' => [],
            'discount' => [],
            'categories' => [],
            'request_open_quotation' => false
        ];

        // Check if entity a supplier and add supplier contract data
        if($entityType === Hostname::class) {
            $hostname = Hostname::with('website')->find($entityId);
            if ($hostname && $hostname->website && $hostname->website->supplier) {
                $supplierContract = $contracts->filter(function($contract) use ($entityType, $entityId) {
                    return $contract->receiver_connection === 'cec' &&
                        $contract->receiver_type === User::class &&
                        $contract->receiver_id === 1;
                })->first();

                if ($supplierContract ) {
                    $contractData = $supplierContract->custom_fields;

                    $policy['supplier_contract'] = [
                        'contract_id' => $supplierContract->id,
                        'is_supplier' => $hostname->website->supplier,
                        'payment_terms' => $contractData['payment_terms'] ?? null,
                        'runs' => $contractData['runs'] ?? [],
                        'exchange_rate' => $contractData['exchange_rate'] ?? [],
                        'created_at' => $supplierContract->created_at,
                        'activated_at' => $supplierContract->activated_at
                    ];
                } else {
                    // Supplier but no contract data yet
                    $policy['supplier_contract'] = [
                        'is_supplier' => true,
                        'has_contract' => false,
                        'message' => 'Supplier status confirmed but no contract data found'
                    ];
                }
            }
        }

        // Add partner information
        foreach ($contracts as $contract) {
            if ($contract->requester_type === $entityType && $contract->requester_id === $entityId) {
                // This entity is the requester
                $policy['partners'][] = [
                    'type' => $contract->receiver_type,
                    'id' => $contract->receiver_id,
                    'role' => 'receiver',
                    'contract_id' => $contract->id,
                    'contract_type' => $contract->type
                ];
            } else {
                // This entity is the receiver
                $policy['partners'][] = [
                    'type' => $contract->requester_type,
                    'id' => $contract->requester_id,
                    'role' => 'requester',
                    'contract_id' => $contract->id,
                    'contract_type' => $contract->type
                ];
            }

            // Extract data from custom fields
            if ($contract->custom_fields) {
                $customFields = $contract->custom_fields->toArray();

                // Extract permissions
                if (isset($customFields['permissions'])) {
                    $policy['permissions'] = array_merge($policy['permissions'], (array)$customFields['permissions']);
                }

                // Extract restrictions
                if (isset($customFields['restrictions'])) {
                    $policy['restrictions'] = array_merge($policy['restrictions'], (array)$customFields['restrictions']);
                }

                // Extract discount data
                if (isset($customFields['discount'])) {
                    $discountData = $customFields['discount'];

                    // Merge discount data - handle both general and category-specific discounts
                    if (is_array($discountData)) {
                        $policy['discount'][] = [
                            'contract_id' => $contract->id,
                            'discount_data' => $discountData
                        ];
                    }
                }

                // Extract categories data
                if (isset($customFields['categories'])) {
                    $categoriesData = $customFields['categories'];

                    if (is_array($categoriesData)) {
                        foreach ($categoriesData as $category) {
                            if (isset($category['id'])) {
                                $policy['categories'][] = [
                                    'contract_id' => $contract->id,
                                    'category_id' => $category['id'],
                                    'category_data' => $category
                                ];
                            }
                        }
                    }
                }

                // Check for quotation request access
                if (isset($customFields['request_open_quotation']) && $customFields['request_open_quotation'] === true) {
                    $policy['request_open_quotation'] = true;
                }
            }
        }

        // Remove duplicates and organize data
        $policy['permissions'] = array_unique($policy['permissions']);
        $policy['restrictions'] = array_unique($policy['restrictions']);

        // Organize discount data by contract
        $policy['discount'] = $this->organizeDiscountData($policy['discount']);

        // Organize categories data
        $policy['categories'] = $this->organizeCategoriesData($policy['categories']);

        // Convert array to object recursively for nested access
        return (object)$policy;
    }

    /**
     * Create supplier contract (reseller becoming supplier)
     */
    public function createSupplierContract(
        int $requesterId,
        string $requesterConnection,
        array $contractData,
        bool $canRequestQuotation,
        array $additionalData = [],

    ): Contract {
        // Default data for supplier contract with system
        $data = array_merge([
            'requester_type' => Hostname::class,
            'requester_id' => $requesterId,
            'requester_connection' => $requesterConnection,
            'receiver_connection' => 'cec',
            'receiver_type' => 'App\Models\User', // Or whatever represents your system
            'receiver_id' => 1, // System ID
            'type' => ContractType::INTERNAL,
            'custom_fields' => [
                'supplier' => true,
                'contract' => $contractData,
                'permissions' => [
                    'canManageProducts',
                    'canReceiveOrders',
                    'canProcessPayments',
                    'canAccessReports'
                ],
                'canRequestQuotation' => $canRequestQuotation
            ]
        ], $additionalData);

        return $this->create($data);
    }

    /**
     * Check if entity is a supplier (has supplier contract)
     */
    public function isSupplier(string $entityType, int $entityId): bool
    {
        $policy = $this->getContractPolicy($entityType, $entityId);

        return $policy && $policy->supplier_contract && $policy->supplier_contract->is_supplier;
    }

    /**
     * Get supplier contract data
     */
    public function getSupplierContract(string $entityType, int $entityId): ?object
    {
        $policy = $this->getContractPolicy($entityType, $entityId);

        return $policy?->supplier_contract ?? null;
    }

    /**
     * Calculate commission for supplier based on runs
     */
    public function calculateCommission(string $entityType, int $entityId, float $amount): float
    {
        $supplierContract = $this->getSupplierContract($entityType, $entityId);

        if (!$supplierContract || empty($supplierContract->runs)) {
            return 0.0;
        }

        // Find the appropriate run based on amount
        foreach ($supplierContract->runs as $run) {
            if ($amount >= $run->from && $amount <= $run->to) {
                return ($amount * $run->percentage) / 100;
            }
        }

        return 0.0;
    }

    /**
     * Accept supplier contract by setting it to active with proper data structure
     *
     * @param string $st The connection/UUID
     * @param array $contractData The contract data
     * @param bool $canRequestQuotation Whether the supplier can request quotations
     * @return Contract The accepted contract
     * @throws Exception
     */
    public function acceptSupplierContract(
        int|Contract $contractId,
        array $contractData,
        bool $canRequestQuotation = false
    ): Contract {
        // Find existing contract by connection
        if ($contractId instanceof Contract) {
            $contract = $contractId;
        } else {
            $contract = Contract::findOrFail($contractId);
        }
        // Prepare the update data using the template structure
        $updateData = [
            'st' => Status::ACCEPTED->value,
            'active' => true,
            'activated_at' => Carbon::now()->toDateTimeString(),
            'custom_fields' => [
                'supplier' => true,
                'contract' => $contractData,
                'permissions' => [
                    'canManageProducts',
                    'canReceiveOrders',
                    'canProcessPayments',
                    'canAccessReports'
                ],
                'canRequestQuotation' => $canRequestQuotation,
            ]
        ];

        return $this->update($contract, $updateData);
    }

    /**
     * Checks if the requester can request a quotation from a supplier based on contract policy.
     *
     * @param string $requesterType The type of the requester
     * @param int $requesterId The ID of the requester
     * @param string $supplierType The type of the supplier
     * @param int $supplierId The ID of the supplier
     *
     * @return bool Returns true if the requester can request a quotation from the supplier, false otherwise
     */
    public function canRequestQuotation(
        string $requesterType,
        int $requesterId,
        string $supplierType,
        int $supplierId
    ): bool {
        $policy = $this->getContractPolicy($requesterType, $requesterId);

        if (!$policy) {
            return false;
        }

        // Check if quotation access is enabled
        if (!$policy['request_open_quotation']) {
            return false;
        }

        // Check if there's an active contract with the supplier
        foreach ($policy['partners'] as $partner) {
            if ($partner['type'] === $supplierType && $partner['id'] == $supplierId) {
                return true;
            }
        }

        return false;
    }

    public function getReceiverConnections(
        ?Hostname $hostname = null,
        array $filters = []
    ): array
    {
        $contracts = $this->getMyContracts($hostname);

        return $contracts
            ->filter(function($contract) use ($filters) {
                // Apply default filters
                $passesFilter = $contract->am_requester; // Only contracts where we're requester

                // Apply custom filters
                foreach ($filters as $field => $value) {
                    if (is_callable($value)) {
                        $passesFilter = $passesFilter && $value($contract);
                    } else {
                        $passesFilter = $passesFilter && $contract->$field === $value;
                    }
                }

                return $passesFilter;
            })
            ->pluck('receiver_connection')
            ->toArray();
    }

    /**
     * Organizes discount data into a structured array.
     *
     * @param array $discountData The array of discount data to be organized
     *
     * @return array The organized discount data array with keys 'general' and 'categories'
     */
    protected function organizeDiscountData(
        array $discountData
    ): array
    {
        $organized = [
            'general' => [],
            'categories' => []
        ];

        foreach ($discountData as $item) {
            $contractId = $item['contract_id'];
            $discount = $item['discount_data'];

            // Handle general discount
            if (isset($discount['general'])) {
                $organized['general'][] = [
                    'contract_id' => $contractId,
                    'mode' => $discount['general']['mode'] ?? 'inactive',
                    'status' => $discount['general']['status'] ?? false,
                    'slots' => $discount['general']['slots'] ?? []
                ];
            }

            // Handle category-specific discounts
            if (isset($discount['categories']) && is_array($discount['categories'])) {
                foreach ($discount['categories'] as $categoryDiscount) {
                    $organized['categories'][] = [
                        'contract_id' => $contractId,
                        'category_id' => $categoryDiscount['id'] ?? null,
                        'mode' => $categoryDiscount['mode'] ?? 'inactive',
                        'status' => $categoryDiscount['status'] ?? false,
                        'slots' => $categoryDiscount['slots'] ?? []
                    ];
                }
            }
        }

        return $organized;
    }

    /**
     * Organizes categories data to ensure unique category IDs and group contracts by category.
     *
     * @param array $categoriesData The array of categories data to be organized
     *
     * @return array The organized categories data with unique category IDs and grouped contracts
     */
    protected function organizeCategoriesData(
        array $categoriesData
    ): array
    {
        $organized = [];
        $uniqueCategories = [];

        foreach ($categoriesData as $item) {
            $contractId = $item['contract_id'];
            $categoryId = $item['category_id'];

            // Avoid duplicate category IDs
            if (!in_array($categoryId, $uniqueCategories)) {
                $uniqueCategories[] = $categoryId;

                $organized[] = [
                    'category_id' => $categoryId,
                    'contracts' => [$contractId],
                    'category_data' => $item['category_data']
                ];
            } else {
                // Add contract ID to existing category
                foreach ($organized as &$category) {
                    if ($category['category_id'] === $categoryId) {
                        if (!in_array($contractId, $category['contracts'])) {
                            $category['contracts'][] = $contractId;
                        }
                        break;
                    }
                }
            }
        }

        return $organized;
    }

    /**
     * Get information about the model based on the entity type.
     *
     * @param string $entityType The entity type to retrieve model information for
     * @return array|null An array containing table and fields information if found, null otherwise
     */
    protected function getModelInfo(
        string $entityType
    ): ?array
    {
        // Enhanced mapping with more models and better field selection
        $modelMappings = [
            'App\\Models\\Hostname' => [
                'table' => 'hostnames',
                'fields' => ['fqdn', 'id', 'website_id']
            ],
            Hostname::class => [
                'table' => 'hostnames',
                'fields' => ['fqdn', 'id', 'website_id']
            ],
            'App\\Models\\User' => [
                'table' => 'users',
                'fields' => ['email', 'created_at']
            ],
            'App\\Models\\Company' => [
                'table' => 'companies',
                'fields' => ['name', 'url', 'coc', 'tax_nr', 'created_at']
            ],
            // Add more models as needed
        ];

        // Try to get from mapping first
        if (isset($modelMappings[$entityType])) {
            return $modelMappings[$entityType];
        }

        // Try to instantiate the model to get table info dynamically
        try {
            if (class_exists($entityType)) {
                $model = new $entityType;

                return [
                    'table' => $model->getTable(),
                    'fields' => $this->getModelFields($model)
                ];
            }
        } catch (Exception $e) {
            Log::warning("Could not get model info for: {$entityType}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get model fields based on fillable attributes.
     *
     * @param mixed $model The model instance to retrieve fields from
     *
     * @return array An array of selected model fields
     */
    protected function getModelFields(
        mixed $model
    ): array
    {
        // Try to get fillable fields first
        $fillable = $model->getFillable();

        if (!empty($fillable)) {
            // Filter out sensitive fields and return most useful ones
            $excludeFields = ['password', 'remember_token', 'email_verified_at', 'deleted_at'];
            $safeFields = array_diff($fillable, $excludeFields);

            // Prioritize common useful fields
            $priorityFields = ['name', 'title', 'email', 'phone', 'fqdn', 'uuid'];
            $selectedFields = [];

            // Add priority fields first
            foreach ($priorityFields as $field) {
                if (in_array($field, $safeFields)) {
                    $selectedFields[] = $field;
                }
            }

            // Add other fields up to a limit
            $remainingFields = array_diff($safeFields, $selectedFields);
            $selectedFields = array_merge($selectedFields, array_slice($remainingFields, 0, 3));

            return array_slice($selectedFields, 0, 5); // Limit to 5 fields max
        }

        // Default fields for unknown models
        return ['name', 'email', 'created_at'];
    }

    /**
     * Get the system connection name from the configuration file.
     *
     * @return string The name of the system connection as defined in the configuration file
     */
    protected function getSystemConnection(): string
    {
        return config('tenancy.db.system-connection-name', 'system');
    }

    /**
     * Get the current hostname.
     *
     * @return Hostname|null The current hostname if found, null otherwise
     */
    protected function getCurrentHostname(): ?Hostname
    {
        return hostname();
    }

    /**
     * Get the connection name for the given hostname
     *
     * @param string $hostname The hostname to retrieve the connection for
     * @return string The connection name associated with the
     * @throws Exception
     */
    protected function getTenantConnection(string $hostname): string
    {
        $website = Website::where('uuid', $hostname)->first();
        if (!$website) {
            throw new Exception("Tenant not found for hostname: {$hostname}");
        }
        return $website->uuid;
    }

    /**
     * Validates the contract data.
     *
     * @param array $data The data to be validated for contract creation
     *
     * @throws ValidationException If validation fails with error messages
     */
    protected function validateContractData(
        array $data
    ): void
    {
        $errors = [];
        $requiredFields = ['requester_type', 'requester_id', 'receiver_type', 'receiver_id'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        // Validate entity IDs are positive integers
        if (isset($data['requester_id']) && (!is_numeric($data['requester_id']) || $data['requester_id'] <= 0)) {
            $errors['requester_id'] = ['The requester_id must be a positive integer.'];
        }

        if (isset($data['receiver_id']) && (!is_numeric($data['receiver_id']) || $data['receiver_id'] <= 0)) {
            $errors['receiver_id'] = ['The receiver_id must be a positive integer.'];
        }

        // Validate entity types are valid classes
        if (isset($data['requester_type']) && !class_exists($data['requester_type'])) {
            $errors['requester_type'] = ['The requester_type must be a valid model class.'];
        }

        if (isset($data['receiver_type']) && !class_exists($data['receiver_type'])) {
            $errors['receiver_type'] = ['The receiver_type must be a valid model class.'];
        }

        // Validate contract type if provided
        if (isset($data['type']) && !($data['type'] instanceof ContractType)) {
            $errors['type'] = ['The type must be a valid ContractType enum value.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validates the contract creation rules based on the requester and receiver types.
     *
     * @param array $data The data containing requester_type, receiver_type, requester_id, receiver_id
     *
     * @throws ValidationException If the contract creation rules are not satisfied
     */
    protected function validateContractRules(
        array $data
    ): void
    {
        $errors = [];
        $requesterType = $data['requester_type'];
        $receiverType = $data['receiver_type'];

        // Business rules:
        // 1. Tenant ↔ Tenant (with supplier restrictions)
        // 2. Tenant → User (only this direction)
        // 3. Company → Tenant (only this direction)

        // Handle Tenant-to-Tenant contracts with supplier logic
        if ($requesterType === Hostname::class && $receiverType === Hostname::class) {
            $this->validateTenantToTenantContract($data, $errors);
        }
        // Handle other entity combinations
        else {
            $allowedCombinations = [
                // Tenant → User (only this direction)
                [Hostname::class, 'App\\Models\\User'],

                // Company → Tenant (only this direction)
                ['App\\Models\\Company', Hostname::class],

                // You can add more allowed combinations here
            ];

            $isValidCombination = false;

            foreach ($allowedCombinations as $combination) {
                if ($requesterType === $combination[0] && $receiverType === $combination[1]) {
                    $isValidCombination = true;
                    break;
                }
            }

            if (!$isValidCombination) {
                $errors['contract_entities'] = [
                    'Invalid contract combination. Allowed combinations are: ' .
                    'Supplier ↔ Supplier, Reseller → Supplier, Tenant → User, Company → Tenant'
                ];
            }
        }

        // Additional rule: Can't create contract with same entity
        if ($requesterType === $receiverType && $data['requester_id'] === $data['receiver_id']) {
            $errors['same_entity'] = ['Cannot create a contract with the same entity.'];
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate if tenant can contract with another tenant based on specific rules
     *
     * @param array $data
     * @param array $errors
     * @return void
     */
    protected function validateTenantToTenantContract(
        array $data,
        array &$errors
    ): void
    {
        $requesterId = $data['requester_id'];
        $receiverId = $data['receiver_id'];

        // Get the hostnames with their website relationships
        $requesterHostname = Hostname::with('website')->find($requesterId);
        $receiverHostname = Hostname::with('website')->find($receiverId);

        if (!$requesterHostname) {
            $errors['requester_id'] = ['Requester hostname not found.'];
        }

        if (!$receiverHostname) {
            $errors['receiver_id'] = ['Receiver hostname not found.'];
        }

        if (!$requesterHostname || !$receiverHostname) {
            return; // Can't validate further without valid hostnames
        }

        if (!$requesterHostname->website) {
            $errors['requester_website'] = ['Requester hostname has no associated website.'];
        }

        if (!$receiverHostname->website) {
            $errors['receiver_website'] = ['Receiver hostname has no associated website.'];
        }

        if (!$requesterHostname->website || !$receiverHostname->website) {
            return; // Can't validate further without websites
        }

        $requesterIsSupplier = (bool) $requesterHostname->website->supplier;
        $receiverIsSupplier = (bool) $receiverHostname->website->supplier;

        /*
         * Tenant Contract Rules:
         * Supplier ↔ Supplier (bidirectional)
         * Reseller → Supplier (one-way only)
         * Reseller → Reseller (not allowed)
         * Supplier → Reseller (not allowed)
         */

        $isValidContract = false;
        $contractDescription = '';

        if ($requesterIsSupplier && $receiverIsSupplier) {
            // Supplier ↔ Supplier (always allowed)
            $isValidContract = true;
            $contractDescription = 'Supplier-to-Supplier';
        } elseif (!$requesterIsSupplier && $receiverIsSupplier) {
            // Reseller → Supplier (allowed)
            $isValidContract = true;
            $contractDescription = 'Reseller-to-Supplier';
        } elseif ($requesterIsSupplier && !$receiverIsSupplier) {
            // Supplier → Reseller (not allowed)
            $errors['tenant_contract_direction'] = [
                'Suppliers cannot initiate contracts with Resellers. Only Resellers can contract with Suppliers.'
            ];
        } elseif (!$requesterIsSupplier && !$receiverIsSupplier) {
            // Reseller → Reseller (not allowed)
            $errors['reseller_to_reseller'] = [
                'Resellers cannot create contracts with other Resellers. Resellers can only contract with Suppliers.'
            ];
        }

        // Log the validation for debugging
        if ($isValidContract) {
            Log::info("Valid {$contractDescription} contract validation", [
                'requester_id' => $requesterId,
                'receiver_id' => $receiverId,
                'requester_is_supplier' => $requesterIsSupplier,
                'receiver_is_supplier' => $receiverIsSupplier
            ]);
        }
    }

    /**
     * Checks if a contract already exists between the specified entities.
     *
     * @param array $data The array containing requester_type, requester_id, receiver_type, and receiver_id
     *
     * @throws ValidationException If a contract already exists between the entities
     * @throws Exception
     */
    protected function checkContractExists(array $data): void
    {
        $existingContract = $this->getContractsBetween(
            $data['requester_type'],
            $data['requester_id'],
            $data['receiver_type'],
            $data['receiver_id']
        )->first();

        if ($existingContract) {
            throw ValidationException::withMessages([
                'contract_exists' => [
                    "A contract already exists between these entities. Contract ID: {$existingContract->id}"
                ]
            ]);
        }
    }

    /**
     * Convert an array to a stdClass object recursively.
     *
     * @param mixed $array The array to convert to an object
     *
     * @return mixed The converted object
     */
    private function arrayToObject(
        mixed $array
    ):mixed
    {
        if (is_array($array)) {
            $object = new \stdClass();
            foreach ($array as $key => $value) {
                $object->$key = $this->arrayToObject($value);
            }
            return $object;
        }
        return $array;
    }
}
