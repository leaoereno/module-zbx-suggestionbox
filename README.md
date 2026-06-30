# 💡 Suggestion Box — Zabbix 7.0 Module

Módulo de Caixa de Sugestões para Zabbix 7.0 LTS. Permite que usuários logados criem e votem em ideias/sugestões de melhorias, facilitando a priorização do que será desenvolvido.

**Autor:** Rafael Leão
**Versão:** 1.0.0  
**Repositório:** https://github.com/leaoereno/module-zbx-suggestion-box

---

## Funcionalidades

- ✅ Qualquer usuário logado pode criar sugestões
- ✅ Qualquer usuário pode votar (toggle: vota e desvota)
- ✅ Suporte a TAGs por sugestão
- ✅ Busca em tempo real por título/descrição
- ✅ Filtro por tag (pill clicável)
- ✅ Ordenação: mais votados / mais recentes
- ✅ Autor ou Super Admin pode excluir sugestões
- ✅ Relatório de mais votados (Super Admin)
- ✅ Export CSV do relatório
- ✅ UX/UI responsivo e moderno

---

## Estrutura

```
module-zbx-suggestion-box/
├── manifest.json
├── Module.php
├── install.sql
├── deploy.sh
├── actions/
│   ├── SuggestionBoxList.php     ← Página principal
│   ├── SuggestionBoxSave.php     ← Salvar sugestão (JSON)
│   ├── SuggestionBoxVote.php     ← Votar/desvotar (JSON)
│   ├── SuggestionBoxDelete.php   ← Excluir sugestão (JSON)
│   └── SuggestionBoxReport.php   ← Relatório Super Admin
└── views/
    ├── suggestion.box.list.php
    ├── suggestion.box.save.php
    ├── suggestion.box.vote.php
    ├── suggestion.box.delete.php
    └── suggestion.box.report.php
```

---

## Instalação

### 1. Tabelas e permissões (SQL)

```bash
mysql -u zabbix -p zabbix < install.sql
```

Cria as tabelas `zbx_suggestions`, `zbx_suggestion_tags`, `zbx_suggestion_votes` e insere os `role_rule` necessários.

### 2. Copiar módulo

```bash
cp -r module-zbx-suggestion-box/ /usr/share/zabbix/modules/
chown -R apache:apache /usr/share/zabbix/modules/module-zbx-suggestion-box/
```

Ou use o deploy script:

```bash
chmod +x deploy.sh
./deploy.sh lab     # lab-zbx (192.168.0.151)
./deploy.sh prod    # lnxdczbxfront01/02
./deploy.sh local   # executa localmente no servidor Zabbix
```

### 3. Ativar no Zabbix

**Administration → General → Modules** → Scan directory → Ativar **Suggestion Box**

---

## Menu

| Local no menu | Action | Acesso |
|---|---|---|
| **Serviços → Suggestion Box** | `suggestion.box.list` | Todos os usuários |
| **Administration → Suggestions Report** | `suggestion.box.report` | Super Admin apenas |

---

## Tabelas criadas

| Tabela | Descrição |
|--------|-----------|
| `zbx_suggestions` | Sugestões (`suggestionid`, `userid`, `title`, `description`, `created_at`) |
| `zbx_suggestion_tags` | Tags por sugestão (`tagid`, `suggestionid`, `tag`) |
| `zbx_suggestion_votes` | Votos (`voteid`, `suggestionid`, `userid`, `voted_at`) — UNIQUE por par user/suggestion |

IDs gerados com `MAX(id)+1` (padrão para tabelas customizadas).

---

## Notas técnicas

- Todo JS é inline nas views (F5 BIG-IP bloqueia `.js` estáticos)
- `\DBstart()` / `\DBend()` para transações (nunca `DBbegin/DBcommit`)
- Super Admin detectado via `users.type = 3` direto no DB (não via `CRoleHelper`)
- MariaDB 10.11: `GROUP BY` inclui todas as colunas não-agregadas
- `UNIQUE KEY` em `zbx_suggestion_votes(suggestionid, userid)` previne double-vote no banco
