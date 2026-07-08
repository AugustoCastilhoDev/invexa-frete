@extends('layouts.legal')
@section('title', 'Termos de Uso')

@section('content')
<h2>1. Aceitação dos Termos</h2>
<p>Estes Termos de Uso regulam o acesso e a utilização do Invexa Frete ("Sistema", "Plataforma"), sistema de gestão
de viagens, frota e financeiro para transportadoras, operado por Castilho Soluções Digitais ("nós", "nossa empresa").
Ao criar uma conta ou utilizar a Plataforma, a transportadora contratante ("Cliente") e seus usuários autorizados
declaram ter lido, compreendido e aceito integralmente estes Termos.</p>

<h2>2. Descrição do Serviço</h2>
<p>O Invexa Frete é oferecido no modelo SaaS (Software as a Service) e permite, entre outras funcionalidades:
cadastro de motoristas, veículos e clientes; abertura, acompanhamento e encerramento de viagens; lançamentos de
combustível e manutenção; controle de descontos, bonificações e adiantamentos; acertos financeiros com motoristas;
relatórios e DRE; verificação de autenticidade de documentos fiscais junto aos portais públicos da SEFAZ; e um
portal de acesso para o motorista. Cada Cliente contratante opera em ambiente isolado ("empresa"), sem acesso a
dados de outras transportadoras.</p>

<h2>3. Cadastro e Responsabilidade pela Conta</h2>
<p>O acesso à Plataforma é feito por meio de credenciais individuais (e-mail e senha, com autenticação em dois
fatores disponível). O Cliente é responsável por manter a confidencialidade dessas credenciais e por todas as
atividades realizadas em sua conta, inclusive as de usuários que ele mesmo cadastrar com os papéis de administrador
ou operador. O acesso do motorista ao Portal do Motorista é concedido e revogado exclusivamente pelo Cliente.</p>

<h2>4. Planos, Pagamento e Inadimplência</h2>
<p>O uso da Plataforma é condicionado à contratação de um dos planos disponíveis, com cobrança recorrente processada
por meio de parceiro de pagamentos. O não pagamento na data de vencimento pode resultar, após aviso prévio, na
suspensão temporária do acesso à Plataforma até a regularização, sem prejuízo da cobrança dos valores em aberto.
Os dados do Cliente são preservados durante o período de suspensão por inadimplência, salvo solicitação expressa de
exclusão ou encerramento definitivo do contrato.</p>

<h2>5. Uso Adequado</h2>
<p>O Cliente compromete-se a utilizar a Plataforma de forma lícita, e a não: (a) inserir dados de terceiros
(motoristas, clientes) sem a devida autorização ou base legal para tratamento; (b) tentar acessar dados de outra
empresa cliente ou contornar os mecanismos de isolamento e segurança da Plataforma; (c) utilizar a Plataforma para
fins fraudulentos ou que violem a legislação aplicável, incluindo a legislação trabalhista e de trânsito.</p>

<h2>6. Propriedade Intelectual</h2>
<p>O código-fonte, design, marca e demais elementos da Plataforma são de propriedade da Castilho Soluções Digitais.
A contratação de um plano concede ao Cliente uma licença de uso não exclusiva e intransferível, pelo período da
assinatura, não se transferindo qualquer direito de propriedade intelectual sobre o Sistema.</p>

<h2>7. Disponibilidade do Serviço</h2>
<p>Envidamos esforços para manter a Plataforma disponível de forma contínua, mas não garantimos disponibilidade
ininterrupta. Poderão ocorrer interrupções programadas para manutenção, mediante aviso quando possível, bem como
interrupções não programadas decorrentes de fatores fora do nosso controle razoável.</p>

<h2>8. Limitação de Responsabilidade</h2>
<p>A Plataforma é uma ferramenta de apoio à gestão operacional e financeira da transportadora. O Cliente é o único
responsável pela exatidão dos dados inseridos (valores de frete, percentuais de comissão, dados cadastrais) e pelas
decisões tomadas com base nos relatórios e cálculos gerados pelo Sistema. Não nos responsabilizamos por prejuízos
decorrentes de uso indevido, de dados incorretos inseridos pelo próprio Cliente, ou de decisões comerciais tomadas
com base nas informações da Plataforma.</p>

<h2>9. Dados Pessoais e Privacidade</h2>
<p>O tratamento de dados pessoais realizado na Plataforma — inclusive dados de motoristas e clientes cadastrados
pelo Cliente — segue o disposto em nossa <a href="{{ route('legal.privacidade') }}">Política de Privacidade</a>,
parte integrante destes Termos. O Cliente, na qualidade de controlador dos dados de seus motoristas e clientes que
insere na Plataforma, é responsável por garantir que possui base legal adequada para o respectivo tratamento.</p>

<h2>10. Cancelamento e Rescisão</h2>
<p>O Cliente pode solicitar o cancelamento de sua assinatura a qualquer momento pelos canais de suporte. Nos
reservamos o direito de suspender ou encerrar o acesso de contas que violem estes Termos, mediante aviso prévio
sempre que possível. Em caso de encerramento, os dados poderão ser mantidos pelo prazo necessário ao cumprimento de
obrigações legais, sendo posteriormente anonimizados ou eliminados conforme nossa Política de Privacidade.</p>

<h2>11. Alterações destes Termos</h2>
<p>Podemos atualizar estes Termos de Uso periodicamente, para refletir mudanças na Plataforma ou na legislação
aplicável. Alterações relevantes serão comunicadas aos Clientes pelos canais habituais de contato. O uso continuado
da Plataforma após a atualização implica concordância com os novos Termos.</p>

<h2>12. Legislação Aplicável e Foro</h2>
<p>Estes Termos são regidos pela legislação brasileira. Fica eleito o foro da comarca de Leopoldina, Estado de Minas
Gerais, para dirimir eventuais controvérsias, com renúncia a qualquer outro, por mais privilegiado que seja.</p>

<h2>13. Contato</h2>
<p>Dúvidas sobre estes Termos de Uso podem ser encaminhadas para
<a href="mailto:contato@invexa-app.com.br">contato@invexa-app.com.br</a>.</p>
@endsection
