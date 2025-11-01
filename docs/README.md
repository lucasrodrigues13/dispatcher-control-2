# Documentação do Projeto

Esta pasta contém toda a documentação do projeto, incluindo análises, propostas e especificações técnicas.

## 📚 Estrutura

```
docs/
├── README.md (este arquivo)
├── analises/
│   ├── ANALISE_REGRAS_NEGOCIO.md
│   ├── PROBLEMAS_FILTRAGEM_CONTEXTO_USUARIO.md
│   └── MODELS_E_MIGRATIONS_FALTANTES.md
└── propostas/
    └── PROPOSTA_SISTEMA_PLANOS_CUSTOMIZADOS.md
```

### 📋 Análises (`analises/`)

Documentos que analisam requisitos, regras de negócio e identificam inconsistências:

- **`ANALISE_REGRAS_NEGOCIO.md`** - Análise detalhada das regras de negócio fornecidas pelo stakeholder, incluindo identificação de contradições e pontos que precisam de esclarecimento.

- **`PROBLEMAS_FILTRAGEM_CONTEXTO_USUARIO.md`** - Análise completa de problemas de filtragem onde usuários veem dados de outros usuários ao invés de apenas seus próprios dados. Lista todos os controllers e métodos afetados.

- **`MODELS_E_MIGRATIONS_FALTANTES.md`** - Análise do dump SQL comparado com os models e migrations existentes. Identifica quais migrations faltam e quais models precisam de ajustes.

### 💡 Propostas (`propostas/`)

Documentos que apresentam soluções técnicas para apresentação ao stakeholder:

- **`PROPOSTA_SISTEMA_PLANOS_CUSTOMIZADOS.md`** - Proposta completa do sistema de planos customizados por usuário, incluindo arquitetura, fluxos, interface e casos de uso.

### 📝 Especificações (futuro)

Para documentos técnicos detalhados de implementação, use a pasta `especificacoes/` (a ser criada quando necessário).

## 🔄 Como Usar

1. **Análises:** Consulte para entender os requisitos e identificar inconsistências antes de implementar
2. **Propostas:** Use para apresentar soluções ao stakeholder e obter aprovação antes de começar o desenvolvimento
3. **Especificações:** (futuro) Use como referência técnica durante a implementação

## 📝 Adicionando Nova Documentação

Ao criar novos documentos markdown:

1. **Coloque na pasta correta:**
   - Análises → `analises/`
   - Propostas → `propostas/`
   - Especificações técnicas → `especificacoes/` (criar quando necessário)

2. **Siga as convenções:**
   - Use nomes descritivos e claros (UPPERCASE com underscores)
   - Adicione data ou versão se relevante (ex: `PROPOSTA_PLANOS_2025-01.md`)
   - Mantenha este README atualizado com novos documentos

3. **Estrutura sugerida para novos documentos:**
   - Título claro e objetivo
   - Seção de visão geral
   - Seção de detalhes/requisitos
   - Seção de conclusão/próximos passos

