<?php

namespace App\Services\Api;

use Crabbly\Fpdf\Fpdf;
use App\Models\Patient\Patient;
use App\Models\ExportReportRequest\VitalPdfReportExportRequest;
use Exception;

class PdfGeneratorService
{
    // Vital PDF
    public static function vitalPdfExport($request, $id)
    {
        try {
            $resultData = VitalPdfReportExportRequest::where('udid', $id)->first();
            $patientVital = "";
            $timezone = "";
            if ($resultData) {
                if (isset($resultData->patientId)) {
                    $patient = Patient::find($resultData->patientId);

                    if (!empty($patient)) {
                        $patient = $patient->toArray();
                    }
                    if (isset($request->timezone) && !empty($request->timezone)) {
                        $timezone = $request->timezone;
                    } else {
                        if (isset($patient["customTimezone"])) {
                            $timezone = $patient["customTimezone"];
                        }
                    }
                    if (isset($request->deviceTypeId) && !empty($request->deviceTypeId)) {
                        $deviceTypeId = $request->deviceTypeId;
                    } else {
                        $deviceTypeId = "";
                    }
                } else {
                    $patient = "";
                }
                if (!empty($resultData->fromDate) && !empty($resultData->toDate)) {
                    $fromDate = date('Y-m-d H:s:i', $resultData->fromDate);
                    $toDate = date('Y-m-d H:s:i', $resultData->toDate);
                    $fieldId = $resultData->fieldId;
                    $fieldIdArr = explode(",", $fieldId);
                    if (isset($request->deviceTypeId) && !empty($request->deviceTypeId)) {
                        $deviceTypeId = $request->deviceTypeId;
                        $patientVital = Patient::getVitalsByPatinetId($resultData->patientId, $fromDate, $toDate, $fieldIdArr, $deviceTypeId);
                    } else {
                        $patientVital = Patient::getVitalsByPatinetId($resultData->patientId, $fromDate, $toDate, $fieldIdArr);
                    }
                } else {
                    $patientVital = Patient::getVitalsByPatinetId($resultData->patientId);
                }
            }
            if (!empty($timezone)) {
                date_default_timezone_set($timezone);
            }
            $pdf = new Fpdf;
            //set pdf header
            $fontName = "Times";
            if (!empty($patient)) {
                $title_name = "Report of " . $patient["firstName"] . " " . $patient["lastName"];
            } else {
                $title_name = "Report";
            }
            PdfGeneratorService::pdfStyle($pdf, $title_name, $fontName);
            $header = array('Date Time', 'Name', 'Value');
            // Set font format and font-size
            $pdf->SetFont('Times', 'B', 12);
            $pdf->SetDrawColor(2, 80, 180);
            $pdf->SetFillColor(220, 220, 0);
            PdfGeneratorService::headerTable($pdf, $header);
            if (!empty($patientVital)) {
                $patientVital = $patientVital->toArray();
                foreach ($patientVital as $val) {
                    $takeTime = date('M d, Y', strtotime($val["takeTime"]));
                    if (isset($val["vital_field_names"]["name"]) && !empty($val["vital_field_names"]["name"])) {
                        $vitalName = $val["vital_field_names"]["name"];
                    } else {
                        $vitalName = "";
                    }

                    $vitalValue = $val["value"];

                    $pdf->Cell(45, 6, $takeTime, 5, 0, 'C');
                    $pdf->Cell(45, 6, $vitalName, 5, 0, 'C');
                    $pdf->Cell(45, 6, $vitalValue, 5, 0, 'C');
                    $pdf->Ln();
                }
            }
            // Set it new line
            $pdf->Ln();

            // Close document and sent to the browser
            $pdf->Output();
            exit;
            //save file
            // Storage::put($pdf->Output('S'))
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Simple table
    public static function headerTable($pdf, $header)
    {
        try {
            // Header
            foreach ($header as $col) {
                $pdf->Cell(45, 6, $col, 5, 0, 'C');
            }
            $pdf->Ln();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }

    // Simple table
    public static function pdfStyle($pdf, $title_name, $fontName)
    {
        try {
            $pdf->AddPage();

            // Set font format and font-size
            $pdf->SetFont($fontName, 'B', 20);

            // Framed rectangular area
            $pdf->Cell(176, 5, $title_name, 0, 0, 'C');
            // header
            // Set font format and font-size
            $pdf->SetFont($fontName, '', 14);
            $pdf->Ln();
            $pdf->Ln();
        } catch (Exception $e) {
            throw new \RuntimeException($e);
        }
    }
}
