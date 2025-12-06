<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Models\permissions_roles;
use App\Models\RolesUsers;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\DB;

class adminController extends Controller
{


     public function permissoes()
    {
        $user = Auth::user();

        $permissoes = Permission::all();

        // return $permissoes;
        return view('admin.app_permissions', compact('user', 'permissoes'));
    }

    public function myProfile(){

        $user = Auth::user();

        return view('admin.app_meu_perfil', compact('user'));

    }


    public function logout()
    {

        Session::flush();

        Auth::logout();

        return redirect('login');
    }


    public function roles_users()
{
    $user = Auth::user();
    $roles = Role::all();           // todas as roles
    $users = User::all();

    // pega, via query, o role_id de cada user_id
    $userRoleMap = DB::table('roles_users')
        ->pluck('role_id', 'user_id'); // [ user_id => role_id, ... ]

    return view('admin.app_roles_users', compact('roles', 'users', 'userRoleMap', 'user'));
}


    public function permissions_roles(){
        $user = Auth::user();


        $role_id = 1;
        $roles = Role::all();
        $permissions = Permission::all();

        $permissions_roles = DB::table('permissions_roles')
        ->join('permissions', 'permissions.id', '=', 'permissions_roles.permission_id')
        ->join('roles', 'roles.id', '=', 'permissions_roles.role_id')
        ->where('permissions_roles.role_id', '=', $role_id)
        ->select('permissions.*', 'permissions_roles.*')
        ->get();

        $selected = [];

        foreach ($permissions_roles as $option) {
            $selected[] = $option->name;
        }

        return view('admin.app_permissions_roles', compact('user','permissions_roles', 'role_id', 'roles', 'selected'));
    }

    public function permissions_roles_by_id($id){

        $user = Auth::user();

        $role_id = $id;
        $roles = Role::all();
        $permissions = Permission::all();

        $permissions_roles = DB::table('permissions_roles')
        ->join('permissions', 'permissions.id', '=', 'permissions_roles.permission_id')
        ->join('roles', 'roles.id', '=', 'permissions_roles.role_id')
        ->where('permissions_roles.role_id', '=', $role_id)
        ->select('permissions.*', 'permissions_roles.*')
        ->get();

        $selected = [];

        foreach ($permissions_roles as $option) {
            $selected[] = $option->name;
        }

        return view('admin.app_permissions_roles', compact('user','permissions_roles', 'role_id', 'roles', 'selected'));
    }


    public function salvar_permissions_roles(Request $request)
    {

        $user = Auth::user();



        // ================ PERMISSÃO PARA VISUALIZAR ========================
        if($request->visualizacao)
        {
            foreach ($request->visualizacao as $option) {

                $permissao_id = Permission::where('name', $option)->first();

                if(!$permissao_id){
                    $permissao = new Permission();
                    $permissao->name = $option;
                    $permissao->description = $option;
                    $permissao->save();
                }

                $permissao_id = Permission::where('name', $option)->first()->id;

                $localizar = permissions_roles::where('permission_id', $permissao_id)
                ->where('role_id', $request->role_id)->first();
                if(!$localizar){
                    $roles = new permissions_roles();
                    $roles->permission_id = $permissao_id;
                    $roles->role_id = $request->role_id;

                    $roles->save();
                }
            }

            $permissoes_visualizar = Permission::where('name', 'like', '%visualizar%')->get();

            foreach ($permissoes_visualizar as $item) {
                if (!in_array($item->name, $request->visualizacao)) {
                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);
                    }
                }
            }
        }
        else{

            $permissoes_visualizar = Permission::where('name', 'like', '%visualizar%')->get();
            foreach ($permissoes_visualizar as $item) {

                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();

                    echo $item->id." - ";
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);

                    }
            }
        }


        // ================ PERMISSÃO PARA CADASTRAR ========================
        if($request->inclusao)
        {
            foreach ($request->inclusao as $option) {

                $permissao_id = Permission::where('name', $option)->first();

                if(!$permissao_id){
                    $permissao = new Permission();
                    $permissao->name = $option;
                    $permissao->description = $option;
                    $permissao->save();
                }

                $permissao_id = Permission::where('name', $option)->first()->id;

                $localizar = permissions_roles::where('permission_id', $permissao_id)
                ->where('role_id', $request->role_id)->first();
                if(!$localizar){
                    $roles = new permissions_roles();
                    $roles->permission_id = $permissao_id;
                    $roles->role_id = $request->role_id;

                    $roles->save();
                }
            }

            $permissoes_visualizar = Permission::where('name', 'like', '%registrar%')->get();

            foreach ($permissoes_visualizar as $item) {
                if (!in_array($item->name, $request->inclusao)) {
                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);
                    }
                }
            }

        }
        else{

            $permissoes_visualizar = Permission::where('name', 'like', '%registrar%')->get();
            foreach ($permissoes_visualizar as $item) {

                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();

                    echo $item->id." - ";
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);
                    }
            }
        }

        // // ================ PERMISSÃO PARA EDITAR ========================
        if($request->edicao)
        {
            foreach ($request->edicao as $option) {

                $permissao_id = Permission::where('name', $option)->first();

                if(!$permissao_id){
                    $permissao = new Permission();
                    $permissao->name = $option;
                    $permissao->description = $option;
                    $permissao->save();
                }

                $permissao_id = Permission::where('name', $option)->first()->id;

                $localizar = permissions_roles::where('permission_id', $permissao_id)
                ->where('role_id', $request->role_id)->first();
                if(!$localizar){
                    $roles = new permissions_roles();
                    $roles->permission_id = $permissao_id;
                    $roles->role_id = $request->role_id;

                    $roles->save();
                }
            }

            $permissoes_visualizar = Permission::where('name', 'like', '%editar%')->get();

            foreach ($permissoes_visualizar as $item) {
                if (!in_array($item->name, $request->edicao)) {
                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);
                    }
                }
            }



        }
        else{

            $permissoes_visualizar = Permission::where('name', 'like', '%editar%')->get();
            foreach ($permissoes_visualizar as $item) {

                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();

                    echo $item->id." - ";
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);

                    }

            }
        }

        // // ================ PERMISSÃO PARA EXCLUIR ========================
        if($request->exclusao)
        {
            foreach ($request->exclusao as $option) {

                $permissao_id = Permission::where('name', $option)->first();

                if(!$permissao_id){
                    $permissao = new Permission();
                    $permissao->name = $option;
                    $permissao->description = $option;
                    $permissao->save();
                }

                $permissao_id = Permission::where('name', $option)->first()->id;

                $localizar = permissions_roles::where('permission_id', $permissao_id)
                ->where('role_id', $request->role_id)->first();
                if(!$localizar){
                    $roles = new permissions_roles();
                    $roles->permission_id = $permissao_id;
                    $roles->role_id = $request->role_id;

                    $roles->save();
                }
            }

            $permissoes_visualizar = Permission::where('name', 'like', '%eliminar%')->get();

            foreach ($permissoes_visualizar as $item) {
                if (!in_array($item->name, $request->exclusao)) {
                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);
                    }
                }
            }



        }
        else{

            $permissoes_visualizar = Permission::where('name', 'like', '%eliminar%')->get();
            foreach ($permissoes_visualizar as $item) {

                    $localizar_id_ignorado = permissions_roles::where('permission_id', $item->id)
                        ->where('role_id', $request->role_id)
                        ->first();

                    echo $item->id." - ";
                    if ($localizar_id_ignorado) {

                        permissions_roles::destroy($localizar_id_ignorado->id);

                    }

            }
        }

        Alert::toast('Alteração efetuada Com Sucesso', 'success');
        return back();
    }

    public function salvar_roles_users(Request $request)
    {
        //
        $roles = new RolesUsers();
        $roles->user_id = $request->user_id;
        $roles->role_id = $request->role_id;

        $roles->save();

        Alert::toast('Alteração efetuada Com Sucesso', 'success');
        return $roles;
    }

    public function actualizar_roles_users(Request $request)
    {
        $user = Auth::user();

        //
        $user_id = RolesUsers::where('user_id', $request->user_id)->first();

        if($user_id){
            $roles = RolesUsers::find($user_id->id);
            $roles->user_id = $request->user_id;
            $roles->role_id = $request->role_id;

            $roles->save();

            return $roles;
        }
        else{

            $roles = new RolesUsers();
            $roles->user_id = $request->user_id;
            $roles->role_id = $request->role_id;

            $roles->save();

            Alert::toast('Alteração efetuada Com Sucesso', 'success');
            return back();
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        Log::info('Profile update started (updateProfile)', [
            'user_id' => $user->id,
            'has_photo' => $request->hasFile('photo'),
            'has_logo' => $request->hasFile('logo'),
            'is_owner' => $user->is_owner,
            'all_files' => $request->allFiles(),
        ]);

        // Validar dados básicos
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // 2MB max - apenas para owner
        ]);

        // Atualizar nome e email
        $user->name = $request->name;
        $user->email = $request->email;

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            
            Log::info('Photo file received', [
                'user_id' => $user->id,
                'file_name' => $photo->getClientOriginalName(),
                'file_size' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'extension' => $photo->getClientOriginalExtension(),
            ]);

            // Create user-specific directory
            $userDir = 'profile-photos/user-' . $user->id;
            
            // Create directory if it doesn't exist
            $storagePath = storage_path('app/public/' . $userDir);
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                Log::info('Created directory', ['path' => $storagePath]);
            }

            // Delete old photo if exists
            if ($user->photo) {
                $oldPhotoPath = storage_path('app/public/' . $user->photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                    Log::info('Deleted old photo', ['path' => $oldPhotoPath]);
                }
            }

            // Generate unique filename
            $filename = 'photo-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store photo using Laravel's storage
            // storeAs returns path like: public/profile-photos/user-1/photo-123.jpg
            $storedPath = $photo->storeAs('public/' . $userDir, $filename);
            
            Log::info('Photo stored', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/' . $storedPath),
                'file_exists' => file_exists(storage_path('app/' . $storedPath)),
            ]);
            
            // Save photo path (relative to storage/app/public)
            // Remove 'public/' prefix from stored path
            // storedPath: "public/profile-photos/user-1/photo-123.jpg"
            // user->photo: "profile-photos/user-1/photo-123.jpg"
            $user->photo = preg_replace('/^public\//', '', $storedPath);
            
            Log::info('Photo path saved to user', [
                'user_id' => $user->id,
                'photo_path' => $user->photo,
                'asset_path' => asset('storage/' . $user->photo),
            ]);
        }

        // Handle logo upload (only for dispatcher owner)
        if ($user->is_owner && $request->hasFile('logo')) {
            $logo = $request->file('logo');
            
            Log::info('Logo file received', [
                'user_id' => $user->id,
                'file_name' => $logo->getClientOriginalName(),
                'file_size' => $logo->getSize(),
                'mime_type' => $logo->getMimeType(),
                'extension' => $logo->getClientOriginalExtension(),
            ]);

            // Create user-specific directory for logos
            $logoDir = 'logos/user-' . $user->id;
            
            // Create directory if it doesn't exist
            $storagePath = storage_path('app/public/' . $logoDir);
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
                Log::info('Created logo directory', ['path' => $storagePath]);
            }

            // Delete old logo if exists
            if ($user->logo) {
                $oldLogoPath = storage_path('app/public/' . $user->logo);
                if (file_exists($oldLogoPath)) {
                    @unlink($oldLogoPath);
                    Log::info('Deleted old logo', ['path' => $oldLogoPath]);
                }
            }

            // Generate unique filename
            $filename = 'logo-' . time() . '.' . $logo->getClientOriginalExtension();
            
            // Store logo using Laravel's storage
            $storedPath = $logo->storeAs('public/' . $logoDir, $filename);
            
            Log::info('Logo stored', [
                'stored_path' => $storedPath,
                'full_path' => storage_path('app/' . $storedPath),
                'file_exists' => file_exists(storage_path('app/' . $storedPath)),
            ]);
            
            // Save logo path (relative to storage/app/public)
            $user->logo = preg_replace('/^public\//', '', $storedPath);
            
            Log::info('Logo path saved to user', [
                'user_id' => $user->id,
                'logo_path' => $user->logo,
                'asset_path' => asset('storage/' . $user->logo),
            ]);
        }

        $user->save();
        
        Log::info('User saved', [
            'user_id' => $user->id,
            'photo' => $user->photo,
        ]);

        Alert::toast('Profile updated successfully!', 'success');
        return back();
    }
}
