# Problemas de Filtragem por Contexto de Usuário

## 🔍 Resumo

Vários formulários de cadastro estão exibindo TODOS os registros do banco de dados ao invés de filtrar apenas os registros que pertencem ao contexto do usuário logado.

**Problema Principal:** Um dispatcher deveria ver apenas os carriers que ele criou, mas está vendo todos os carriers do sistema.

---

## 📋 Problemas Identificados

### 1. **DriverController - Método `edit()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/DriverController.php`  
**Linha:** 149

**Problema:**
```php
$carriers = Carrier::with('user')->get();
```
Mostra TODOS os carriers do sistema, não apenas os do dispatcher logado.

**Correto deveria ser:**
```php
// Buscar carriers apenas do dispatcher logado (igual ao método create())
$dispatcher = Dispatcher::where('user_id', Auth::id())->first();
if ($dispatcher) {
    $carriers = Carrier::with('user')
        ->where('dispatcher_company_id', $dispatcher->id)
        ->get();
} else {
    $carriers = [];
}
```

**Contexto:** Ao editar um driver, o dispatcher pode selecionar qualquer carrier, inclusive de outros dispatchers.

---

### 2. **CarrierController - Método `edit()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/CarrierController.php`  
**Linha:** 172

**Problema:**
```php
$dispatchers = Dispatcher::with('user')->get();
```
Mostra TODOS os dispatchers do sistema.

**Correto deveria ser:**
```php
// Mostrar apenas o dispatcher do usuário logado (ou relacionado ao carrier)
$dispatchers = Dispatcher::with('user')
    ->where('user_id', auth()->id())
    ->get();
```

**Contexto:** Ao editar um carrier, pode selecionar qualquer dispatcher, permitindo "roubar" carriers de outros dispatchers.

---

### 3. **EmployeerController - Método `edit()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/EmployeerController.php`  
**Linha:** 126

**Problema:**
```php
$dispatchers = Dispatcher::with('user')->get();
```
Mostra TODOS os dispatchers do sistema.

**Correto deveria ser:**
```php
// Mostrar apenas o dispatcher do usuário logado
$dispatchers = Dispatcher::with('user')
    ->where('user_id', auth()->id())
    ->get();
```

**Contexto:** Ao editar um employee, pode atribuir a qualquer dispatcher.

---

### 4. **DealController - Métodos `create()` e `edit()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/DealController.php`  
**Linhas:** 27-28, 76-77

**Problema:**
```php
$dispatchers = Dispatcher::with("user")->get();
$carriers = Carrier::with("user")->get();
```
Mostra TODOS os dispatchers e carriers do sistema.

**Correto deveria ser:**
```php
// Dispatchers: apenas do usuário logado
$dispatcher = Dispatcher::where('user_id', auth()->id())->first();
$dispatchers = $dispatcher ? collect([$dispatcher]) : collect();

// Carriers: apenas do dispatcher logado
if ($dispatcher) {
    $carriers = Carrier::with('user')
        ->where('dispatcher_company_id', $dispatcher->id)
        ->get();
} else {
    $carriers = collect();
}
```

**Contexto:** Ao criar/editar um Deal, pode vincular qualquer dispatcher ou carrier.

---

### 5. **ChargeSetupController - Métodos `create()` e `edit()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/ChargeSetupController.php`  
**Linhas:** 29-30, 80-81

**Problema:**
```php
$carriers = Carrier::all();
$dispatchers = Dispatcher::all();
```
Mostra TODOS os carriers e dispatchers do sistema.

**Correto deveria ser:**
```php
// Dispatchers: apenas do usuário logado
$dispatchers = Dispatcher::with('user')
    ->where('user_id', auth()->id())
    ->get();

// Carriers: apenas do dispatcher logado
$dispatcher = Dispatcher::where('user_id', auth()->id())->first();
if ($dispatcher) {
    $carriers = Carrier::where('dispatcher_company_id', $dispatcher->id)->get();
} else {
    $carriers = collect();
}
```

**Contexto:** Ao criar/editar Charge Setup, pode selecionar qualquer carrier ou dispatcher.

---

### 6. **TimeLineChargeController - Método `create()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/TimeLineChargeController.php`  
**Linha:** 64

**Problema:**
```php
$carriers = Carrier::with('user')->get();
$dispatchers = Dispatcher::with('user')->get();
```
Mostra TODOS os carriers e dispatchers do sistema.

**Nota:** Este controller também aparece em `edit()` (linha 343) e outro método (linha 431).

**Correto deveria ser:**
Mesma lógica dos casos anteriores - filtrar por dispatcher do usuário logado.

**Contexto:** Ao criar/editar invoice/timeline charge, pode selecionar qualquer carrier.

---

### 7. **ViewController - Método `index()`** ⚠️ ATENÇÃO

**Arquivo:** `app/Http/Controllers/ViewController.php`  
**Linhas:** 23-29

**Problema:**
```php
$customers = Customer::select('id', 'company_name')
    ->orderBy('company_name', 'asc')
    ->get();

$carriers = Carrier::select('id', 'company_name')
    ->orderBy('company_name', 'asc')
    ->get();
```
Mostra TODOS os customers e carriers.

**Nota:** Este parece ser um controller de relatórios. Preciso entender se é intencional mostrar todos ou se deveria filtrar também.

**Pergunta:** ViewController é usado para relatórios? Deveria mostrar todos ou filtrar por contexto?

---

### 8. **AdditionalServiceController - Método `store()`** ⚠️ CRÍTICO

**Arquivo:** `app/Http/Controllers/AdditionalServiceController.php`  
**Linha:** 75

**Problema:**
```php
$carriers = \App\Models\Carrier::all();
```
Quando `carrier_id === 'all'`, busca TODOS os carriers do sistema.

**Correto deveria ser:**
```php
// Se for 'all', buscar apenas os carriers do dispatcher logado
$dispatcher = Dispatcher::where('user_id', auth()->id())->first();
if ($dispatcher) {
    $carriers = Carrier::where('dispatcher_company_id', $dispatcher->id)->get();
} else {
    $carriers = collect();
}
```

**Contexto:** Ao criar serviço adicional para "todos", cria para TODOS os carriers do sistema, não apenas os do dispatcher.

---

### 9. **KanbanController** ⚠️ VERIFICAR

**Arquivo:** `app/Http/Controllers/KanbanController.php`  
**Linhas:** 173, 190

**Problema:**
```php
$carriers = Carrier::with("user")->get();
```
Mostra TODOS os carriers.

**Nota:** Preciso verificar o contexto completo deste controller para entender se deveria filtrar.

---

### 10. **LoadImportController - Método `create()`** ✅ CORRETO

**Arquivo:** `app/Http/Controllers/LoadImportController.php`  
**Linhas:** 100-106

**Status:** ✅ JÁ ESTÁ CORRETO!

```php
// Busca o dispatcher do usuário logado
$dispatchers = Dispatcher::where('user_id', Auth::id())->first();

// Se não existir dispatcher, retorna vazio
if (!$dispatchers) {
    $carriers = collect();
} else {
    // Filtra os carriers pelo dispatcher_company_id
    $carriers = Carrier::with(['dispatchers.user', 'user'])
        ->where('dispatcher_company_id', $dispatchers->id)
        ->paginate(10);
}
```

Este método está correto! Use como referência.

---

### 11. **BrokerController - Método `index()`** ⚠️ VERIFICAR

**Arquivo:** `app/Http/Controllers/BrokerController.php`  
**Linha:** 23

**Problema:**
```php
$brokers = Broker::with('user')->paginate(10);
```
Mostra TODOS os brokers do sistema.

**Nota:** Preciso entender: brokers pertencem a dispatchers? Ou são independentes? Se pertencerem, deveria filtrar por dispatcher.

**Pergunta:** Brokers têm relação com dispatcher? Ou são entidades independentes?

---

### 12. **ComissionController** ⚠️ VERIFICAR

**Arquivo:** `app/Http/Controllers/ComissionController.php`  
**Linhas:** 42, 44, 75

**Problema:**
```php
$dispatchers = Dispatcher::with('user')->get();
$employees = Employeer::with('user')->get();
```

Mostra TODOS os dispatchers e employees.

**Nota:** Preciso verificar o contexto completo para entender a regra de negócio.

---

## 📊 Resumo por Severidade

### ⚠️ CRÍTICO (Precisa correção urgente)
1. DriverController::edit() - Carriers
2. CarrierController::edit() - Dispatchers
3. EmployeerController::edit() - Dispatchers
4. DealController::create() e edit() - Dispatchers e Carriers
5. ChargeSetupController::create() e edit() - Carriers e Dispatchers
6. TimeLineChargeController::create() - Carriers e Dispatchers
7. AdditionalServiceController::store() - Carriers (quando 'all')

### ⚠️ VERIFICAR (Precisa entender regra de negócio)
8. ViewController::index() - Customers e Carriers (pode ser intencional para relatórios)
9. KanbanController - Carriers (verificar contexto)
10. BrokerController::index() - Brokers (verificar se brokers têm relação com dispatcher)
11. ComissionController - Dispatchers e Employees (verificar regra)

### ✅ CORRETO (Usar como referência)
12. LoadImportController::create() - Já filtra corretamente
13. DriverController::create() - Já filtra corretamente

---

## 🔧 Padrão de Correção

### Para Carriers (quando usuário é Dispatcher):
```php
// Sempre buscar o dispatcher do usuário logado primeiro
$dispatcher = Dispatcher::where('user_id', Auth::id())->first();

if ($dispatcher) {
    $carriers = Carrier::with('user')
        ->where('dispatcher_company_id', $dispatcher->id)
        ->get();
} else {
    $carriers = collect(); // ou []
}
```

### Para Dispatchers (quando usuário é Dispatcher):
```php
// Mostrar apenas o dispatcher do usuário logado
$dispatchers = Dispatcher::with('user')
    ->where('user_id', auth()->id())
    ->get();
```

### Para Employees (quando usuário é Dispatcher):
```php
// Mostrar apenas employees do dispatcher logado
$dispatcher = Dispatcher::where('user_id', Auth::id())->first();

if ($dispatcher) {
    $employees = Employeer::with('user')
        ->where('dispatcher_id', $dispatcher->id)
        ->get();
} else {
    $employees = collect();
}
```

---

## ❓ Perguntas para Esclarecer

1. **ViewController:** É intencional mostrar todos os customers/carriers em relatórios? Ou deveria filtrar também?

2. **KanbanController:** Qual a regra de negócio? Deveria mostrar todos ou filtrar?

3. **ComissionController:** Como funciona a comissão? Um dispatcher pode ver comissões de outros dispatchers?

4. **DealController:** Um dispatcher pode criar deals para carriers de outros dispatchers? Parece ser um problema de segurança.

5. **CarrierController::edit():** Um dispatcher pode editar um carrier e atribuir a outro dispatcher? Isso é permitido?

6. **BrokerController:** Brokers pertencem a dispatchers? Ou são independentes? Se pertencerem, deveria filtrar por dispatcher no index().

---

## 📝 Próximos Passos

1. ✅ Documentar todos os problemas encontrados (FEITO)
2. ⏳ Aguardar esclarecimento sobre casos duvidosos
3. ⏳ Implementar correções seguindo o padrão acima
4. ⏳ Validar que não há outros pontos de acesso não autorizado

---

## 🔒 Observação de Segurança

Alguns desses problemas podem ser **falhas de segurança** graves:
- Um dispatcher pode "roubar" carriers de outros dispatchers
- Um dispatcher pode ver/editar dados de outros dispatchers
- Um dispatcher pode criar deals/invoices para carriers de outros

**Recomendação:** Corrigir esses problemas o quanto antes, especialmente os marcados como CRÍTICO.

