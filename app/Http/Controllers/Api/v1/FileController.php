<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\FileService;
use App\Http\Controllers\Controller;
use App\Http\Requests\File\FileRequest;

class FileController extends Controller
{
    // Craete File Path
    public function createFile(FileRequest $request)
    {
        return (new FileService)->fileCreate($request);
    }

    // Delete File Path
    public function deleteFile(Request $request)
    {
        return (new FileService)->fileDelete($request);
    }

     // Download File 
     public function download(Request $request)
     {
         return (new FileService)->downloadFile($request);
     }

    
}
