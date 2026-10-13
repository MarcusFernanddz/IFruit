# Autenticação do IFruit

## Primeira configuração

1. Faça um backup do banco de dados `ifruit`.
2. No phpMyAdmin, selecione `ifruit` e execute uma única vez o arquivo `ifruit_auth_migration.sql` da raiz do projeto.
3. Abra `http://localhost/IFruit/pages/configurar-admin.php` e defina nome, e-mail e uma senha com pelo menos 10 caracteres para o primeiro administrador.
4. Entre em `http://localhost/IFruit/pages/login%20(1).php` com essas credenciais.
5. Use **Contas de acesso** para registrar contas de estagiários. O administrador pode desativar ou reativar essas contas.

A configuração inicial é bloqueada depois que existir uma senha válida na tabela de contas. Senhas são armazenadas com `password_hash`; vendas novas guardam o ID do usuário e uma cópia do nome e e-mail usados no momento do registro.

O perfil `admin` ou `estagiario` e o campo `criado_por` deixam a estrutura pronta para definir permissões mais específicas futuramente. Por enquanto, ambos os perfis autenticados acessam as funções operacionais; somente administradores gerenciam contas.