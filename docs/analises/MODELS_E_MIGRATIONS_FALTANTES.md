# Models e Migrations Faltantes

## 📋 Resumo da Análise

Análise do dump SQL em comparação com os models e migrations existentes no projeto.

---

## ✅ Models que JÁ EXISTEM (não faltam)

| Tabela SQL | Model Existente | Status |
|------------|----------------|--------|
| `additional_services` | `AdditionalService.php` | ✅ OK |
| `attachments` | `Attachment.php` | ✅ OK |
| `billing_notifications` | `BillingNotification.php` | ✅ OK |
| `brokers` | `Broker.php` | ✅ OK |
| `carriers` | `Carrier.php` | ✅ OK |
| `charges_setups` | `ChargeSetup.php` | ✅ OK |
| `commissions` | `Comission.php` ⚠️ | ✅ OK (nome diferente) |
| `containers` | `Container.php` | ✅ OK |
| `containers_loads` | `ContainerLoad.php` | ✅ OK |
| `customers` | `Customer.php` | ✅ OK |
| `deals` | `Deal.php` | ✅ OK |
| `dispatchers` | `Dispatcher.php` | ✅ OK |
| `drivers` | `Driver.php` | ✅ OK |
| `employees` | `Employeer.php` | ✅ OK |
| `invoices` | `Invoice.php` | ✅ OK |
| `loads` | `Load.php` | ✅ OK |
| `payments` | `Payment.php` | ✅ OK |
| `permissions` | `Permission.php` | ✅ OK |
| `permissions_roles` | `permissions_roles.php` | ✅ OK |
| `plans` | `Plan.php` | ✅ OK |
| `roles` | `Role.php` | ✅ OK |
| `roles_users` | `RolesUsers.php` | ✅ OK |
| `subscriptions` | `Subscription.php` | ✅ OK |
| `time_line_charges` | `TimeLineCharge.php` | ✅ OK |
| `usage_tracking` | `UsageTracking.php` | ✅ OK |
| `user_card_configs` | `UserCardConfig.php` | ✅ OK |
| `users` | `User.php` | ✅ OK |

---

## ❌ Migrations que FALTAM (crítico)

### 1. **`deals`** ⚠️ CRÍTICO
- **Tabela:** `deals`
- **Model:** ✅ `Deal.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, dispatcher_id, carrier_id, value, created_at, updated_at
  ```
- **Foreign Keys:**
  - `fk_dispatcher_deals` → `dispatchers(id)`
  - `fk_carrier_deals` → `carriers(id)`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_deals_table.php`

---

### 2. **`commissions`** ⚠️ CRÍTICO
- **Tabela:** `commissions`
- **Model:** ✅ `Comission.php` existe (nota: nome está diferente - tabela é `commissions`, model é `Comission`)
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, dispatcher_id, deal_id, employee_id, value, created_at, updated_at
  ```
- **Foreign Keys:**
  - `fk_commissions_dispatcher` → `dispatchers(id)`
  - `fk_commissions_deal` → `deals(id)`
  - `fk_commissions_employee` → `employees(id)`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_commissions_table.php`

---

### 3. **`charges_setups`** ⚠️ CRÍTICO
- **Tabela:** `charges_setups`
- **Model:** ✅ `ChargeSetup.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, charges_setup_array (JSON), carrier_id, dispatcher_id, 
  created_at, updated_at, price (enum: 'price','paid amount')
  ```
- **Foreign Keys:**
  - `fk_charges_setups_carrier_id` → `carriers(id)`
  - `fk_charges_setups_dispatcher_id` → `dispatchers(id)`
- **Check Constraint:** `json_valid(charges_setup_array)`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_charges_setups_table.php`

---

### 4. **`containers`** ⚠️ IMPORTANTE
- **Tabela:** `containers`
- **Model:** ✅ `Container.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, name, user_id, created_at, updated_at
  ```
- **Foreign Keys:**
  - `containers_ibfk_1` → `users(id) ON DELETE CASCADE`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_containers_table.php`

---

### 5. **`containers_loads`** ⚠️ IMPORTANTE
- **Tabela:** `containers_loads`
- **Model:** ✅ `ContainerLoad.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, container_id, load_id, position, moved_at, created_at, updated_at
  ```
- **Foreign Keys:**
  - `fk_containers_loads_container_id` → `containers(id) ON DELETE CASCADE`
  - `fk_containers_loads_load_id` → `loads(id) ON DELETE CASCADE`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_containers_loads_table.php`

---

### 6. **`attachments`** ⚠️ IMPORTANTE
- **Tabela:** `attachments`
- **Model:** ✅ `Attachment.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela:**
  ```sql
  id, user_id, void_check_path, w9_path, coi_path, 
  proof_fmcsa_path, drivers_license_path, 
  truck_picture_1_path, truck_picture_2_path, truck_picture_3_path,
  created_at, updated_at
  ```
- **Foreign Keys:**
  - `attachments_ibfk_1` → `users(id) ON DELETE CASCADE`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_attachments_table.php`

---

### 7. **`time_line_charges`** ⚠️ IMPORTANTE
- **Tabela:** `time_line_charges`
- **Model:** ✅ `TimeLineCharge.php` existe
- **Migration:** ❌ **FALTA**
- **Campos na tabela (muitos campos!):**
  ```sql
  id, invoice_id, costumer, price, status_payment,
  carrier_id, dispatcher_id, created_at, updated_at,
  date_start, date_end, due_date, payment_terms,
  invoice_notes, amount_type (enum: 'price','paid_amount'),
  array_type_dates (JSON), load_ids (JSON), load_details (TEXT)
  ```
- **Foreign Keys:**
  - `fk_time_line_charges_carrier_id` → `carriers(id) ON DELETE CASCADE`
  - `fk_time_line_charges_dispatcher_id` → `dispatchers(id) ON DELETE CASCADE`
- **Indexes:**
  - `idx_due_date` on `due_date`
  - `idx_due_date_status` on `(due_date, status_payment)`
- **Check Constraints:**
  - `json_valid(array_type_dates)`
  - `json_valid(load_ids)`

**Arquivo necessário:** `database/migrations/YYYY_MM_DD_HHMMSS_create_time_line_charges_table.php`

---

## ⚠️ Observações Importantes

### 1. **Nome do Model `Comission` vs Tabela `commissions`**

O model está como `Comission.php` (com 1 's') mas a tabela é `commissions` (com 2 's').

**Solução:** O model já está correto usando `protected $table = "commissions"`, então está OK. Mas seria melhor renomear o model para `Commission.php` para seguir a convenção do Laravel.

---

### 2. **Model `Employeer` vs Tabela `employees`**

O model está como `Employeer.php` mas a tabela é `employees`.

**Verificar:** O model deve usar `protected $table = "employees"` ou renomear para `Employee.php`.

---

## 📝 Resumo por Prioridade

### 🔴 **CRÍTICO** (precisam ser criadas primeiro):
1. ✅ `create_deals_table.php`
2. ✅ `create_commissions_table.php`
3. ✅ `create_charges_setups_table.php`

### 🟡 **IMPORTANTE** (precisam ser criadas):
4. ✅ `create_containers_table.php`
5. ✅ `create_containers_loads_table.php`
6. ✅ `create_attachments_table.php`
7. ✅ `create_time_line_charges_table.php`

---

## 🎯 Próximos Passos

1. Criar as 7 migrations faltantes seguindo o schema do SQL
2. Considerar renomear `Comission.php` → `Commission.php`
3. Verificar se `Employeer.php` está usando a tabela correta
4. Executar `php artisan migrate` para aplicar as migrations
5. Verificar se há relacionamentos faltantes nos models

---

## 📌 Nota sobre Convenções

- **Models:** Laravel espera que o nome do model seja singular e PascalCase
- **Tabelas:** Laravel espera que o nome da tabela seja plural e snake_case
- **Migrations:** Laravel espera `create_{table_name}_table` ou `YYYY_MM_DD_HHMMSS_create_{table_name}_table`

Se o nome do model não seguir a convenção (como `Comission` ao invés de `Commission`), é necessário usar `protected $table = "nome_da_tabela"` no model.

