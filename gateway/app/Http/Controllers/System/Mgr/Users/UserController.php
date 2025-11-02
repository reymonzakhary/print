<?php

namespace App\Http\Controllers\System\Mgr\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\Users\CreateUserRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        // create new user with company
        // login with the new user -> api
        // suppliers get request
        // suppliers invite post request filter company data
        /**
         * 1)
         */
        return Inertia::render('Users', [
            'users' => User::with('company')->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateUserRequest $request
     * @return JsonResponse
     */
    public function store(
        CreateUserRequest $request
    )
    {
        $user_data = $request->only('email', 'username', 'password');

        $company_data = $request->except('username', 'password', 'authorization', 'authToken', 'authUsername', 'password');

        $authorization = [];

        $authorization['type'] = $request->authorization;
        $authorization['token'] = $request->authToken;
        $authorization['username'] = $request->authUsername;
        $authorization['password'] = $request->authPassword;

        $user = User::create($user_data);

        $company = $user->company()->create(array_merge($company_data, [
            'name' => $request->company_name,
            'authorization' => $authorization,
        ]));

        $user->companies()->syncWithoutDetaching($company->id);

        $user->addRoles($request->roles);
        $user->profile()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'gender' => $request->gender
        ]);

        $address = Address::firstOrCreate(
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
        $company->addresses()
            ->syncWithoutDetaching([
                $address->id => $addressable
            ]);

        return response()->json([
            'message' => 'user created',
            'status' => Response::HTTP_CREATED
        ], Response::HTTP_CREATED);
    }
}
