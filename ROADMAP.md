# Roadmap — Invexa Frete

Documento vivo com o que já está pronto e o que está planejado. Atualize conforme o backlog evoluir.

**Status atual**: em desenvolvimento local. Deploy em produção está pausado por decisão do time — retomar quando decidido (ver seção "Em espera").

---

## ✅ Funcionalidades implementadas

### Motoristas
- Cadastro completo (CPF, CNH, categoria, validade, telefone, e-mail)
- Percentual de comissão padrão por motorista
- Histórico de viagens por motorista
- Busca por nome, CPF ou telefone
- CPF e CNH mascarados na interface (`123.***.***-01`), com botão para revelar o valor completo

### Veículos
- Cadastro completo da frota (placa, modelo, marca, ano, tipo, RENAVAM, capacidade)
- Controle de status (ativo, inativo, em manutenção)
- Histórico de viagens por veículo
- Busca por placa, modelo ou marca

### Manutenção de veículos
- Registro de manutenção preventiva/corretiva, independente de viagem
- Data/KM da próxima manutenção prevista
- Registrar manutenção "em andamento" move o veículo para status "manutenção" automaticamente; concluir devolve para "ativo" (se não houver outra manutenção em aberto)
- Histórico e total gasto por veículo na tela de detalhe

### Clientes
- Cadastro de Pessoa Física e Jurídica
- Tabela de frete padrão por cliente
- Vinculação direta às viagens
- Busca por nome, CNPJ/CPF, cidade ou telefone
- CPF de cliente pessoa física mascarado (CNPJ não, pois não é dado pessoal)

### Viagens
- Abertura e acompanhamento completo de viagens
- Status: Aberta → Em Andamento → Aguardando Acerto → Encerrada
- Lançamento de combustível, manutenção e outros gastos, com aprovação: lançamentos feitos pelo próprio motorista (no Portal) ficam pendentes e só entram nos totais da viagem depois que um operador aprova
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não) — cálculo correto desde a abertura da viagem
- Descontos (vale, multa, outros)
- Documentos fiscais (CT-e, MDF-e, NF-e), com botão para verificar autenticidade direto no portal público oficial da SEFAZ (chave de acesso), sem custo e sem certificado digital — CT-e/NF-e é só chave + captcha; MDF-e exige login gov.br do próprio usuário, por não ter consulta pública simples equivalente
- Impressão de comprovante de acerto em PDF
- Rastreabilidade: cada viagem, lançamento, desconto e documento registra quem criou e quem alterou por último
- Avanço de status direto na tela da viagem (Aberta → Em Andamento → Aguardando Acerto), sem precisar abrir a edição; não permite pular etapas, evitando reabrir uma viagem já encerrada por engano
- **Assinatura digital do motorista**: captura a assinatura por canvas na tela da viagem (aguardando acerto ou encerrada), sem depender de app externo; a assinatura sai embutida no comprovante em PDF, com data/hora do momento em que foi coletada

### Financeiro / Acertos
- Acertos por Motorista com histórico individual
- Separação de saldo a pagar vs total já pago
- Exportação em PDF e CSV

### Relatórios
- Relatório Financeiro por período com filtros avançados (motorista, veículo, status)
- Resumo por motorista com paginação
- Composição de despesas em gráfico
- Exportação em PDF (landscape) e CSV
- **DRE simplificado** por período: Receita Bruta → Custos Diretos (comissão, combustível, manutenção de viagem) → Resultado Bruto → Despesas Operacionais (manutenção de frota avulsa + despesas administrativas) → Resultado Líquido. Considera apenas viagens encerradas. Exportação em PDF
- **Despesas Gerais**: cadastro de custos administrativos não ligados a uma viagem (aluguel, salários, contas, seguro, impostos, marketing, outros), com filtro por período/categoria — alimenta o DRE

### Dashboard
- Cards de resumo: viagens abertas, faturamento, lucro, frota
- Gráfico de Faturamento vs Lucro com filtros de 30, 60, 90 dias e período personalizado
- Gráfico de Viagens por Status
- Viagens em aberto e Top 5 motoristas do mês
- Painel de **Pendências**: CNH de motorista vencida/vencendo, veículos em manutenção, documentos fiscais pendentes, manutenção preventiva vencendo

### Multi-tenant (Empresas)
- Todas as tabelas de domínio (motoristas, veículos, clientes, viagens, lançamentos, descontos, documentos, manutenções, despesas gerais) são escopadas por `empresa_id` via escopo global no model — cada empresa cliente só enxerga e só cria dados dentro da própria empresa, automaticamente, sem precisar reescrever cada consulta existente
- Novo papel **super admin** (sem empresa própria): tela dedicada (`/empresas`) para cadastrar empresas clientes e o administrador inicial de cada uma; não acessa nenhuma tela operacional (que é sempre escopada por empresa)
- Empresa pode ser desativada (ex.: inadimplência) — bloqueia login de todos os usuários e motoristas dela de uma vez, sem precisar desativar um por um
- E-mail, CPF, placa e CNPJ continuam únicos globalmente (decisão deliberada): mantém o login por e-mail/CPF exatamente como já era, sem precisar informar a empresa antes de entrar
- Dados existentes antes dessa mudança foram preservados numa "Empresa Padrão" criada automaticamente pela migration de backfill — nada foi perdido
- Tela de detalhe da empresa (`/empresas/{id}`) para dar suporte: lista os usuários dela e um resumo operacional (motoristas, veículos, clientes, viagens, despesas gerais), sem precisar consultar o banco direto quando o cliente relata um problema
- **Modo suporte**: botão "Suporte" que loga o super admin como o administrador ativo daquela empresa (guardando a identidade original na sessão), com aviso fixo no topo de toda tela e botão para encerrar e voltar a ser super admin — pensado para reproduzir problemas relatados vendo exatamente a mesma tela do cliente

### Usuários e permissões
- Papéis: super admin (gerencia empresas clientes da plataforma), admin (gerencia usuários da própria empresa) e operador (acesso operacional)
- Tela de gestão de usuários (`/users`), restrita a admin e escopada à própria empresa
- Autocadastro público desativado — usuários só são criados por um admin
- Login bloqueado para usuário inativo
- Proteções: ninguém desativa/rebaixa a si mesmo; sempre precisa sobrar um admin ativo
- Autenticação em Dois Fatores (2FA) opcional via TOTP (Google Authenticator, Authy etc.), autogerenciável na tela de Perfil, com códigos de recuperação de uso único
- Telas de login, recuperação de senha e desafio de 2FA com identidade visual própria (logo, cores da marca)
- Botão para mostrar/esconder a senha digitada no login

### Notificações
- E-mail automático para admins ativos quando uma viagem entra em "aguardando acerto"
- E-mail automático para o motorista (se tiver e-mail cadastrado) quando a viagem é encerrada, com o resumo do acerto
- Envio real via Resend configurado e testado
- Sino de notificações no topbar para admins: mesma notificação de "aguardando acerto" também fica registrada por usuário (independente do e-mail), com contagem de não lidas e leitura individual — um admin marcar como lida não afeta os demais

### LGPD
- Mascaramento de CPF/CNH na interface (comprovantes em PDF continuam completos, pois são documentos de identificação assinados)
- Política de retenção configurável (`config/lgpd.php`, padrão 5 anos) sem precisar mexer em código
- Comando `lgpd:anonimizar` (com `--dry-run`) que apaga dados pessoais de motoristas, clientes pessoa física e usuários excluídos há mais tempo que o prazo, preservando o registro para não quebrar o histórico financeiro
- Agendado mensalmente (requer cron do Laravel ativo no servidor de produção)

### Armazenamento
- Comprovantes de lançamento e documentos fiscais anexados às viagens são enviados para **Cloudflare R2** (compatível com S3) em produção, via disco configurável (`UPLOADS_DISK`)
- Bucket privado: acesso aos arquivos só por URL assinada e temporária (10 min), gerada sob demanda — nada fica público por padrão
- Em dev continua no disco local (`public`) sem precisar de credenciais; troca de ambiente é só variável de ambiente, sem alteração de código

### Portal do Motorista
- Acesso próprio do motorista (guard de autenticação separado dos usuários do sistema), login com CPF + senha
- Acesso concedido pelo admin na tela de edição do motorista, que define a senha inicial — sem autocadastro
- Motorista vê só as próprias viagens e acertos, com detalhe somente leitura e download do comprovante em PDF (mesmo arquivo gerado para o admin, com a assinatura digital se já coletada)
- Motorista pode lançar combustível/manutenção direto da viagem, anexando foto do comprovante — fica pendente até um operador aprovar ou rejeitar; só entra nos totais depois de aprovado
- Isolamento total entre motoristas (um não acessa dado do outro) e entre o portal e o painel administrativo
- Troca de senha pelo próprio motorista

### Infraestrutura de qualidade
- 232 testes automatizados (unitários + feature) cobrindo cálculo financeiro, ciclo de vida de viagens, CRUD de todos os módulos, permissões, 2FA, notificações, anonimização, upload/armazenamento de arquivos, isolamento multi-tenant e o portal do motorista
- CI no GitHub Actions rodando a suíte a cada push/PR para `main`

---

## 🔜 Próximas implementações sugeridas

### Curto prazo
- **WhatsApp**: arquitetura de notificação já pronta para receber um novo canal; falta só a conta em um provedor (Twilio, Z-API, Meta Cloud API) para integrar de verdade

### Médio prazo
- **API REST** para um futuro app nativo do motorista (o Portal do Motorista via navegador já cobre o uso do dia a dia pelo celular; API só valeria a pena se um app nativo entrar em pauta)
- **Portal do cliente**: mesma ideia do portal do motorista, mas para o cliente acompanhar suas próprias viagens/documentos
- **Fluxo de caixa**: complementar ao DRE (que é por competência); acompanharia entradas/saídas reais de caixa por data de pagamento — avaliado e não priorizado por ora, já que o DRE atual cobre bem a necessidade atual

### Longo prazo (apostas maiores, ligadas ao plano de vender para outras transportadoras)
- **Billing/assinatura** se o modelo for SaaS auto-serviço, ou fluxo de onboarding manual se for venda direta (hoje o onboarding já é manual: o super admin cadastra a empresa e o admin inicial dela pela própria tela)
- **Integração com rastreamento veicular (GPS)** para KM automático em vez de digitação manual
- **Validação fiscal automática (via API paga)**: o link de verificação manual na SEFAZ já foi implementado (ver seção Viagens); uma automação completa (status buscado e exibido sem o usuário sair do sistema) exigiria assinar um provedor como Danfe Rápida (~R$497/mês) ou Nuvem Fiscal/Focus NFe — avaliado e não contratado ainda, pois o link manual já resolve a necessidade atual sem custo recorrente

---

## 🧊 Em espera

- **Deploy em produção**: pausado por decisão do time (2026-07-01). Quando retomar, checklist mínimo:
  - Configurar `.env` de produção com `MAIL_MAILER=resend` + `RESEND_API_KEY` (já testado em dev)
  - Configurar `.env` de produção com `UPLOADS_DISK=r2` + credenciais R2 (já testado em dev)
  - Confirmar cron do Laravel ativo (`* * * * * php artisan schedule:run`) para a anonimização mensal LGPD funcionar
  - Rodar `php artisan migrate --force` (39 migrations pendentes de aplicar no ambiente de produção, incluindo a criação do usuário super admin — ver abaixo)
  - Confirmar o e-mail do super admin da plataforma (`ac.castilho87@gmail.com`, hardcoded na migration de multi-tenant) antes de rodar em produção; o acesso é reivindicado via "esqueci minha senha" depois do primeiro `migrate`
  - Revisar `config/lgpd.php` / prazos de retenção com jurídico/contábil antes de confiar no expurgo automático
  - Confirmar que a extensão PHP `gd` está habilitada no servidor (necessária para o comprovante em PDF exibir a assinatura digital do motorista)
- **WhatsApp**: aguardando decisão de provedor
