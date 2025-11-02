<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Users\CreateUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\System\User\UserResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class UserController extends Controller
{
    public function __construct(
        private readonly DatabaseManager $databaseManager,
    ) {
    }

    /**
     * Returns a list of all available users
     *
     * @return AnonymousResourceCollection|mixed
     */
    public function index(): mixed
    {
        return UserResource::collection(
            User::query()->with(['profile', 'company.addresses.country'])->get()
        )->additional([
            'message' => __('Users has been retrieved successfully'),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show a specific user
     *
     * @param User $user
     *
     * @return JsonResponse|UserResource
     */
    public function show(
        User $user
    ): JsonResponse|UserResource
    {
        return UserResource::make(
            $user->load(['profile', 'company.addresses.country'])
        )
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Create a new user
     *
     * @param CreateUserRequest $request
     *
     * @return UserResource|JsonResponse
     */
    public function store(
        CreateUserRequest $request
    ): JsonResponse|UserResource
    {
        $company_data = $request->except('username', 'password', 'authorization', 'authToken', 'authUsername', 'password');

        $authorization = [];

        $authorization['type'] = $request->authorization;
        $authorization['token'] = $request->authToken;
        $authorization['username'] = $request->authUsername;
        $authorization['password'] = $request->authPassword;

        $user = User::query()->create(
            $request->safe()->only('email', 'username', 'password')
        );

        /* @var User $user */

        $company = $user->company()->create(
            array_merge($company_data, [
                'name' => $request->company_name,
                'authorization' => $authorization,
            ])
        );

        $user->companies()->syncWithoutDetaching($company->id);

        $user->addRoles($request->roles);

        $user->profile()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender
        ]);

        $address = Address::query()->firstOrCreate(
            $request->only(
                'address',
                'number',
                'city',
                'zip_code',
                'region',
                'country_id'
            )
        );

        $company = $user->company;

        $addressable = collect($request->validated())
            ->filter(fn($v, $k) => in_array($k, ['type', 'full_name', 'company_name', 'phone_number', 'default'], true))
            ->toArray();

        $company->addresses()->syncWithoutDetaching([
            $address->id => $addressable
        ]);

        return UserResource::make(
            $user->load(['profile', 'company.addresses.country'])
        )
            ->additional([
                'message' => 'User created',
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * Update an existing user
     *
     * @param UpdateUserRequest $request
     * @param User $user
     *
     * @return UserResource
     *
     * @throws Throwable
     */
    public function update(
        UpdateUserRequest $request,
        User              $user,
    ): UserResource
    {
        $this->databaseManager->transaction(
            static function () use ($request, $user): void {
                $user->updateOrFail(
                    $request->safe()->only(['email'])
                );

                $user->profile()->firstOrFail()->updateOrFail(
                    $request->safe()->only([
                        'gender',
                        'first_name',
                        'middle_name',
                        'last_name',
                        'dob',
                        'bio',
                        'avatar'
                    ])
                );
            }
        );

        return UserResource::make(
            $user->load(['profile', 'company.addresses.country'])
        )
            ->additional([
                'message' => __('User has been updated successfully'),
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Delete a specific user
     *
     * @param User $user
     *
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function destroy(
        User $user,
    ): JsonResponse
    {
        $this->databaseManager->transaction(
            static function () use ($user): void {
                $user->profile()->delete();
                $user->addresses()->detach();
                $user->deleteOrFail();
            }
        );

        return response()->json([
            'message' => __('User has been deleted successfully'),
            'status' => Response::HTTP_OK
        ]);
    }
}
