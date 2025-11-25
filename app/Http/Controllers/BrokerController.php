<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\RolesUsers;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\BillingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NewCarrierCredentialsMail;

class BrokerController extends Controller
{
    public function index()
    {
        $brokers = Broker::with('user')->paginate(10);
        return view('broker.index', compact('brokers'));
    }

    public function create()
    {
        $authUser = Auth::user();
        $adminTenantService = app(\App\Services\AdminTenantService::class);
        
        // ⭐ NOVO: Se for admin, verificar se está visualizando um tenant específico
        if ($authUser->isAdmin()) {
            if ($adminTenantService->isViewingAll()) {
                return redirect()->back()
                    ->with('error', 'Por favor, selecione um tenant específico no dropdown acima antes de criar um novo usuário.');
            }
            
            // Obter o tenant selecionado para validação
            $viewingTenantId = $adminTenantService->getViewingTenantId();
            $viewingTenant = $viewingTenantId ? User::find($viewingTenantId) : null;
            
            if (!$viewingTenant) {
                return redirect()->back()
                    ->with('error', 'Tenant selecionado não encontrado. Por favor, selecione um tenant válido.');
            }
        }
        
        // ⭐ VALIDAÇÃO PRIMEIRO: Verificar limite ANTES de qualquer coisa
        // Se for admin, usar tenant selecionado para validação
        if ($authUser->isAdmin()) {
            $userForValidation = $viewingTenant;
        } else {
            $userForValidation = $authUser;
        }
        
        $billingService = app(BillingService::class);
        $userLimitCheck = $billingService->checkUserLimit($userForValidation, 'broker');

        // Se não tiver permissão, SEMPRE redirecionar ANTES de mostrar formulário
        if (!($userLimitCheck['allowed'] ?? false)) {
            // Se sugerir upgrade, redirecionar para tela de planos
            if ($userLimitCheck['suggest_upgrade'] ?? false) {
                // ⭐ NOVO: Armazenar URL de origem para retornar após pagamento
                session(['return_url_after_payment' => route('brokers.create')]);
                
                return redirect()->route('subscription.build-plan')
                    ->with('error', $userLimitCheck['message'] ?? 'Limite atingido. Faça upgrade do seu plano.');
            }
            
            // Caso contrário, voltar com erro
            return redirect()->back()
                ->with('error', $userLimitCheck['message'] ?? 'Você não tem permissão para criar brokers.');
        }
        
        // Se chegou aqui, tem permissão - buscar dados para o formulário
        // ⭐ NOVO: Se for admin, passar lista de owners disponíveis
        $owners = $authUser->isAdmin() ? User::getAvailableOwners() : collect();
        
        return view('broker.create', compact('owners'));
    }

    public function store(Request $request)
    {
        // Validação básica
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'license_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'accounting_email' => 'nullable|email',
            'accounting_phone_number' => 'nullable|string',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ];
        
        // ⭐ NOVO: Se for admin, adicionar validação de owner_id
        if (Auth::user()->isAdmin()) {
            $validationRules['owner_id'] = 'required|exists:users,id';
        }
        
        $validator = Validator::make($request->all(), $validationRules, [
            'email.unique' => 'This email already exists...',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Gera senha automática baseada no nome
        $base = \Illuminate\Support\Str::of($request->input('name'))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '');
        $plainPassword = (string) $base.'2025';

        try {
            DB::beginTransaction();

            // Criar o usuário
            // ⭐ NOVO: Verificar limite de usuários antes de criar broker
            $authUser = Auth::user();
            $adminTenantService = app(\App\Services\AdminTenantService::class);
            
            // Se for admin, usar tenant selecionado para validação
            if ($authUser->isAdmin()) {
                if ($adminTenantService->isViewingAll()) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Por favor, selecione um tenant específico no dropdown acima antes de criar um novo usuário.');
                }
                
                $viewingTenantId = $adminTenantService->getViewingTenantId();
                $viewingTenant = $viewingTenantId ? User::find($viewingTenantId) : null;
                
                if (!$viewingTenant) {
                    DB::rollBack();
                    return redirect()->back()
                        ->with('error', 'Tenant selecionado não encontrado.');
                }
                
                $userForValidation = $viewingTenant;
            } else {
                $userForValidation = $authUser;
            }
            
            $billingService = app(BillingService::class);
            $userLimitCheck = $billingService->checkUserLimit($userForValidation, 'broker');

            if (!$userLimitCheck['allowed']) {
                // Se sugerir upgrade, redirecionar para montar plano
                if ($userLimitCheck['suggest_upgrade'] ?? false) {
                    // ⭐ NOVO: Armazenar URL de origem para retornar após pagamento
                    session(['return_url_after_payment' => route('brokers.create')]);
                    
                    return redirect()->route('subscription.build-plan')
                        ->with('error', $userLimitCheck['message']);
                }
                
                return redirect()->back()
                    ->withErrors(['error' => $userLimitCheck['message']])
                    ->withInput();
            }

            // ⭐ NOVO: Se for admin, usar tenant selecionado; senão, usar getOwnerId()
            if ($authUser->isAdmin()) {
                // Se admin forneceu owner_id no request, validar; senão, usar o tenant selecionado
                if ($request->filled('owner_id')) {
                    $selectedOwner = User::find($request->owner_id);
                    if ($selectedOwner && $selectedOwner->isOwner() && !$selectedOwner->isAdmin()) {
                        $targetOwnerId = $selectedOwner->id;
                    } else {
                        DB::rollBack();
                        return redirect()->back()
                            ->withErrors(['owner_id' => 'Owner selecionado é inválido.'])
                            ->withInput();
                    }
                } else {
                    // Usar o tenant selecionado como owner
                    $targetOwnerId = $viewingTenant->id;
                }
            } else {
                $targetOwnerId = $authUser->getOwnerId();
                
                // Validar permissão
                if (!$authUser->canManageTenant()) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['error' => 'Você não tem permissão para criar este registro.'])
                        ->withInput();
                }
            }
            
            $ownerId = $targetOwnerId;
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($plainPassword),
                'must_change_password' => true,
                'owner_id' => $ownerId, // Vincular ao owner do tenant
                'is_owner' => false,
                'is_subowner' => false,
            ]);

            // Criar o broker vinculado ao usuário criado
            $broker = Broker::create([
                'user_id' => $user->id, // Broker vinculado ao seu próprio user
                'license_number' => $request->license_number ?? null,
                'company_name' => $request->company_name ?? null,
                'phone' => $request->phone ?? null,
                'address' => $request->address ?? null,
                'notes' => $request->notes ?? null,
                'accounting_email' => $request->accounting_email ?? null,
                'accounting_phone_number' => $request->accounting_phone_number ?? null,
                'fee_percent' => $request->fee_percent ?? null,
                'payment_terms' => $request->payment_terms ?? null,
                'payment_method' => $request->payment_method ?? null,
            ]);

            // ⭐ CORRIGIDO: Brokers são sub-usuários do Dispatcher
            // A subscription está no Dispatcher principal, não no Broker
            // Não criar subscription para Broker

            // Atribuir role
            $role = DB::table('roles')->where('name', 'Broker')->first();
            if ($role) {
                $roles = new RolesUsers();
                $roles->user_id = $user->id;
                $roles->role_id = $role->id;
                $roles->save();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao criar broker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar broker: ' . $e->getMessage()])
                ->withInput();
        }

        // Enviar email com credenciais (com tratamento de erro para não quebrar o fluxo)
        try {
            Mail::to($user->email)->queue(new NewCarrierCredentialsMail($user, $plainPassword));
        } catch (\Exception $e) {
            Log::warning('Falha ao enviar email de credenciais', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            // Não interrompe o fluxo - o broker foi criado com sucesso
        }

        // Se veio do fluxo de registro via auth, dispara evento e faz login
        if ($request->register_type === "auth_register") {
            event(new Registered($user));
            Auth::login($user);
            return redirect(RouteServiceProvider::HOME);
        }

        return redirect()
            ->route('brokers.index')
            ->with('success', 'Broker criado com sucesso; credenciais enviadas por e-mail.');
    }

    public function show($id)
    {
        $broker = Broker::with('user')->findOrFail($id);
        return view('broker.show', compact('broker'));
    }

    public function edit($id)
    {
        $broker = Broker::with('user')->findOrFail($id);
        return view('broker.edit', compact('broker'));
    }

    public function update(Request $request, $id)
    {
        $broker = Broker::findOrFail($id);
        $user = $broker->user;

        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => 'nullable|string|min:6|confirmed',
            'license_number' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'accounting_email' => 'nullable|email',
            'accounting_phone_number' => 'nullable|string',
            'fee_percent' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Atualiza o usuário
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
            ]);

            // Atualiza o broker
            $broker->update([
                'license_number' => $validated['license_number'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'accounting_email' => $validated['accounting_email'] ?? null,
                'accounting_phone_number' => $validated['accounting_phone_number'] ?? null,
                'fee_percent' => $validated['fee_percent'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('brokers.index')->with('success', 'Broker updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao atualizar broker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'broker_id' => $id
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar broker: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $broker = Broker::findOrFail($id);
        $user = $broker->user;

        try {
            DB::beginTransaction();

            $broker->delete();
            if ($user) {
                $user->delete();
            }

            DB::commit();

            return redirect()->route('brokers.index')->with('success', 'Broker deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao remover broker', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'broker_id' => $id
            ]);

            return redirect()->route('brokers.index')
                             ->withErrors(['error' => 'Erro ao remover broker: ' . $e->getMessage()]);
        }
    }
}
