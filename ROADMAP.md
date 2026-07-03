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
- Lançamento de combustível, manutenção e outros gastos
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não) — cálculo correto desde a abertura da viagem
- Descontos (vale, multa, outros)
- Documentos fiscais (CT-e, MDF-e, NF-e)
- Impressão de comprovante de acerto em PDF
- Rastreabilidade: cada viagem, lançamento, desconto e documento registra quem criou e quem alterou por último

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

### Usuários e permissões
- Papéis: admin (gerencia usuários) e operador (acesso operacional)
- Tela de gestão de usuários (`/users`), restrita a admin
- Autocadastro público desativado — usuários só são criados por um admin
- Login bloqueado para usuário inativo
- Proteções: ninguém desativa/rebaixa a si mesmo; sempre precisa sobrar um admin ativo
- Autenticação em Dois Fatores (2FA) opcional via TOTP (Google Authenticator, Authy etc.), autogerenciável na tela de Perfil, com códigos de recuperação de uso único

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

### Infraestrutura de qualidade
- 177 testes automatizados (unitários + feature) cobrindo cálculo financeiro, ciclo de vida de viagens, CRUD de todos os módulos, permissões, 2FA, notificações, anonimização e upload/armazenamento de arquivos
- CI no GitHub Actions rodando a suíte a cada push/PR para `main`

---

## 🔜 Próximas implementações sugeridas

### Curto prazo
- **WhatsApp**: arquitetura de notificação já pronta para receber um novo canal; falta só a conta em um provedor (Twilio, Z-API, Meta Cloud API) para integrar de verdade

### Médio prazo
- **API REST** para app do motorista (ex: lançar combustível/comprovante direto do celular, sem acessar o painel admin)
- **Portal do motorista/cliente**: acesso restrito só aos próprios dados (viagens, acertos, documentos)
- **Assinatura digital do comprovante de acerto**: hoje é só impressão em PDF; um fluxo de assinatura eletrônica reduziria atrito operacional
- **Fluxo de caixa**: complementar ao DRE (que é por competência); acompanharia entradas/saídas reais de caixa por data de pagamento

### Longo prazo (apostas maiores, ligadas ao plano de vender para outras transportadoras)
- **Multi-tenant**: isolar dados por empresa cliente (`empresa_id` + escopo global em todas as tabelas). Pré-requisito real antes de vender a segunda licença — a estrutura de papéis (admin/operador) já construída é a base para isso
- **Billing/assinatura** se o modelo for SaaS auto-serviço, ou fluxo de onboarding manual se for venda direta
- **Integração com rastreamento veicular (GPS)** para KM automático em vez de digitação manual
- **Integração fiscal (SEFAZ)** para validar CT-e/MDF-e/NF-e automaticamente em vez de só registrar os números

---

## 🧊 Em espera

- **Deploy em produção**: pausado por decisão do time (2026-07-01). Quando retomar, checklist mínimo:
  - Configurar `.env` de produção com `MAIL_MAILER=resend` + `RESEND_API_KEY` (já testado em dev)
  - Configurar `.env` de produção com `UPLOADS_DISK=r2` + credenciais R2 (já testado em dev)
  - Confirmar cron do Laravel ativo (`* * * * * php artisan schedule:run`) para a anonimização mensal LGPD funcionar
  - Rodar `php artisan migrate --force` (22 migrations pendentes de aplicar no ambiente de produção)
  - Revisar `config/lgpd.php` / prazos de retenção com jurídico/contábil antes de confiar no expurgo automático
- **WhatsApp**: aguardando decisão de provedor
