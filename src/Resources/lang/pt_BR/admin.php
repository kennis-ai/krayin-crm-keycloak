<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Linhas de Idioma da UI Admin - Português Brasileiro
    |--------------------------------------------------------------------------
    |
    | Strings de tradução para interface administrativa do Keycloak SSO.
    |
    */

    'config' => [
        // Títulos de Página
        'title' => 'Configuração Keycloak SSO',
        'edit_title' => 'Editar Configuração Keycloak',
        'role_mappings_title' => 'Mapeamento de Funções',
        'users_title' => 'Usuários Keycloak',

        // Botões
        'configure' => 'Configurar',
        'save' => 'Salvar Configurações',
        'cancel' => 'Cancelar',
        'back' => 'Voltar',
        'test_connection' => 'Testar Conexão',
        'save_mappings' => 'Salvar Mapeamentos',
        'add_mapping' => 'Adicionar Mapeamento',
        'sync_user' => 'Sincronizar Usuário',

        // Status
        'status' => 'Status',
        'enabled' => 'Ativado',
        'disabled' => 'Desativado',

        // Estatísticas
        'total_users' => 'Total de Usuários',
        'keycloak_users' => 'Usuários Keycloak',
        'local_users' => 'Usuários Locais',
        'total_keycloak_users' => 'Total de Usuários Keycloak',
        'active_this_week' => 'Ativos Esta Semana',
        'active_today' => 'Ativos Hoje',

        // Seções de Configuração
        'connection_info' => 'Informações de Conexão',
        'features' => 'Recursos',
        'quick_actions' => 'Ações Rápidas',
        'recent_users' => 'Usuários Keycloak Recentes',
        'general_settings' => 'Configurações Gerais',
        'connection_settings' => 'Configurações de Conexão',
        'feature_settings' => 'Configurações de Recursos',
        'statistics' => 'Estatísticas',

        // Campos
        'base_url' => 'URL do Servidor Keycloak',
        'realm' => 'Domínio (Realm)',
        'client_id' => 'ID do Cliente',
        'client_secret' => 'Segredo do Cliente',
        'redirect_uri' => 'URI de Redirecionamento',
        'auto_provision' => 'Provisionar Usuários Automaticamente',
        'sync_user_data' => 'Sincronizar Dados do Usuário',
        'role_mapping' => 'Mapeamento de Funções',
        'fallback_local' => 'Retornar para Autenticação Local',

        // Texto de Ajuda
        'base_url_help' => 'A URL base do seu servidor Keycloak (ex: https://keycloak.example.com)',
        'realm_help' => 'O nome do domínio (realm) Keycloak para autenticação',
        'client_id_help' => 'O ID do cliente OAuth2 da configuração do seu cliente Keycloak',
        'client_secret_help' => 'O segredo do cliente OAuth2 da configuração do seu cliente Keycloak',
        'redirect_uri_help' => 'A URL de callback para a qual o Keycloak redirecionará após a autenticação',
        'enable_sso' => 'Ativar Keycloak SSO',
        'enable_sso_help' => 'Ativar autenticação Single Sign-On via Keycloak',
        'auto_provision_users' => 'Provisionar Usuários Automaticamente',
        'auto_provision_users_help' => 'Criar contas de usuário automaticamente quando fizerem login via Keycloak',
        'sync_user_data' => 'Sincronizar Dados do Usuário',
        'sync_user_data_help' => 'Sincronizar informações do usuário do Keycloak a cada login',
        'enable_role_mapping' => 'Ativar Mapeamento de Funções',
        'enable_role_mapping_help' => 'Mapear funções Keycloak para funções Krayin CRM automaticamente',
        'allow_local_auth' => 'Permitir Autenticação Local',
        'allow_local_auth_help' => 'Permitir que usuários façam login com credenciais locais quando Keycloak estiver ativado',
        'fallback_on_error' => 'Retornar em Erro',
        'fallback_on_error_help' => 'Retornar automaticamente para autenticação local se a conexão com Keycloak falhar',

        // Ações
        'manage_role_mappings' => 'Gerenciar Mapeamento de Funções',
        'view_keycloak_users' => 'Ver Usuários Keycloak',

        // Mapeamento de Funções
        'role_mappings_info' => 'Configuração de Mapeamento de Funções',
        'role_mappings_description' => 'Mapear funções Keycloak para funções Krayin CRM. Os usuários receberão funções Krayin baseadas em suas funções Keycloak.',
        'keycloak_role' => 'Função Keycloak',
        'krayin_role' => 'Função Krayin',
        'select_role' => 'Selecionar Função',
        'no_mappings' => 'Nenhum mapeamento de função configurado. Clique em "Adicionar Mapeamento" para criar um.',
        'available_roles' => 'Funções Krayin Disponíveis',
        'role_name' => 'Nome da Função',
        'users_count' => 'Contagem de Usuários',

        // Lista de Usuários
        'keycloak_users_list' => 'Lista de Usuários Keycloak',
        'id' => 'ID',
        'name' => 'Nome',
        'email' => 'E-mail',
        'role' => 'Função',
        'keycloak_id' => 'ID Keycloak',
        'last_updated' => 'Última Atualização',
        'actions' => 'Ações',
        'no_keycloak_users' => 'Nenhum usuário fez login via Keycloak ainda.',

        // Mensagens
        'update_success' => 'Configuração Keycloak atualizada com sucesso.',
        'update_failed' => 'Falha ao atualizar configuração Keycloak. Verifique suas configurações e tente novamente.',
        'test_success' => 'Conexão com o servidor Keycloak bem-sucedida!',
        'test_failed' => 'Falha ao conectar ao servidor Keycloak. Verifique sua configuração.',
        'test_error' => 'Ocorreu um erro ao testar a conexão.',
        'test_disabled' => 'Keycloak SSO está atualmente desativado.',
        'role_mappings_updated' => 'Mapeamentos de funções atualizados com sucesso.',
        'role_mappings_failed' => 'Falha ao atualizar mapeamentos de funções.',
        'user_not_keycloak' => 'Este usuário não é um usuário Keycloak.',
        'sync_not_implemented' => 'Sincronização manual de usuário ainda não implementada.',
        'sync_failed' => 'Falha ao sincronizar dados do usuário.',
    ],
];
