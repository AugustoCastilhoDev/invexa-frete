# Roadmap — Invexa Frete

Documento vivo com o que já está pronto e o que está planejado. Atualize conforme o backlog evoluir.

**Status atual**: em produção em [invexafrete.com.br](https://invexafrete.com.br) desde 2026-07-07 (VPS Hostinger, deploy manual via SSH/Nginx — ver seção "Infraestrutura de qualidade").

---

## ✅ Funcionalidades implementadas

### Motoristas
- Cadastro completo (CPF, CNH, categoria, validade, telefone, e-mail)
- Percentual de comissão padrão por motorista
- Histórico de viagens por motorista
- Busca por nome, CPF ou telefone
- CPF e CNH mascarados na interface (`123.***.***-01`), com botão para revelar o valor completo
- **Importação em massa via CSV**: cadastra vários motoristas de uma vez a partir de uma planilha, com modelo para baixar; linhas com erro (CPF duplicado, data inválida etc.) não travam as demais — ficam listadas com o número da linha e o motivo, para corrigir e reenviar só o que faltou

### Veículos
- Cadastro completo da frota (placa, modelo, marca, ano, tipo, RENAVAM, chassi, validade do documento/CRLV, capacidade)
- Controle de status (ativo, inativo, em manutenção)
- Histórico de viagens por veículo
- Busca por placa, modelo ou marca
- Listagem destaca em vermelho, com ícone de atenção, veículos com validade do documento vencendo em até 30 dias (ou já vencida)
- **Importação em massa via CSV**: mesmo mecanismo dos motoristas; respeita o limite de veículos do plano linha a linha (se estourar no meio do arquivo, as linhas restantes ficam marcadas como erro em vez de criar acima do contratado). O vínculo cavalo/carreta não é importado — fica para ajuste manual depois

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
- **Importação em massa via CSV**: mesmo mecanismo dos motoristas/veículos, com os campos essenciais (tipo, nome, documento, contato, cidade/estado, tabela de frete); endereço detalhado fica para completar depois na edição

### Viagens
- Abertura e acompanhamento completo de viagens
- Status: Aberta → Em Andamento → Aguardando Acerto → Encerrada
- Lançamento de combustível, manutenção e outros gastos, com aprovação: lançamentos feitos pelo próprio motorista (no Portal) ficam pendentes e só entram nos totais da viagem depois que um operador aprova
- **KM do veículo e litros abastecidos** nos lançamentos de combustível (admin e Portal do Motorista), campos exibidos só quando o tipo é "Combustível"; alimentam a **média de combustível (km/L)** da viagem, exibida ao lado do KM Rodados na tela e no PDF
- Controle de KM inicial e final
- Adiantamento ao motorista (com opção de desconto ou não) — cálculo correto desde a abertura da viagem
- Descontos (vale, multa, adiantamento, outros) e **Bonificação** (diária, prêmio): mesma seção "Descontos e Bonificações", mas soma ao saldo do motorista em vez de subtrair — linha em verde com "+", distinta dos tipos de débito
- Documentos fiscais (CT-e, MDF-e, NF-e), com botão para verificar autenticidade direto no portal público oficial da SEFAZ (chave de acesso), sem custo e sem certificado digital — CT-e/NF-e é só chave + captcha; MDF-e exige login gov.br do próprio usuário, por não ter consulta pública simples equivalente
- Impressão de comprovante de acerto em PDF — Fix: o layout de duas colunas (Motorista/Veículo, Lançamentos/Descontos) usava `display:flex`, que o Dompdf não renderiza de forma confiável, ficando tudo empilhado em vez de lado a lado; trocado por layout em `<table>`, a mesma técnica já usada com sucesso no bloco Resumo Financeiro. O mesmo fix foi replicado no PDF de Acertos por Motorista
- Rastreabilidade: cada viagem, lançamento, desconto e documento registra quem criou e quem alterou por último
- Avanço de status direto na tela da viagem (Aberta → Em Andamento → Aguardando Acerto), sem precisar abrir a edição; não permite pular etapas, evitando reabrir uma viagem já encerrada por engano
- **Assinatura digital do motorista**: captura a assinatura por canvas na tela da viagem (aguardando acerto ou encerrada), sem depender de app externo; a assinatura sai embutida no comprovante em PDF, com data/hora do momento em que foi coletada
- **Controle de recebimento do frete** (contas a receber do cliente): na listagem de todas as viagens, botão de um clique para confirmar/desfazer o recebimento do frete, com data registrada automaticamente; filtro por recebido/pendente e exportação em CSV (com as mesmas colunas do relatório + status e data de recebimento)

### Programação de Frota
- Tela dedicada (`/programacoes`) para planejar o motorista/veículo/cliente da próxima viagem antes de encerrar a atual — pedido comum de transportadoras de cavalo/carreta para não deixar veículo parado entre viagens
- Modelo `ProgramacaoViagem` deliberadamente desacoplado da máquina de status de `Viagem` (não entra em recálculo financeiro, notificações nem contagens de frota) — evita risco de uma viagem "programada" sem dados reais poluir esses cálculos
- Atalho "Programar Próxima Viagem" na própria tela da viagem (`em_andamento`/`aguardando_acerto`), pré-preenchendo motorista/veículo
- Cards de controle de frota: programações pendentes e veículos ativos sem próxima viagem definida
- Validação de conflito: não permite duas programações pendentes para o mesmo motorista ou veículo
- Confirmação é sempre manual — encerrar a viagem atual não abre a próxima automaticamente; o botão "Confirmar" leva ao formulário normal de nova viagem, já pré-preenchido, e ao salvar marca a programação como confirmada e vinculada à viagem criada
- Valor do frete opcional na programação (útil quando já negociado), pré-preenchido no formulário de nova viagem ao confirmar

### Financeiro / Acertos
- Acertos por Motorista com histórico individual
- **Média de combustível (km/L) do período**: soma do KM rodado e dos litros abastecidos em todas as viagens do filtro (motorista + período), exibida como card na tela, no PDF e como colunas extras no CSV
- Bonificações do período somadas à parte, sem entrar no total de "Descontos" (que continua só com os tipos de débito)
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
- **Limite de veículos por plano**: campo configurável por empresa (nulo = sem limite) que bloqueia o cadastro de um novo veículo ao atingir o teto contratado — pensado para o modelo de cobrança por faixa de frota (ex.: até 5 veículos = plano X). A própria tela de Veículos do cliente mostra "X / Y" e avisa quando o limite é atingido, sem precisar entrar em contato para descobrir
- **Conjunto cavalo + carreta**: uma carreta pode ser vinculada a um cavalo mecânico (campo simples na tela de cadastro/edição); enquanto vinculada, conta como parte do mesmo conjunto no limite do plano (1 conjunto = 1 veículo cobrado); uma carreta avulsa, sem cavalo vinculado, volta a contar separadamente
- Fix: login real do motorista (via sessão/cookie, diferente do `actingAs` usado nos testes) causava erro 500 por recursão infinita — resolver a empresa do guard `motorista` consultava o próprio model `Motorista`, escopado pela mesma empresa que ainda não tinha sido resolvida. Corrigido com uma trava de reentrância no `TenantContext`
- Fix: o card "Frota / Motoristas" do Dashboard contava um conjunto cavalo + carreta vinculada como 2 veículos, divergindo do "X / Y" da tela de Veículos (que já aplicava a regra de contar como 1). Corrigido aplicando o mesmo scope `contamParaLimite()` no Dashboard

### Cobrança recorrente (Asaas)
- Ao cadastrar uma empresa nova, o super admin escolhe o plano (Starter/Pro/Business/Enterprise) e o ciclo (mensal/anual); o limite de veículos é preenchido automaticamente pelo plano, mas continua editável para casos negociados à parte
- Cria automaticamente o cliente e a assinatura recorrente no Asaas (sandbox ou produção, via `ASAAS_ENV`), com 14 dias de trial antes da primeira cobrança — plano Enterprise fica de fora (sempre negociado manualmente, sem assinatura automática)
- Resiliente à ausência de credencial: sem `ASAAS_API_KEY` configurada (ou se a chamada à API falhar), o cadastro da empresa continua funcionando normalmente — só fica sem o vínculo de cobrança, visível na tela de detalhe da empresa com um aviso
- Webhook (`POST /webhooks/asaas`, protegido por token) recebe os eventos de pagamento e só registra o status/data do último evento na empresa — a suspensão por inadimplência continua manual, decisão do super admin pela tela (não desativa sozinho)
- **Criar assinatura retroativamente**: empresa sem vínculo de cobrança (criada antes dessa feature, Enterprise que virou plano padrão, ou falha na chamada original) mostra um formulário na própria tela de detalhe para escolher/reescolher plano e ciclo e tentar de novo, sem precisar recriar a empresa
- **Indicador de situação de cobrança**: badge colorido na listagem de empresas (Em dia / Atrasado / Em trial / Sem assinatura / Reembolsado), traduzindo o status bruto do evento do Asaas para algo legível; clicável, leva direto à seção de Cobrança da empresa, onde um alerta reforça que o atraso não bloqueia o acesso sozinho

### Usuários e permissões
- Papéis: super admin (gerencia empresas clientes da plataforma), admin (gerencia usuários da própria empresa e vê tudo dela) e operador (acesso operacional do dia a dia)
- **Escopo do operador**: acessa Viagens, Motoristas, Veículos, Clientes, Lançamentos, Descontos, Documentos, Manutenções e Acertos; fica de fora do DRE, Relatórios Financeiros e Despesas Gerais (dados estratégicos/administrativos) e não exclui nenhum registro (motorista, veículo, viagem, lançamento, desconto, documento, manutenção) nem a própria conta na tela "Meu Perfil" — só o admin apaga. Antes dessa mudança o operador tinha acesso igual ao admin em tudo, exceto gestão de usuários
- Tela de gestão de usuários (`/users`), restrita a admin e escopada à própria empresa
- Autocadastro público desativado — usuários só são criados por um admin
- Login bloqueado para usuário inativo
- Rate limiting: 5 tentativas de login incorretas bloqueiam novas tentativas temporariamente (chave por e-mail/CPF + IP), aplicado tanto no login do painel quanto no do Portal do Motorista
- Política mínima de senha (8 caracteres) e confirmação obrigatória ao criar usuário ou empresa (`Password::defaults()`)
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
- **Log de acesso à aplicação** (`logs_acesso`), exigido pelo Marco Civil da Internet (Art. 15): todo login (painel e Portal do Motorista) grava automaticamente IP, data/hora e o guard usado, via listener no evento `Illuminate\Auth\Events\Login` — cobre login normal, 2FA e o modo suporte do super admin
- Comando `lgpd:expurgar-logs-acesso` (com `--dry-run`) apaga logs de acesso com mais de 12 meses (`config('lgpd.retencao_meses.logs_acesso')`)
- Ambos agendados mensalmente (requer cron do Laravel ativo no servidor de produção)

### Armazenamento
- Comprovantes de lançamento e documentos fiscais anexados às viagens são enviados para **Cloudflare R2** (compatível com S3) em produção, via disco configurável (`UPLOADS_DISK`)
- Bucket privado: acesso aos arquivos só por URL assinada e temporária (10 min), gerada sob demanda — nada fica público por padrão
- Em dev continua no disco local (`public`) sem precisar de credenciais; troca de ambiente é só variável de ambiente, sem alteração de código

### Portal do Motorista
- Acesso próprio do motorista (guard de autenticação separado dos usuários do sistema), login com CPF + senha
- Acesso concedido pelo admin na tela de edição do motorista, que define a senha inicial — sem autocadastro
- Motorista vê só as próprias viagens e acertos, com detalhe somente leitura e download do comprovante em PDF (mesmo arquivo gerado para o admin, com a assinatura digital se já coletada)
- Motorista pode lançar combustível/manutenção direto da viagem, anexando foto do comprovante — fica pendente até um operador aprovar ou rejeitar; só entra nos totais depois de aprovado
- Lançamento de combustível no Portal também tem os campos de KM do veículo e litros abastecidos (mesmo mecanismo do admin), essencial pra média de combustível ter dados reais já que é o canal mais usado pra registrar abastecimento
- Fix: comprovante limitado a 2MB rejeitava fotos comuns de câmera de celular sem mostrar erro nenhum na tela — nenhum dos dois layouts (admin/portal) renderizava `$errors` do Laravel, então qualquer falha de validação nos formulários rápidos de lançamento/desconto falhava silenciosamente. Corrigido: limite subiu para 5MB e os dois layouts agora exibem um alerta com a mensagem de erro
- Isolamento total entre motoristas (um não acessa dado do outro) e entre o portal e o painel administrativo
- Troca de senha pelo próprio motorista

### Landing Page
- Página institucional pública na raiz (`/`), servida a visitantes; usuário/motorista autenticado que acessa `/` é redirecionado direto para o dashboard/portal, sem ver a landing
- Seções: hero com CTA, recursos do sistema, demonstração interativa, planos e preços (Starter/Pro/Business/Enterprise, com destaque no plano mais escolhido), e contato
- **Demonstração interativa** ("Veja como é por dentro"): abas clicáveis (Dashboard, Viagens, Acerto, Portal do Motorista) com mockups em HTML/CSS puro — sidebar, KPIs, tabelas e o mockup do Portal em formato de celular —, reaproveitando as cores/badges reais do sistema; usada no lugar de screenshots porque ainda não há dados reais de cliente suficientes para uma demo autêntica
- Acesso direto ao Portal do Motorista no cabeçalho, ao lado de "Entrar" e "Falar com Vendas" (rótulo curto "Portal" no mobile, pra não espremer o botão de CTA)
- Tabela de planos exibida com valores reais (mensal e anual), teto de veículos por plano e taxa de implantação — CTA "Falar com Vendas" leva ao WhatsApp com a mensagem já preenchida com o nome do plano
- Sem cadastro self-service ainda: toda venda passa por contato manual (WhatsApp/e-mail); a empresa continua sendo criada pelo super admin na tela `/empresas` depois do primeiro contato

### Responsividade mobile do painel
- Sidebar em off-canvas abaixo de 992px: escondida por padrão, abre com o botão de menu no topbar, fecha pelo X dentro dela ou tocando fora (overlay escurecido) — antes disso a sidebar de 250px fixos ocupava boa parte da tela em qualquer celular, deixando o conteúdo espremido e quebrado
- Todas as tabelas do painel (viagens, veículos, motoristas, financeiro, DRE, etc.) envolvidas em `table-responsive`: em telas estreitas, a tabela rola horizontalmente dentro do próprio card, sem estourar a largura da página
- Verificado visualmente em viewport de celular (390px) antes e depois da correção

### Modo escuro e personalização do painel
- Alternância entre tema claro/escuro pelo card de usuário no rodapé do sidebar (Bootstrap 5.3 color modes via `data-bs-theme`), preferência salva em `localStorage` e aplicada antes do primeiro paint (sem "flash" de tema errado ao recarregar)
- Relógio/data ao vivo no sidebar, abaixo da logo, atualizado a cada segundo
- Componentes que usavam cores fixas (`bg-white`, `table-light`, `btn-outline-dark`, gradientes inline) e não reagiam ao tema escuro foram corrigidos com overrides globais, conforme encontrados — cabeçalhos de card, cabeçalhos de tabela, botões de exportar PDF e os cards de saldo em Acertos
- Favicon próprio (caminhão sobre o gradiente laranja da marca) em todas as telas — o `favicon.ico` do scaffold original estava vazio (0 bytes)

### Infraestrutura de qualidade
- 361 testes automatizados (unitários + feature) cobrindo cálculo financeiro, ciclo de vida de viagens, CRUD de todos os módulos, permissões, 2FA, notificações, anonimização, log de acesso, upload/armazenamento de arquivos, isolamento multi-tenant, programação de frota, controle de recebimento do frete e o portal do motorista
- CI no GitHub Actions rodando a suíte a cada push/PR para `main`

### Deploy em produção
- Domínio próprio [invexafrete.com.br](https://invexafrete.com.br) (registro.br) apontando via registro A para a mesma VPS Hostinger (Ubuntu 22.04) que já hospeda outro projeto — sites isolados por virtual host próprio no Nginx (cada um com seu bloco `server`, sem interferir um no outro)
- SSL via Certbot/Let's Encrypt, com renovação automática configurada
- Banco de dados MySQL dedicado (`invexafrete`, usuário próprio) — não compartilha dados com o outro projeto na mesma VPS
- `.env` de produção configurado: Resend (e-mail transacional), Cloudflare R2 (armazenamento de arquivos), Asaas em modo produção (chave real + webhook cadastrado e validado)
- Cron do Laravel ativo (`* * * * * php artisan schedule:run`) para as tarefas mensais de LGPD (anonimização + expurgo de logs de acesso)
- Super admin da plataforma (`ac.castilho87@gmail.com`) com acesso reivindicado via "esqueci minha senha" após o primeiro `migrate --force`

---

## 🔜 Próximas implementações sugeridas

### Curto prazo
- **WhatsApp**: arquitetura de notificação já pronta para receber um novo canal; falta só a conta em um provedor (Twilio, Z-API, Meta Cloud API) para integrar de verdade
- **Revisar prazos de retenção da LGPD** (`config/lgpd.php`) com jurídico/contábil, agora que o expurgo automático mensal já roda de verdade em produção (cron ativo)

### Médio prazo
- **API REST** para um futuro app nativo do motorista (o Portal do Motorista via navegador já cobre o uso do dia a dia pelo celular; API só valeria a pena se um app nativo entrar em pauta)
- **Portal do cliente**: mesma ideia do portal do motorista, mas para o cliente acompanhar suas próprias viagens/documentos
- **Fluxo de caixa**: complementar ao DRE (que é por competência); acompanharia entradas/saídas reais de caixa por data de pagamento — avaliado e não priorizado por ora, já que o DRE atual cobre bem a necessidade atual

### Longo prazo (apostas maiores, ligadas ao plano de vender para outras transportadoras)
- **Suspensão automática por inadimplência**: hoje o webhook do Asaas só registra o status (ver seção Cobrança recorrente); automatizar a desativação da empresa fica para quando o volume de clientes justificar o risco de um falso positivo suspender alguém por engano
- **Upgrade/downgrade de plano self-service**: hoje trocar de plano depois de criada a empresa é manual (ajustar `limite_veiculos` e a assinatura no Asaas direto); um fluxo pela própria tela do admin é um passo natural depois que houver clientes reais pedindo isso
- **Integração com rastreamento veicular (GPS)** para KM automático em vez de digitação manual
- **Validação fiscal automática (via API paga)**: o link de verificação manual na SEFAZ já foi implementado (ver seção Viagens); uma automação completa (status buscado e exibido sem o usuário sair do sistema) exigiria assinar um provedor como Danfe Rápida, Nuvem Fiscal ou Focus NFe — avaliado e não contratado ainda, pois o link manual já resolve a necessidade atual sem custo recorrente

---

## 🧊 Em espera

- **WhatsApp**: aguardando decisão de provedor
