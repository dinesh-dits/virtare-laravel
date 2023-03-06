<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Services\Api\NoteService;
use App\Http\Controllers\Controller;

class NoteController extends Controller
{
    // Add Notes
    public function addNote(Request $request, $entity, $id)
    {
        return (new NoteService)->noteAdd($request, $entity, $id);
    }

    // List Notes
    public function listNote(Request $request, $entity, $id = null, $noteId = null)
    {
        return (new NoteService)->noteList($request, $entity, $id, $noteId);
    }

    // Patient Notes
    public function patientNote(Request $request)
    {
        return (new NoteService)->patientNoteList($request);
    }
}
