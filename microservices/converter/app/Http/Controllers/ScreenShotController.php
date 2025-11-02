<?php
namespace App\Http\Controllers;

use App\Http\Requests\FromUrlRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class ScreenShotController extends Controller
{

    public function fromUrl(Request $request)
    {

        $type = $request->type??"pdf";
        $quality = $request->quality??100;
        $meme = (strtolower($type)==="pdf")?"application/pdf":"image/".strtolower($type);

        $request->merge(
            [
                "type"=>$type,
                "meme"=>$meme,
                "quality"=>$quality,
                "deviceScaleFactor" => $request->deviceScaleFactor??2
            ]
        );

        $this->validate(
            $request,
            [
                'url'   => 'required|active_url',
                'type'  => 'required|in:pdf,png,jpg,jpeg',
                'meme'  => 'required',
                'name'  => 'required|string',
                'options' => 'required|array',
                'options.*'=> 'string|in:fullPage,hideBackground,noSandbox,showBackground',
                'width' => 'nullable|integer',
                'height' => 'nullable|integer',
                'quality' => 'required|integer|min:0|max:100',
                'deviceScaleFactor' => 'nullable|integer',
                'format' => "nullable|in:a0,a1,a2,a3,a4,a5,a6,a7"
            ]
        );
        $fileData = Browsershot::url($request->url)
                                ->ignoreHttpsErrors()
                                ->preventUnsuccessfulResponse()
                                ->waitUntilNetworkIdle(false)
                                ->timeout(120);
        if ($request->type !== "png") {
            $fileData=$fileData->setScreenshotType($request->type, $request->quality);
        }

        if ($request->deviceScaleFactor) {
            $fileData = $fileData->deviceScaleFactor($request->deviceScaleFactor);
        }
        if ($request->width && $request->height) {
            $fileData = $fileData->paperSize($request->width, $request->height);
        }
        if ($request->format) {
            $fileData = $fileData->format($request->format);
        }
        foreach ($request->options as $option) {
            $fileData = $fileData->{$option}();
        }
        // un hash it for development check
        // $fileData = $fileData->save(storage_path("app/$request->name"));
        // return response()->download(storage_path("app/$request->name"));
        if ($request->type === "pdf") {
            $fileData = $fileData->pdf();
        } else {
            $fileData = $fileData->screenshot();
        }
        return response($fileData)->header("Content-Type", $request->meme);
    }
}
