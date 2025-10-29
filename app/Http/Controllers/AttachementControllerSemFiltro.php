<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachementController extends Controller
{
    public function index()
    {
        $attachments = Attachment::with('user')->get();
        return view('attachment.index', compact('attachments'));
    }

    public function create()
    {
        $users = User::all();
        return view('attachment.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'void_check'        => 'nullable|file',
            'w9'                => 'nullable|file',
            'coi'               => 'nullable|file',
            'proof_fmcsa'       => 'nullable|file',
            'drivers_license'   => 'nullable|file',
            'truck_picture_1'   => 'nullable|file',
            'truck_picture_2'   => 'nullable|file',
            'truck_picture_3'   => 'nullable|file',
        ]);

        $data = ['user_id' => $request->user_id];

        // Base para os uploads em public/attachments/{user_id}
        $baseDir = public_path("attachments/{$request->user_id}");
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $fields = [
            'void_check',
            'w9',
            'coi',
            'proof_fmcsa',
            'drivers_license',
            'truck_picture_1',
            'truck_picture_2',
            'truck_picture_3',
        ];

        foreach ($fields as $field) {
            if ($request->hasFile($field)) {
                $file     = $request->file($field);
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                // Move para public/attachments/{user_id}
                $file->move($baseDir, $filename);
                // Grava a URL completa no banco, a partir de public/
                $data["{$field}_path"] = asset("attachments/{$request->user_id}/{$filename}");
            }
        }

        Attachment::create($data);

        return redirect()
            ->route('attachments.index')
            ->with('success', 'Arquivos enviados com sucesso!');
    }

    public function show(string $id)
    {
        $attachment = Attachment::with('user')->findOrFail($id);
        return view('attachment.show', compact('attachment'));
    }

    public function edit(string $id)
    {
        $attachment = Attachment::findOrFail($id);
        $users = User::all();
        return view('attachment.edit', compact('attachment', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $attachment = Attachment::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'void_check' => 'nullable|file',
            'w9' => 'nullable|file',
            'coi' => 'nullable|file',
            'proof_fmcsa' => 'nullable|file',
            'drivers_license' => 'nullable|file',
            'truck_picture_1' => 'nullable|file',
            'truck_picture_2' => 'nullable|file',
            'truck_picture_3' => 'nullable|file',
        ]);

        $attachment->user_id = $request->user_id;

        foreach ([
            'void_check',
            'w9',
            'coi',
            'proof_fmcsa',
            'drivers_license',
            'truck_picture_1',
            'truck_picture_2',
            'truck_picture_3'
        ] as $field) {
            if ($request->hasFile($field)) {
                // Deleta o antigo
                if ($attachment["{$field}_path"]) {
                    Storage::disk('public')->delete($attachment["{$field}_path"]);
                }

                $file = $request->file($field);
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs("attachments/{$request->user_id}", $filename, 'public');
                $attachment["{$field}_path"] = $path;
            }
        }

        $attachment->save();

        return redirect()->route('attachments.index')->with('success', 'Arquivos atualizados com sucesso!');
    }

    public function destroy(string $id)
    {
        $attachment = Attachment::findOrFail($id);

        foreach ([
            'void_check_path',
            'w9_path',
            'coi_path',
            'proof_fmcsa_path',
            'drivers_license_path',
            'truck_picture_1_path',
            'truck_picture_2_path',
            'truck_picture_3_path'
        ] as $field) {
            if ($attachment[$field]) {
                Storage::disk('public')->delete($attachment[$field]);
            }
        }

        $attachment->delete();

        return redirect()->route('attachments.index')->with('success', 'Documentos removidos com sucesso!');
    }
}
