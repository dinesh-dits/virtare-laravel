<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Requests\Workflow\WorkflowRequest;
use App\Services\Api\WorkflowService;
use App\Http\Controllers\Controller;

class WorkflowController extends Controller
{

    /*
      function used for add a new workflow
    */

    public function add(WorkflowRequest $request)
    {
        return (new WorkflowService)->add($request);
    }

    /*
      function used for update a new workflow
    */

    public function update(WorkflowRequest $request, $id)
    {
        return (new WorkflowService)->update($request, $id);
    }

    /*
      function used for list/detail of workflow if UDID is provided.
    */

    public function list(Request $request, $id = null)
    {
        return (new WorkflowService)->get($request, $id);
    }

    /*
      function used for list/detail of workflow Events if UDID is provided.
    */

    public function event(Request $request, $id = null)
    {
        return (new WorkflowService)->events($request, $id);
    }

    /*
      function used for list/detail of workflow Events Columns if UDID is provided.
    */

    public function column(Request $request, $eventId = null)
    {
        return (new WorkflowService)->columns($request, $eventId);
    }

    /*
      function used for list/detail of workflow Events Action if UDID is provided.
    */

    public function action(Request $request, $eventId = null)
    {
        return (new WorkflowService)->action($request, $eventId);
    }

    /*
      function used for update criteria for workflow with provided udid
    */

    public function addCriteria(Request $request, $id = null)
    {
        return (new WorkflowService)->addCriteria($request, $id);
    }

    /*
      function used for get criteria for workflow with provided udid
    */

    public function getCriteria(Request $request, $id = null)
    {
        return (new WorkflowService)->getCriteria($request, $id);
    }

    /*
      function used for Add Steps for workflow with provided udid
    */

    public function addStep(Request $request, $id = null)
    {
        return (new WorkflowService)->addStep($request, $id);
    }

    /*
      function used for Add Steps for workflow with provided udid
    */

    public function getStep(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->getStep($request, $workFlowId, $id);
    }

    /*
      function used for Update Steps for workflow with provided udid
    */

    public function updateStep(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->updateStep($request, $workFlowId, $id);
    }

    /*
      function used for Update Steps for workflow with provided udid
    */

    public function deleteStep(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->deleteStep($request, $workFlowId, $id);
    }

    /*
      function used for get offset field of selected enent for workflow
    */

    public function offset(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->offset($request, $workFlowId, $id);
    }

    /*
      function used for Field of Alert selected by user.
    */

    public function alertField(Request $request, $workFlowId, $id)
    {
        return (new WorkflowService)->alertField($request, $workFlowId, $id);
    }

    /*
      function used for save Action of alert selected.
    */

    public function addStepAction(Request $request, $workFlowId)
    {
        return (new WorkflowService)->addStepAction($request, $workFlowId);
    }

    /*
      function used for Detail of Action of alert selected.
    */

    public function getStepAction(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->getStepAction($request, $workFlowId, $id);
    }

    /*
      function used for Detail of Action of alert selected.
    */

    public function updateStepAction(Request $request, $workFlowId, $id)
    {
        return (new WorkflowService)->updateStepAction($request, $workFlowId, $id);
    }

    /*
      function used for Delete of Action of alert selected.
    */

    public function deleteStepAction(Request $request, $workFlowId, $id = null)
    {
        return (new WorkflowService)->deleteStepAction($request, $workFlowId, $id);
    }

    public function assign_workflow()
    {
        return (new WorkflowService)->assign_workflow();
    }
}
