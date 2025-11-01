feat: refatoração completa - filtragem por contexto e correções estruturais

## 🏗️ Refatoração de Modelos e Controllers

### Renomeação Employeer → Employee
- Renomeado model Employeer para Employee
- Renomeado EmployeerController para EmployeeController
- Atualizadas todas as referências em controllers, models e rotas
- Corrigidos relacionamentos nos models (User, Load, Comission, Dispatcher)

### Novos Controllers
- CommissionController: implementado filtro completo por dispatcher logado
- EmployeeController: novo controller após renomeação

### Migrations Criadas
- create_deals_table
- create_commissions_table
- create_charges_setups_table
- create_containers_table
- create_containers_loads_table
- create_attachments_table
- create_time_line_charges_table
- rename_dispatcher_company_id_to_dispatcher_id_in_carriers_table

## 🔒 Filtragem por Contexto de Usuário

Implementada filtragem completa em todos os controllers para garantir que usuários
vejam apenas dados do seu próprio dispatcher:

### Controllers Corrigidos
- CommissionController: filtro em todos os métodos (index, create, store, edit, update, destroy, reports)
- DealController: filtro por dispatcher e carriers do usuário logado
- ChargeSetupController: filtro completo (index, create, edit, store, update, destroy)
- TimeLineChargeController: filtro de dispatchers e carriers no create/show
- LoadImportController: correção para retornar coleções em create/edit
- CarrierController, DriverController, DashboardController: atualizações de referências

### Correções em Views
- commission/create.blade.php: correção para exibir nomes reais de employees
- commission/edit.blade.php: mesma correção
- invoice/time_line_charge/create.blade.php: padronização de tamanhos de campos HTML
- load/create.blade.php: correção de coleções de dispatchers/carriers

## 🗄️ Mudanças no Banco de Dados

### Migrations
- Renomeado campo dispatcher_company_id → dispatcher_id na tabela carriers
- Adicionados campos user_id, max_dispatchers, is_custom na tabela plans
- Criadas todas as migrations faltantes identificadas no SQL dump

### Models
- Carrier: atualizado para usar dispatcher_id
- Dispatcher: corrigido relacionamento carriers()
- Employee: adicionados accessors getUserNameAttribute() e getUserEmailAttribute()
- Plan: adicionados campos e scopes para planos customizados

## 📝 Atualizações em Rotas e Configurações

- routes/web.php: atualizado para usar EmployeeController
- Atualizados imports e referências em todos os controllers
- Corrigidos factories e seeders

## 📚 Documentação

- docs/deploy/GUIA_DEPLOY_PRODUCAO.md: guia completo de deploy
- docs/deploy/CREDENCIAIS_DEPLOY.md: template para anotar credenciais
- docs/analises/PROBLEMAS_FILTRAGEM_CONTEXTO_USUARIO.md: análise completa dos problemas
- docs/analises/MODELS_E_MIGRATIONS_FALTANTES.md: documentação das migrations faltantes

## 🔧 Correções Técnicas

- Dockerfile: melhorias em permissões de storage
- BillingService: atualizações para planos customizados
- Correção de referências dispatcher_company_id → dispatcher_id em todo o código

## 📊 Estatísticas

- 43 arquivos modificados
- 857 inserções, 793 deleções
- 7 novas migrations criadas
- 2 controllers renomeados/criados
- 15+ controllers atualizados com filtragem contextual

