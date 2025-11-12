<?php

/**
 * This is a laravel application.
 * Laravel version is v10.48.4.
 */

namespace App\Http\Controllers\Tenant\Mgr\Machines;

use App\Http\Controllers\Controller;
use App\Http\Requests\Machines\MachineStoreRequest;
use App\Http\Requests\Machines\MachineUpdateRequest;
use App\Http\Resources\Machines\MachineResource;
use App\Models\Tenant\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class MachineController extends Controller
{

    /**
     * Retrieve a collection of machine resources from the database.
     *
     * @return AnonymousResourceCollection A collection of machine resources.
     * @throws ValidationException
     */
    public function index(): AnonymousResourceCollection
    {
        return MachineResource::collection(
            Machine::query()->obtainMachines()->get()
        )->additional([
            "status" => Response::HTTP_OK
        ]);


    }

    /**
     * Store a new machine in the database.
     *
     * @param MachineStoreRequest $request The request object containing the machine data.
     *
     * @return MachineResource The created machine as a resource.
     * @throws ValidationException
     */
    public function store(
        MachineStoreRequest $request
    ): MachineResource
    {

        return MachineResource::make(
            Machine::query()->obtainCreateMachine($request->validated())->first()
        )
            ->additional([
                'message' => __('Machine has been created successfully.'),
                "status" => Response::HTTP_CREATED
            ]);

    }

    /**
     * Update a machine in the database.
     *
     * @param MachineUpdateRequest $request The request object containing the machine data.
     * @param string               $machine The identifier of the machine to update.
     *
     * @return JsonResponse The JSON response containing the update result.
     * @throws ValidationException
     */
    public function update(
        MachineUpdateRequest $request,
        string               $machine
    ): JsonResponse
    {

        if(Machine::query()->obtainUpdateMachine(
            machine: $machine,
            request: $request->except('options', 'tenant', 'uuid', 'hostname', 'host_id', 'display_price'),
            options: $request->validated('options')
        )->update()) {
            return response()->json([
                'message' => __("Machine has been updated successfully."),
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * Delete a machine from the database.
     *
     * @param string $machine The identifier of the machine to delete.
     *
     * @return JsonResponse The JSON response with the status and message.
     * @throws ValidationException
     */
    public function destroy(
        string  $machine
    ): JsonResponse
    {
        if(Machine::query()->obtainDestroyMachine(
            machine: $machine
        )->delete()) {
            return response()->json([
                'message' => __('Machine has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => __('We couldn\'t delete the machine are requested, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
