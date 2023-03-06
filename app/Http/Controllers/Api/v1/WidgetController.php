<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\WidgetService;
use App\Http\Controllers\Controller;

class WidgetController extends Controller
{
    // List Wiget
    public function getWidget()
    {
        return (new WidgetService)->getWidget();
    }

    // Add Assign Widget
    public function assignwidget(Request $request)
    {
        return (new WidgetService)->assignwidget($request);
    }

    // Get Assigned Widget
    public function getassignedWidget()
    {
        return (new WidgetService)->getassignedWidget();
    }

    // Update Widget
    public function updateWidget(request $request, $id)
    {
        return (new WidgetService)->updateWidget($request, $id);
    }

    // List Widget Access
    public function listWidgetAccess(Request $request, $id)
    {
        return (new WidgetService)->listWidgetAccess($request, $id);
    }

    // Add Widget Access
    public function createWidgetAccess(Request $request, $id)
    {
        return (new WidgetService)->createWidgetAccess($request, $id);
    }

    // Delete Widget Access
    public function deleteWidgetAccess(Request $request, $id)
    {
        return (new WidgetService)->deleteWidgetAccess($request, $id);
    }

    //Add Dashboard Widgets
    public function addDashboardWidget(Request $request, $id = NULL)
    {
        return (new WidgetService)->addDashboardWidget($request, $id);
    }
    //Dashboard Widget List
    public function dashboardWidgetList(Request $request)
    {
        return (new WidgetService)->dashboardWidgetList($request);
    }
}
