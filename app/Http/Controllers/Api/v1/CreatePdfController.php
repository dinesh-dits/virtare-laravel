<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Helper;
use Mailjet\Client;
use \Mailjet\Resources;
use App\Models\GlobalCode\GlobalCode;
use App\Models\GlobalCode\GlobalCodeCategory;
use Illuminate\Support\Str;

#use Barryvdh\DomPDF\Facade\Pdf;
#use Barryvdh\Snappy\Facades\SnappyImage;


class CreatePdfController extends Controller
{

    public function createTask()
    {
        $data = Storage::disk('s3')->get('public/2022/11/Lk69F4gJr6YybgkVveVOZ3TVh6O0GOw6EIyIpt3a.png');
        echo $data;
        die;
        $jira_key = env('JIRA_KEY', '');
        $jira_email = env('JIRA_EMAIL', '');

        /*$data["fields"]["summary"] = "TEST post from API";
        $data["fields"]["project"]["key"] = "VRH";
        $data["fields"]["parent"]["key"] = "VRH-7";
        $data["fields"]["issuetype"]['id'] = 10016;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_URL, "https://ditstek.atlassian.net/rest/api/3/issue");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$jira_email:$jira_key");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $server_output = curl_exec($ch);
        $response = json_decode($server_output);
        print_r( $response);
        $taskId = $response->id;
        curl_close($ch);*/

        /*
            stdClass Object
            (
                [id] => 11995
                [key] => VRH-1675
                [self] => https://ditstek.atlassian.net/rest/api/3/issue/11995
            )
            []string(2) "[]"*/

//$taskId = $response->key;


        $file = "https://virtare-health.s3.amazonaws.com/public/2022/11/vQ3LpNqywNQPob37E3J4AchytiBVCN06vTkDjjho.jpg?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAVZNMBCJLOHEVG7H2%2F20221110%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20221110T113535Z&X-Amz-SignedHeaders=host&X-Amz-Expires=432000&X-Amz-Signature=a4c1c400211d7070ce8dc54319392aa08f17a845ff11b2132c0a9d549d746bc9";
        $extension = pathinfo('storage/app/public/2022/01/h1jGijkPt5IFjNrz0tATwCfZyThDBU9RRyfCK6H3.png', PATHINFO_EXTENSION);
        echo $extension = current(explode('?', $extension));
        $file = file_get_contents($file);
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($file);


        $BOUNDARY = md5(time());

        $data = "--" . $BOUNDARY . "\r\n";
        $data .= "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename('1667453394.pdf') . "\"\r\n";
        $data .= "Content-Type: $mime_type \r\n";
        $data .= "\r\n";
        $data .= $file . "\r\n";
        $data .= "--" . $BOUNDARY . "--";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Atlassian-Token: no-check", "Content-Type: multipart/form-data;boundary=$BOUNDARY"));
        curl_setopt($ch, CURLOPT_URL, "https://ditstek.atlassian.net/rest/api/3/issue/VRH-1675/attachments");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$jira_email:$jira_key");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        $ch_error = curl_error($ch);
        if ($ch_error) {
            echo "cURL Error: $ch_error";
        } else {
            var_dump($result);
        }
        curl_close($ch);
        $server_output = curl_exec($ch);
    }

    public function createGlobalCodes()
    {

        $data = array('udid' => Str::uuid()->toString(), 'name' => 'Dashboard Category', 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $category = GlobalCodeCategory::create($data);
        $categoryId = $category->id;
        $globalCodes[0] = array('globalCodeCategoryId' => $categoryId, 'udid' => Str::uuid()->toString(), 'name' => '1-Column', 'description' => 1, 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalCodes[1] = array('globalCodeCategoryId' => $categoryId, 'udid' => Str::uuid()->toString(), 'name' => '2-Column', 'description' => 2, 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalCodes[2] = array('globalCodeCategoryId' => $categoryId, 'udid' => Str::uuid()->toString(), 'name' => '3-Column', 'description' => 3, 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalCodes[3] = array('globalCodeCategoryId' => $categoryId, 'udid' => Str::uuid()->toString(), 'name' => '4-Column', 'description' => 4, 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalCodes[4] = array('globalCodeCategoryId' => $categoryId, 'udid' => Str::uuid()->toString(), 'name' => '6-Column', 'description' => 6, 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        GlobalCode::insert($globalCodes);
    }

    public function createGlobalFilter()
    {

        $data = array('udid' => Str::uuid()->toString(), 'name' => 'Report Filter', 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $filter = GlobalCodeCategory::create($data);
        $filterId = $filter->id;
        $globalFilter[0] = array('globalCodeCategoryId' => $filterId, 'udid' => Str::uuid()->toString(), 'name' => 'Top', 'description' => 'top', 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalFilter[1] = array('globalCodeCategoryId' => $filterId, 'udid' => Str::uuid()->toString(), 'name' => 'Left', 'description' => 'left', 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);
        $globalFilter[2] = array('globalCodeCategoryId' => $filterId, 'udid' => Str::uuid()->toString(), 'name' => 'Right', 'description' => 'right', 'providerlocationId' => 1, 'providerId' => 1, 'entityType' => 'Country', 'programId' => 5, 'isActive' => 1, 'isDelete' => 0);

        GlobalCode::insert($globalFilter);
    }

    public function createPdf()
    {
        return $this->createTask();
        die;
        $fromEmail = env('MAILJET_FROM_ADDRESS', null);
        $apiKey = env('MAILJET_USERNAME', null);
        $secretKey = env('MAILJET_PASSWORD', null);
        $mj = new Client($apiKey, $secretKey, true, ['version' => 'v3']);
        $body = [
            'EventType' => "open",
            'IsBackup' => "false",
            'Status' => "alive",
            'Url' => "https://dev.icc-health.com/v2-dev/update-email-stats"
        ];
        $response = $mj->post(Resources::$Eventcallbackurl, ['body' => $body]);
        $response->success() && var_dump($response->getData());
        die;

        $filename = time() . '.pdf';

        $pdf = \PDFW::loadView('pdf.demo');

        return $pdf->download('graph.pdf');


        exit;
        echo Helper::getQrCode('http://localhost:8081/test');
        exit;
        return \PDFW::loadFile('http://127.0.0.1:8000/testpdf1')->inline('12github.pdf');
        exit;


        // return view('pdf.demo');exit;

        $pdf = \PDFW::loadView('pdf.demo');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('enable-javascript', true);
        $pdf->setOption('javascript-delay', 5000);
        $pdf->setOption('enable-smart-shrinking', true);
        $pdf->setOption('no-stop-slow-scripts', true);
        return $pdf->download('graph.pdf');

        exit;


        exit;

        $dd = 'https://www.npmjs.com/package/vue-speedometer';
        $image = Helper::getQrCode($dd);
        Storage::disk('public')->put('test.png', $image);
        die('STOP');

        echo 'sfffs';
        $base_url = env('APP_URL');
        $id = 10;
        $pdfData['ids'] = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . $base_url . rawurlencode("#") . "/escalation-action/" . $id . "&choe=UTF-8";
        Storage::disk('public')->put('test.png', file_get_contents($pdfData['ids']));


        echo $html = view('pdf.escaltion_pdf_file')->render();
        $filename = time() . '.pdf';
        die;

        //WKHTML pdf code
        /*$pdf=  \PDF::loadHtml($html)->inline($filename);
            Storage::disk('public')->put($filename,  $pdf); */

        $pdf = \Pdf::loadView('pdf.escaltion_pdf_file');
        //$pdffile= $pdf->download('invoice.pdf');
        $file = $pdf->stream();
        Storage::disk('public')->put($filename, $file);
    }
}
