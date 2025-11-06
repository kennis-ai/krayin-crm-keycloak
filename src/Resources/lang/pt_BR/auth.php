<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Linhas de Idioma de Autenticação Keycloak (Português Brasileiro)
    |--------------------------------------------------------------------------
    |
    | As seguintes linhas de idioma são usadas durante a autenticação Keycloak
    | para várias mensagens que precisamos exibir ao usuário.
    |
    */

    'sso_disabled' => 'O Single Sign-On está desabilitado no momento. Por favor, use autenticação local.',
    'sso_failed' => 'A autenticação com o Keycloak falhou. Por favor, tente novamente.',
    'sso_failed_fallback' => 'A autenticação com o Keycloak falhou. Você pode tentar fazer login com suas credenciais locais.',
    'login_success' => 'Você foi autenticado com sucesso via Keycloak.',
    'logout_success' => 'Você foi desconectado com sucesso.',
    'logout_partial_success' => 'Você foi desconectado localmente, mas o logout do Keycloak pode ter falh ado.',
    'already_logged_out' => 'Você já está desconectado.',
    'session_expired' => 'Sua sessão expirou. Por favor, faça login novamente.',
    'token_refresh_failed' => 'Falha ao renovar sua sessão. Por favor, faça login novamente.',
];
