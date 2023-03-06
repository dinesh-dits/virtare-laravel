<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Api\ContactService;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Requests\Contact\ContactTextRequest;
use App\Http\Requests\Contact\ContactEmailRequest;

class ContactController extends Controller
{
    // List Request Call
    public function index(ContactRequest $request)
    {
        return (new ContactService)->requestCall($request);
    }

    // List Request Call
    public function requestContactList(Request $request)
    {
        return (new ContactService)->requestContactList($request);
    }

    // Update Request Call
    public function requestcallUpdate(Request $request, $patientId, $id)
    {
        return (new ContactService)->requestcallUpdate($request, $patientId, $id);
    }

    // Add Contact Message
    public function contactMessage(ContactTextRequest $request)
    {
        return (new ContactService)->contactMessage($request);
    }

    // Contact Email
    public function contactEmail(ContactEmailRequest $request)
    {
        return (new ContactService)->contactEmail($request);
    }

    // Add Contact (Client & Site)
    public function addContact(Request $request, $entity, $id)
    {
        return (new ContactService)->contactAdd($request, $entity, $id);
    }

    // List Contact (Client & Site)
    public function listContact(Request $request, $entity, $id, $contactId = null)
    {
        return (new ContactService)->contactList($request, $entity, $id, $contactId);
    }

    // Update Contact (Client & Site)
    public function updateContact(Request $request, $entity, $id, $contactId)
    {
        return (new ContactService)->contactUpdate($request, $entity, $id, $contactId);
    }

    // Delete Contact (Client & Site)
    public function deleteContact(Request $request, $entity, $id, $contactId)
    {
        return (new ContactService)->contactDelete($request, $entity, $id, $contactId);
    }
}
