<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\SiteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SiteRequest;

class SiteController extends Controller
{

    public function addSite(SiteRequest $request, $id)
    {
        return (new SiteService)->siteAdd($request, $id);
    }

    public function listSite(Request $request, $id, $siteId = null)
    {
        return (new SiteService)->siteList($request, $id, $siteId);
    }

    public function siteList(Request $request, $id)
    {
        return (new SiteService)->siteListArray($request, $id);
    }

    public function updateSite(Request $request, $id, $siteId)
    {
        return (new SiteService)->siteUpdate($request, $id, $siteId);
    }

    public function deleteSite(Request $request, $id, $siteId)
    {
        return (new SiteService)->siteDelete($request, $id, $siteId);
    }
}
