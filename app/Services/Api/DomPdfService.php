<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Storage;
use Exception;

class DomPdfService
{

    public function createPdfFile($data, $template)
    {
        try {
            $filename = time() . '.pdf';
            //$pdf = \Pdf::loadView('pdf.'.$template,$data);
            $html = view('pdf.' . $template, $data)->render();
            $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
            //die;
            $pdf = \Pdf::loadHTML($html);
            $file = $pdf->stream();
            if ($file) {
                Storage::disk('public')->put($filename, $file);
                return $filename;
            } else {
                return '';
            }
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}

