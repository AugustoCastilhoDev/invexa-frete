@extends('layouts.legal')
@section('title', 'Política de Privacidade')

@section('content')
<p>Esta Política de Privacidade descreve como a Castilho Soluções Digitais, operadora do Invexa Frete, trata os
dados pessoais no âmbito da Plataforma, em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 —
LGPD).</p>

<h2>1. Papéis no Tratamento de Dados</h2>
<p>A Castilho Soluções Digitais atua como <strong>operadora</strong> dos dados de motoristas, veículos e clientes
cadastrados por cada transportadora na Plataforma, tratando-os sob as instruções e para as finalidades definidas
pelo Cliente, que atua como <strong>controlador</strong> desses dados. Para os dados dos próprios usuários que
acessam o painel administrativo (administradores e operadores), a Castilho Soluções Digitais atua como
controladora.</p>

<h2>2. Quais Dados Coletamos</h2>
<ul>
    <li><strong>Usuários do painel administrativo:</strong> nome, e-mail, senha (armazenada de forma criptografada) e,
    opcionalmente, dados de autenticação em dois fatores.</li>
    <li><strong>Motoristas:</strong> nome, CPF, CNH e categoria, validade da CNH, telefone, e-mail e, quando o acesso
    ao Portal do Motorista é habilitado, senha de acesso criptografada.</li>
    <li><strong>Veículos:</strong> placa, modelo, marca, RENAVAM, chassi e documentos de regularização.</li>
    <li><strong>Clientes da transportadora:</strong> nome/razão social, CPF/CNPJ, endereço e dados de contato.</li>
    <li><strong>Dados operacionais e financeiros:</strong> viagens, valores de frete, lançamentos, descontos,
    documentos fiscais (CT-e, MDF-e, NF-e) e respectivos comprovantes anexados.</li>
    <li><strong>Dados de acesso:</strong> registros de login (data, hora e IP), para fins de segurança e auditoria.</li>
</ul>

<h2>3. Base Legal para o Tratamento</h2>
<p>O tratamento dos dados pessoais na Plataforma se baseia, conforme o caso: na execução de contrato (art. 7º, V,
LGPD), para viabilizar a prestação do serviço contratado pelo Cliente; no cumprimento de obrigação legal (art. 7º,
II), quando aplicável a documentos fiscais e trabalhistas; e no legítimo interesse (art. 7º, IX), para fins de
segurança, prevenção a fraudes e melhoria da Plataforma.</p>

<h2>4. Finalidade do Tratamento</h2>
<p>Os dados são utilizados exclusivamente para: viabilizar o cadastro e a operação da transportadora na Plataforma;
calcular valores de frete, comissão, descontos e acertos financeiros; permitir o acompanhamento das viagens pelo
motorista via Portal; gerar relatórios gerenciais para o Cliente; e cumprir obrigações legais e contratuais.</p>

<h2>5. Compartilhamento de Dados com Terceiros</h2>
<p>Não vendemos dados pessoais. Compartilhamos dados apenas com prestadores de serviço estritamente necessários à
operação da Plataforma, sempre sob obrigações contratuais de confidencialidade e segurança:</p>
<ul>
    <li><strong>Processamento de pagamentos</strong> (cobrança das assinaturas dos Clientes);</li>
    <li><strong>Envio de e-mails transacionais</strong> (notificações do sistema, redefinição de senha);</li>
    <li><strong>Armazenamento de arquivos na nuvem</strong> (comprovantes, documentos fiscais e assinaturas digitais
    anexados às viagens);</li>
    <li><strong>Portais públicos da SEFAZ</strong>, exclusivamente para consulta de autenticidade de documentos
    fiscais informados pelo próprio Cliente, mediante a chave de acesso do documento.</li>
</ul>

<h2>6. Retenção e Eliminação de Dados</h2>
<p>Os dados são mantidos enquanto durar a relação contratual com o Cliente e pelo prazo adicional necessário ao
cumprimento de obrigações legais (fiscais, trabalhistas e regulatórias). Encerrado esse prazo, dados pessoais
podem ser anonimizados, deixando de identificar o titular, mas preservando os registros históricos e financeiros
necessários à transportadora.</p>

<h2>7. Direitos do Titular</h2>
<p>Nos termos do art. 18 da LGPD, o titular dos dados pode solicitar, mediante requisição ao Cliente (transportadora
responsável pelo cadastro) ou diretamente a nós: confirmação da existência de tratamento; acesso aos dados;
correção de dados incompletos, inexatos ou desatualizados; anonimização, bloqueio ou eliminação de dados
desnecessários ou tratados em desconformidade com a lei; e portabilidade dos dados, observados os segredos comercial
e industrial. Solicitações podem ser encaminhadas para
<a href="mailto:contato@invexa-app.com.br">contato@invexa-app.com.br</a>.</p>

<h2>8. Segurança da Informação</h2>
<p>Adotamos medidas técnicas e organizacionais para proteger os dados pessoais, incluindo: isolamento lógico dos
dados de cada transportadora (multi-tenant); senhas armazenadas com hash criptográfico; autenticação em dois
fatores disponível para contas administrativas; limitação de tentativas de login para mitigar ataques de força
bruta; e mascaramento de CPF e CNH nas telas do sistema, com exibição completa apenas mediante ação explícita do
usuário autorizado.</p>

<h2>9. Cookies</h2>
<p>A Plataforma utiliza cookies estritamente necessários ao funcionamento do sistema, como os de sessão de login e
preferência de tema (claro/escuro). Não utilizamos cookies de rastreamento publicitário dentro da área logada do
sistema.</p>

<h2>10. Encarregado de Dados (DPO)</h2>
<p>Para exercer seus direitos como titular de dados ou esclarecer dúvidas sobre esta Política, entre em contato pelo
e-mail <a href="mailto:contato@invexa-app.com.br">contato@invexa-app.com.br</a>.</p>

<h2>11. Alterações desta Política</h2>
<p>Esta Política de Privacidade pode ser atualizada periodicamente para refletir mudanças na Plataforma ou na
legislação aplicável. A data da última atualização está indicada no topo desta página.</p>
@endsection
