<?php

namespace App\Http\Controllers\Tenant\Mgr\FileManager;

use Alexusmai\LaravelFileManager\Events\BeforeInitialization;
use Alexusmai\LaravelFileManager\Events\Deleting;
use Alexusmai\LaravelFileManager\Events\DirectoryCreated;
use Alexusmai\LaravelFileManager\Events\DirectoryCreating;
use Alexusmai\LaravelFileManager\Events\DiskSelected;
use Alexusmai\LaravelFileManager\Events\Download;
use Alexusmai\LaravelFileManager\Events\FileCreated;
use Alexusmai\LaravelFileManager\Events\FileCreating;
use Alexusmai\LaravelFileManager\Events\FilesUploaded;
use Alexusmai\LaravelFileManager\Events\FilesUploading;
use Alexusmai\LaravelFileManager\Events\FileUpdate;
use Alexusmai\LaravelFileManager\Events\Paste;
use Alexusmai\LaravelFileManager\Events\Rename;
use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Alexusmai\LaravelFileManager\Services\Zip;
use App\Events\Tenant\FM\UnzipDirectoryEvent;
use App\Events\Tenant\FM\ZipDirectoryEvent;
use App\Foundation\Media\FileManager;
use App\Http\Requests\FileManager\FileTagRequest;
use App\Http\Requests\FileManager\RequestValidator;
use App\Http\Resources\Media\FilemanagerResource;
use App\Models\Tenants\Media\FileManager as FileManagerModel;
use App\Scoping\Scopes\FileManager\DiskScope;
use App\Scoping\Scopes\FileManager\SearchNameScope;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Exceptions\DecoderException;

/**
 * @group Tenant File Manager
 */
final class FileManagerController extends Controller
{
    /**
     * FileManagerController constructor.
     *
     * @param FileManager $fm
     * @param ConfigRepository $configRepository
     * @param ACL $acl
     */
    public function __construct(
        private readonly FileManager      $fm,
        private readonly ConfigRepository $configRepository,
        private readonly ACL              $acl
    )
    {
    }

    /**
     * Initialize file manager
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"result": {
     * 		"status": "success",
     * 		"message": null
     * 	},
     * 	"config": {
     * 		"acl": false,
     * 		"leftDisk": "tenancy",
     * 		"rightDisk": "assets",
     * 		"leftPath": null,
     * 		"rightPath": "assets",
     * 		"windowsConfig": 3,
     * 		"hiddenFiles": true,
     * 		"disks": {
     * 			"tenancy": {
     * 				"driver": "s3"
     * 			},
     * 			"assets": {
     * 				"driver": "s3"
     * 			},
     * 			"carts": {
     * 				"driver": "s3"
     * 			}
     * 		},
     * 		"lang": "en"
     * 	}
     * }
     *
     * @return JsonResponse
     */
    public function initialize()
    {
        event(new BeforeInitialization());

        return response()->json(
            $this->fm->initialize()
        );
    }

    /**
     * Get files and directories for the selected path and disk
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"result": {
     * 		"status": "success",
     * 		"message": null
     * 	},
     * 	"directories": [
     * 		{
     * 			"type": "dir",
     * 			"path": "chd",
     * 			"basename": "chd",
     * 			"dirname": "",
     * 			"timestamp": null,
     * 			"visibility": null
     * 		}
     * 	],
     * 	"files": [
     * 		{
     * 			"type": "file",
     * 			"path": "chd",
     * 			"basename": "chd",
     * 			"dirname": "",
     * 			"extension": "",
     * 			"filename": "chd",
     * 			"size": 0,
     * 			"timestamp": 1716201127,
     * 			"visibility": null,
     * 			"tags": []
     * 		},
     * 	],
     * 	"sizes": {
     * 		"tenancy": {
     * 			"sizes": [],
     * 			"total": 0,
     * 			"disk": 67108864000
     * 		},
     * 	}
     * }
     *
     * @param RequestValidator $request
     * @return JsonResponse
     */
    public function content(
        RequestValidator $request
    )
    {
        $content = $this->fm->content(
            $request->input('disk'),
            $request->input('originalPath')
        );
        return response()->json(array_merge($content, [
                'sizes' => $request->sizes
            ]
        ));

    }

    /**
     * Directory tree
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"result": {
     * 		"status": "success",
     * 		"message": null
     * 	},
     * 	"directories": [
     * 		{
     * 			"type": "dir",
     * 			"path": "chd",
     * 			"basename": "chd",
     * 			"dirname": "",
     * 			"timestamp": null,
     * 			"visibility": null,
     * 			"props": {
     * 				"hasSubdirectories": false
     * 			}
     * 		}
     * 	]
     * }
     *
     * @param RequestValidator $request
     * @return JsonResponse
     */
    public function tree(
        RequestValidator $request
    )
    {
        return response()->json(
            $this->fm->tree(
                $request->input('disk'),
                $request->input('originalPath')
            )
        );
    }

    /**
     * Check the selected disk
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"result": {
     * 		"status": "success",
     * 		"message": "diskSelected"
     * 	}
     * }
     *
     * @response 422
     * {
     * 	"errors": {
     * 		"disk": [
     * 			"We couldn't find disk configuration."
     * 		]
     * 	},
     * 	"message": "We couldn't find the specified path or directory."
     * }
     *
     * @param RequestValidator $request
     * @return JsonResponse
     */
    public function selectDisk(RequestValidator $request)
    {
        event(new DiskSelected($request->input('disk')));

        return response()->json([
            'result' => [
                'status' => 'success',
                'message' => 'diskSelected',
            ],
        ]);
    }

    /**
     * Upload files
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam overwrite boolean required overwrite file if exists
     * @bodyParam files array required files to upload
     *
     * @response 200
     * {
     *     "result": {
     *         "status": "success",
     *         "message": "uploaded",
     *         "fileManager": [
     *             {
     *                 "user_id": 1,
     *                 "path": "",
     *                 "name": "6e888b4c-c20c-44d9-9790-d7e6cec4b504.jpg",
     *                 "collection": null,
     *                 "size": 215205,
     *                 "model_type": null,
     *                 "model_id": null,
     *                 "disk": "tenancy",
     *                 "group": "images",
     *                 "ext": "jpg",
     *                 "type": "image\/jpeg",
     *                 "updated_at": "2024-05-20T11:07:42.000000Z",
     *                 "created_at": "2024-05-20T11:07:42.000000Z",
     *                 "id": 1
     *             }
     *         ]
     *     }
     * }
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function upload(
        RequestValidator $request
    )
    {
        if (!$request->has('files')){
            return response()->json([
                'message' => __('files are required'),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $acl = resolve(ACL::class);
        $path = str_replace([request()->tenant->uuid . '/', request()->tenant->uuid], ['', ''], $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }
        event(new FilesUploading($request));
        $uploadResponse = $this->fm->upload(
            $request->user(),
            $request->input('disk'),
            $path,
            $request->file('files'),
            $request->boolean('overwrite'),
            $request->input('originalPath')
        );

        event(new FilesUploaded($request));

        return response()->json($uploadResponse);
    }

    /**
     * Delete files and folders
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam items array required array of objects contain type and path of selected files
     *
     * @response 200
     * {
     * "result":{
     *  "status":"success",
     *  "message":"deleted"
     *  }
     * }
     *
     * @response 422
     * {
     * "message":"Can not delete system path",
     * "status":422
     * }
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function delete(
        RequestValidator $request
    ): JsonResponse
    {
        try {
            event(new Deleting($request));

            $items = [];
            $tenantUUID = explode('/', $request->originalPath)[0];

            foreach ($request->input('items') as $item) {
                if ($item && preg_match("/^campaigns|Providers|providers|orders|quotations/", $item['path'])) {
                    throw ValidationException::withMessages([
                        'item' => __('Can not delete system path')
                    ]);
                }

                if ($this->configRepository->getAcl() && !$this->acl->getAccessLevel(
                        $request->input('disk'),
                        $item['path']
                    )) {
                    throw new AuthorizationException(__('Unauthorized'));
                }

                $items[] = [
                    'path' => $tenantUUID . '/' . $item['path'],
                    'tenantPath' => $item['path'],
                    'type' => $item['type']
                ];
            }

            $this->fm->delete($request->input('disk'), $items, $tenantUUID);

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => __('File or folder has been deleted successfully')
            ]);
        } catch (Exception $e) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json(['status' => $statusCode, 'message' => $e->getMessage()], $statusCode);
        }
    }

    /**
     * Copy / Cut files and folders
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam clipboard object required contains array of directories, array of files, disk and type
     *
     * @response 200
     * {"result":{"status":"success","message":"copied"}}
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function paste(
        RequestValidator $request
    ): JsonResponse
    {
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));

        if ($this->configRepository->getAcl() && !$this->acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        event(new Paste($request));
        $files = [];
        $tenantUUID = explode('/', $request->originalPath)[0];

        foreach ($request->clipboard['files'] as $file) {
            $files[] = $tenantUUID . '/' . $file;
        }

        $dirs = [];
        foreach ($request->clipboard['directories'] as $directory) {
            $dirs[] = $tenantUUID . '/' . $directory;
        }

        $request->merge([
            'clipboard' => [
                "type" => $request->clipboard['type'],
                "disk" => $request->clipboard['disk'],
                "directories" => $dirs,
                "files" => $files
            ]
        ]);

        return response()->json(
            $this->fm->paste(
                $request->input('disk'),
                $request->input('originalPath'),
                $request->input('clipboard'),
                tenant()
            )
        );
    }

    /**
     * Rename
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam newName string required path selected fil with new name to rename
     * @bodyParam oldName string required path selected fil with old name
     *
     * @response 200
     * {"result":{"status":"success","message":"renamed"}}
     *
     * @param RequestValidator $request
     * @return JsonResponse
     */
    public function rename(
        RequestValidator $request
    ): JsonResponse
    {
        try {
            $tenant = request()->tenant;
            $path = str_replace($tenant->uuid . '/', '', $request->input('path'));

            if ($this->configRepository->getAcl() && !$this->acl->getAccessLevel($request->input('disk'), $path)) {
                throw new AuthorizationException(__('Unauthorized'), Response::HTTP_UNAUTHORIZED);
            }

            event(new Rename($request));

            $this->fm->rename(
                $request->input('disk'),
                $request->originalPath . $request->input('oldName'),
                $request->originalPath . $request->input('newName'),
                $tenant
            );

            return response()->json([
                'result' => [
                    'status' => 'success',
                    'message' => 'renamed',
                ]
            ]);
        } catch (Exception $e) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json([
                'status' => $statusCode,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Download file
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return mixed
     */
    public function download(RequestValidator $request)
    {
        $acl = resolve(ACL::class);
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        event(new Download($request));
        return $this->fm->download(
            $request->input('disk'),
            $request->input('originalPath')
        );
    }

    /**
     * Create thumbnails
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return Response|mixed
     * @throws BindingResolutionException
     */
    public function thumbnails(RequestValidator $request)
    {
        try {
            return $this->fm->thumbnails(
                $request->input('disk'),
                $request->input('originalPath'),
                $request->input('thumbsize') ?? 80,
            );
        } catch (DecoderException) {
            return response()->json([
                'message' => 'Could not generate a thumbnail as the provided image is not valid.',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Image preview
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return mixed
     * @throws BindingResolutionException
     */
    public function preview(RequestValidator $request)
    {
        try {
            return $this->fm->preview(
                $request->input('disk'),
                $request->input('originalPath'),
                $request->input('thumbsize')
            );
        } catch (DecoderException) {
            return response()->json([
                'message' => "Could not preview the image as it's not valid.",
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Image preview
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function publicPreview(RequestValidator $request)
    {
        return $this->fm->preview(
            $request->input('disk'),
            ($this->IsExternal($request) ? '' : request()->tenant->uuid . '/') . $request->input('path')
        );
    }

    /**
     * File url
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"result": {
     * 		"status": "success",
     * 		"message": null
     * 	},
     * 	"url": "https:\/\/cec-ams3-prod.ams3.digitaloceanspaces.com\/tenancy\/6f24d2b6-2ab5-4c30-844c-bf1f8af1f16a\/"
     * }
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function url(RequestValidator $request)
    {
        return response()->json(
            $this->fm->url(
                $request->input('disk'),
                $request->input('originalPath')
            )
        );
    }

    /**
     * Create new directory
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam name string required name of the directory
     *
     * @response 200
     * {
     *    "result": {
     *        "status": "success",
     *        "message": "dirCreated"
     *    },
     *    "directory": {
     *        "type": "dir",
     *        "path": "\/chd",
     *        "basename": "chd",
     *        "dirname": "\/",
     *        "timestamp": "\/",
     *        "visibility": "\/"
     *    },
     *    "tree": [
     *        {
     *            "type": "dir",
     *            "path": "\/chd",
     *            "basename": "chd",
     *            "dirname": "\/",
     *            "timestamp": "\/",
     *            "visibility": "\/",
     *            "props": {
     *                "hasSubdirectories": false
     *            }
     *        }
     *    ]
     *}
     *
     * @param RequestValidator $request
     * @return JsonResponse
     */
    public function createDirectory(RequestValidator $request)
    {
        $acl = resolve(ACL::class);
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        $orginalPath = (str_ends_with($request->input('originalPath'), "/")) ?
            substr($request->input('originalPath'), 0, -1) :
            $request->input('originalPath');
        event(new DirectoryCreating($request));
        $createDirectoryResponse = $this->fm->createDirectory(
            $request->input('disk'),
            $orginalPath,
            $request->input('name')
        );

        if ($createDirectoryResponse['result']['status'] === 'success') {
            event(new DirectoryCreated($request));
        }

        return response()->json($createDirectoryResponse);
    }

    /**
     * Create new file
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam fileName string required name of the file to be created
     *
     * @response 200
     * {
     *     "result": {
     *         "status": "success",
     *         "message": "fileCreated"
     *     },
     *     "file": {
     *         "type": "file",
     *         "path": "6f24d2b6-2ab5-4c30-844c-bf1f8af1f16a\/chd.cpp",
     *         "basename": "chd.cpp",
     *         "dirname": "6f24d2b6-2ab5-4c30-844c-bf1f8af1f16a",
     *         "extension": "cpp",
     *         "filename": "chd",
     *         "size": 0,
     *         "timestamp": 1716211133,
     *         "visibility": "public"
     *     }
     * }
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function createFile(
        RequestValidator $request
    ): JsonResponse
    {
        try {
            $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));

            if ($this->configRepository->getAcl() && !$this->acl->getAccessLevel($request->input('disk'), $path)) {
                throw new AuthorizationException(__('Unauthorized'), Response::HTTP_UNAUTHORIZED);
            }

            # Mapping `fileName` input to `name` as expected by the `FileCreating` event
            $request->input('name') ?: $request->merge(['name' => $request->input('fileName')]);

            event(new FileCreating($request));

            $originalPath = (str_ends_with($request->input('originalPath'), "/")) ?
                substr($request->input('originalPath'), 0, -1) :
                $request->input('originalPath');

            $this->fm->createFile($request->input('disk'), $originalPath, $request->input('fileName'), $properties);

            event(new FileCreated($request));

            return response()->json([
                'result' => [
                    'status' => 'success',
                    'message' => 'fileCreated'
                ],

                'file' => $properties
            ]);
        } catch (Exception $e) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json([
                'status' => $statusCode,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Update file
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return JsonResponse
     */
    public function updateFile(RequestValidator $request)
    {
        $acl = resolve(ACL::class);
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        event(new FileUpdate($request));

        return response()->json(
            $this->fm->updateFile(
                $request->input('disk'),
                $request->input('originalPath'),
                $request->file('file')
            )
        );
    }

    /**
     * Stream file
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     *
     * @return mixed
     */
    public function streamFile(RequestValidator $request)
    {
        return $this->fm->streamFile(
            $request->input('disk'),
            $request->input('originalPath')
        );
    }

    /**
     * Create zip archive
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam name string required name of zip file to create
     * @bodyParam elements object required object contain array of directories
     *
     * @response 200
     * {"message":"The file will be zipped, and you will be notified"}
     *
     * @param RequestValidator $request
     *
     * @return array|JsonResponse
     */
    public function zip(RequestValidator $request)
    {

        $acl = resolve(ACL::class);
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        $path = str_replace(".zip", "", $request->input('path'));
        $pathExplode = explode('/', $path);
        array_pop($pathExplode);
        $putPath = implode('/', $pathExplode);
        $is_dir = false;
        if (count($request->elements['directories'])) {
            $is_dir = true;
            $elements = implode("|", $request->elements['directories']);
        } else if (count($request->elements['files'])) {
            $is_dir = false;
            $elements = implode("|", $request->elements['files']);
        }

        event(new ZipDirectoryEvent(['disk' => $request->input('disk'),
            "putPath" => $putPath,
            "path" => $path,
            "name" => $request->input('name'),
            "is_dir" => $is_dir,
            "elements" => $elements,
            "user" => $request->user()]));
        return ['message' => __('The file will be zipped, and you will be notified')];
    }

    /**
     * Extract zip archive
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     * @param Zip              $zip
     *
     * @return array|JsonResponse
     */
    public function unzip(RequestValidator $request, Zip $zip)
    {
        $acl = resolve(ACL::class);
        $path = str_replace(request()->tenant->uuid . '/', '', $request->input('path'));
        if ($this->configRepository->getAcl() && !$acl->getAccessLevel($request->input('disk'), $path)) {
            return response()->json([
                'message' => __('Unauthorized'),
                'status' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

//        event(new UnzipEvent($request));
        $current_folder = '';
        $url_arr = explode('/', $request->originalPath);
        $file_name = array_pop($url_arr);
        if (!$request->input('folder')) {
            $current_folder = implode('/', $url_arr);
        } else {
            $url_arr[] = $request->input('folder');
            $current_folder = implode('/', $url_arr);
        }
        event(new UnzipDirectoryEvent(['disk' => $request->input('disk'),
            'folder' => null,
            'file' => $request->originalPath,
            'to' => $current_folder,
            "user" => $request->user()]));
        return ['message' => __('The file will be unzipped, and you will be notified')];
//         $request->merge(['path'=>$request->originalPath,'folder'=>$current_folder]);
//         return $zip->extract();
    }

    /**
     * Integration with ckeditor 4
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Request $request
     *
     * @return Factory|View
     */
    public function ckeditor()
    {
        return view('file-manager::ckeditor');
    }

    /**
     * Integration with TinyMCE v4
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @return Factory|View
     */
    public function tinymce()
    {
        return view('file-manager::tinymce');
    }

    /**
     * Integration with TinyMCE v5
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @return Factory|View
     */
    public function tinymce5()
    {
        return view('file-manager::tinymce5');
    }

    /**
     * Integration with SummerNote
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @return Factory|View
     */
    public function summernote()
    {
        return view('file-manager::summernote');
    }

    /**
     * Simple integration with input field
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @return Factory|View
     */
    public function fmButton()
    {
        return view('file-manager::fmButton');
    }

    /**
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param RequestValidator $request
     * @return bool
     */
    public function IsExternal(RequestValidator $request): bool
    {
        $pathArray = explode('/', $request->input('path'));
        array_pop($pathArray);
        $path = implode('/', $pathArray);
        return (bool)FileManagerModel::where('path', $path)->first('external')?->external;
    }

    /**
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param FileTagRequest $filesTag
     * @return JsonResponse|void
     */
    public function syncTag(FileTagRequest $filesTag)
    {
        foreach ($filesTag['files'] as $file) {
            $pathArray = explode('/', $file['path']);
            $filename = array_pop($pathArray);
            $path = implode('/', $pathArray);
            if ($filepath = FileManagerModel::where([['path', '=', $path], ['name', '=', $filename]])->first()) {
                $filepath->tags()->sync($file['tags']);
                return response()->json([
                    'message' => __('Tag has been added successfully'),
                    'status' => \Symfony\Component\HttpFoundation\Response::HTTP_OK
                ], Response::HTTP_OK);
            }
        }
    }

    public function search()
    {
        return FilemanagerResource::collection(
            FileManagerModel::with('tags')->withScopes($this->scope())
                ->orderBy(request()->sortBy ?? 'id', request()->sortDir ?? 'asc')
                ->paginate(request()->perPage ?? 10)
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null
        ]);
    }

    /**
     * @return string[]
     */
    public function scope(): array
    {

        return [
            'disk' => new DiskScope(),
            'name' => new SearchNameScope(),
//            'tags' => '',
//            'search' => ''

        ];
    }
}
