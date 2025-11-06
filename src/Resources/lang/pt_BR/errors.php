<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mensagens de Erro - Português Brasileiro
    |--------------------------------------------------------------------------
    |
    | Mensagens de erro amigáveis para integração SSO Keycloak.
    |
    */

    // Erros de Conexão
    'connection' => [
        'title' => 'Erro de Conexão',
        'failed' => 'Não foi possível conectar ao servidor de autenticação. Por favor, tente novamente mais tarde.',
        'timeout' => 'Tempo de conexão com o servidor de autenticação esgotado. Verifique sua conexão com a internet e tente novamente.',
        'unreachable' => 'Servidor de autenticação está inacessível no momento. Entre em contato com o administrador se o problema persistir.',
        'ssl_error' => 'Não foi possível estabelecer uma conexão segura. Entre em contato com o administrador.',
    ],

    // Erros de Autenticação
    'authentication' => [
        'title' => 'Erro de Autenticação',
        'failed' => 'Autenticação falhou. Verifique suas credenciais e tente novamente.',
        'invalid_credentials' => 'Credenciais inválidas fornecidas. Tente novamente.',
        'access_denied' => 'Acesso negado. Você não tem permissão para acessar esta aplicação.',
        'cancelled' => 'Autenticação foi cancelada. Você pode tentar novamente quando estiver pronto.',
        'invalid_state' => 'Estado de autenticação inválido. Isso pode ser devido a uma sessão expirada. Por favor, tente novamente.',
    ],

    // Erros de Token
    'token' => [
        'title' => 'Erro de Token',
        'expired' => 'Sua sessão expirou. Por favor, faça login novamente.',
        'invalid' => 'Token de autenticação inválido. Por favor, faça login novamente.',
        'refresh_failed' => 'Falha ao renovar sua sessão. Por favor, faça login novamente.',
        'revocation_failed' => 'Falha ao revogar sua sessão. Pode ser necessário limpar os cookies do navegador.',
    ],

    // Erros de Provisionamento de Usuário
    'provisioning' => [
        'title' => 'Erro de Configuração de Conta',
        'failed' => 'Falha ao configurar sua conta. Entre em contato com o administrador.',
        'creation_failed' => 'Não foi possível criar sua conta de usuário. Entre em contato com o administrador.',
        'update_failed' => 'Falha ao atualizar as informações da sua conta. Tente novamente mais tarde.',
        'missing_email' => 'Endereço de e-mail é obrigatório mas não foi fornecido pelo servidor de autenticação.',
        'missing_name' => 'Informações de nome estão faltando. Certifique-se de que seu perfil está completo.',
        'duplicate_user' => 'Uma conta com este e-mail já existe com um método de autenticação diferente.',
        'role_mapping_failed' => 'Falha ao atribuir permissões adequadas à sua conta. Entre em contato com o administrador.',
    ],

    // Erros de Configuração
    'configuration' => [
        'title' => 'Erro de Configuração',
        'invalid' => 'Sistema de autenticação não está configurado corretamente. Entre em contato com o administrador.',
        'missing_required' => 'Configuração obrigatória está faltando. Entre em contato com o administrador.',
        'disabled' => 'Autenticação por Single Sign-On está desabilitada no momento.',
    ],

    // Erros Genéricos
    'generic' => [
        'title' => 'Ocorreu um Erro',
        'unknown' => 'Ocorreu um erro inesperado. Tente novamente ou entre em contato com o administrador se o problema persistir.',
        'server_error' => 'Ocorreu um erro no servidor. Por favor, tente novamente mais tarde.',
        'maintenance' => 'O sistema de autenticação está em manutenção. Por favor, tente novamente mais tarde.',
    ],

    // Mensagens de Fallback
    'fallback' => [
        'using_local_auth' => 'Single Sign-On está indisponível. Usando autenticação local.',
        'keycloak_unavailable' => 'Serviço de Single Sign-On está temporariamente indisponível. Você ainda pode fazer login com suas credenciais locais.',
    ],

    // Mensagens de Debug (mostradas apenas quando modo debug está ativo)
    'debug' => [
        'exception_details' => 'Exceção: :exception',
        'error_code' => 'Código de Erro: :code',
        'file_line' => 'Arquivo: :file na linha :line',
        'stack_trace' => 'Rastreamento de Pilha',
    ],
];
