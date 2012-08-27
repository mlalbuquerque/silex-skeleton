<?php

/**
 * Regras para Autorizações do Sistema
 * free
 *   As rotas livres. Todos acessam!
 * allow
 *   As rotas permitidas para determinado perfil!
 *   O índice é o perfil e o valor são as rotas (array).
 *   Pode usar da seguite forma:
 *     'perfil' => array('rota.trecho') - perfil só acessa esta rota específica
 *     'perfil' => array('rota.trecho1', 'rota.trecho2') - perfil acessa apenas estas duas rotas
 *     'perfil' => array('rota.*') - perfil acessa todos os trechos da rota
 *     'perfil' => array('*.main') - perfil acessa o trecho main de qualquer rota
 *     'perfil' => array('*.*') OU 'perfil' => 'all' - perfil acessa tudo
 * deny
 *   As rotas proibidas para determinado perfil!
 *   Segue as mesmas regras do allow.
 *
 * A "rota.trecho" é o nome da rota (route). Pra usar, deve setar o nome usando o método bind (veja um exemplo em controllers/app.php)
 */
return array(
    "free" => array(
        "homepage",
        "auth.*"
    )
);
