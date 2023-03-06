<?php

namespace App\Services\Api;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Transformers\File\FileTransformer;

class FileService
{

    // Create File Path
    public function fileCreate($request)
    {
        try {
            $file = Storage::disk('s3')->put('public' . "/" . date("Y") . "/" . date("m"), $request->file);
            $data = [
                "URL" => Storage::disk('s3')->temporaryUrl($file, Carbon::now()->addDays(5)),
                "path" => $file,
            ];
            return fractal()->item($data)->transformWith(new FileTransformer)->toArray();
        } catch (Exception $e) {
            DB::rollback();
            throw new \RuntimeException($e);
        }
    }

    // Delete File Path
    public function fileDelete($request)
    {
        try {
            if ($request->url) {
                $url = str_replace(URL::to('/') . '/', '', $request->url);
            } else {
                $url = str_replace("public/", "", $request->path);
            }
            if (Storage::disk('s3')->exists('public/' . $url)) {
                Storage::disk('s3')->delete('public/' . $url);
            } else {
                return response()->json(['message' => 'File Not Exist']);
            }
            return response()->json(['message' => trans('messages.file_delete')], 200);
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Download File
    public function downloadFile($request)
    {
        try {
            if (Storage::disk('s3')->has($request->path)) {
                $data = Storage::disk('s3')->get($request->path);
                $getMimeType = Storage::disk('s3')->getMimetype($request->path);
                $extension = pathinfo(storage_path($request->path), PATHINFO_EXTENSION);
                $newFileName = 'filename.' . $extension;
                $headers = [
                    'Content-type' => $getMimeType,
                    'Content-Disposition' => sprintf('attachment; filename="%s"', $newFileName)
                ];
                return Response::make($data, 200, $headers);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
