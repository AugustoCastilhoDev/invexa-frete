<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Política de retenção de dados pessoais (LGPD)
    |--------------------------------------------------------------------------
    |
    | Quantos anos, após a exclusão de um registro (motorista, cliente pessoa
    | física ou usuário), o comando `lgpd:anonimizar` aguarda antes de apagar
    | os dados pessoais (CPF, CNH, e-mail, telefone, endereço) mantendo o
    | registro em si para não quebrar o histórico financeiro. Ajuste pelo
    | .env sem precisar mexer em código.
    |
    */

    'retencao_anos' => [
        'motoristas' => (int) env('LGPD_RETENCAO_MOTORISTAS_ANOS', 5),
        'clientes'   => (int) env('LGPD_RETENCAO_CLIENTES_ANOS', 5),
        'usuarios'   => (int) env('LGPD_RETENCAO_USUARIOS_ANOS', 5),
    ],

];
