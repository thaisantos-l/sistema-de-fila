# Sistema de Filas

Projeto fullstack simples com PHP orientado a objetos, arquitetura MVC, API REST, SQL e front-end com Bootstrap.

## Tecnologias

- PHP 8+
- MySQL (MAMP)
- PDO
- Bootstrap 5
- JavaScript (fetch API)

## Funcionalidades

- Cadastro de pessoa na fila
- Consulta de posição/status por senha
- Máscara de telefone no formulário público
- Exibição da senha gerada para o usuário
- Painel administrativo com login
- Listagem da fila
- Chamar próximo automaticamente
- Finalizar atendimento
- Cancelar atendimento
- Remover registros finalizados/cancelados
- Métricas simples da operação

## Arquitetura

```
sistema-de-filas/
├── app/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Web/
│   ├── Core/
│   ├── Models/
│   └── Services/
├── config/
├── database/
├── public/
│   ├── assets/
│   └── views/
├── routes/
└── README.md
```

## Banco de dados

O banco já foi criado por você com o nome `escola-o-nome-do-seu-banco`.

Caso precise recriar:

1. Acesse o phpMyAdmin (`http://seulocalhost/phpmyadmin`).
2. Crie o banco `sistema-filas` com collation `utf8mb4_unicode_ci`.
3. Rode o SQL do arquivo `database/schema.sql`.

## Configuração no Servidor

1. Garanta que Apache e MySQL estejam iniciados no MAMP.
2. O projeto usa este `.env`, em caso de dúvidas, verificar o .env.example:
   - `DB_HOST=` // seu db host
   - `DB_PORT=` // seu db port
   - `DB_NAME=` // seu db name
   - `DB_USER=` // seu db user
   - `DB_PASS=` // seu db pass
3. Acesse no navegador:
   - `http://seudominio/sistema-de-filas`

## Login administrativo

- URL: `http://seudominio/sistema-de-filas/admin/login`
- No primeiro acesso, se a tabela `admin_users` estiver vazia, o sistema cria automaticamente o primeiro admin com o usuário/senha digitados no formulário.

## Endpoints REST

### Públicos

- `POST /api/queue` - cadastra pessoa na fila
- `GET /api/queue/{id}` - consulta posição/status por senha

### Administrativos (requer sessão de admin)

- `GET /api/queue` - lista a fila
- `PATCH /api/queue/next/call` - chama o próximo da fila
- `PATCH /api/queue/{id}/finish` - finaliza atendimento
- `PATCH /api/queue/{id}/cancel` - cancela registro
- `PATCH /api/queue/{id}/status` - atualiza status manualmente
- `DELETE /api/queue/{id}` - remove registro
- `GET /api/metrics` - métricas do painel

## Regras de negócio

- Próximo chamado: registro mais antigo com status `aguardando`.
- Telefone é obrigatório no cadastro público (com DDD).
- Não permite abrir nova senha se já existir senha ativa (`aguardando` ou `em_atendimento`) para o mesmo telefone.
- Status válidos:
  - `aguardando`
  - `em_atendimento`
  - `finalizado`
  - `cancelado`
- Transições permitidas:
  - `aguardando -> em_atendimento | cancelado`
  - `em_atendimento -> finalizado | cancelado`
