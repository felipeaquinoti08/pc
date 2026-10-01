<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Como preparar o GLPI antes de cadastrar aqui</h5>
        <p class="mb-0">São 4 passos feitos <strong>dentro do próprio GLPI</strong> (não neste painel), com um usuário Super-Admin. Só depois disso os campos ao lado têm o que preencher.</p>
    </div>
    <div class="card-body">
        <ol class="mb-0">
            <li class="mb-3">
                <strong>Habilitar a API REST (v2)</strong><br>
                No GLPI: <code>Configurar → Geral → API</code>. Marque a opção de habilitar a API. Sem isso, nenhuma chamada funciona.
            </li>
            <li class="mb-3">
                <strong>Criar um Cliente OAuth</strong><br>
                Ainda em <code>Configurar → Geral → API</code>, aba/seção <strong>Clientes OAuth</strong> → adicionar novo:
                <ul class="mt-2">
                    <li>Nome: algo que identifique este painel (ex: "Painel Central - Import de Ativos")</li>
                    <li><strong>Grants</strong>: marque <code>password</code></li>
                    <li><strong>Scopes</strong>: marque <code>api</code></li>
                </ul>
                Ao salvar, o GLPI mostra o <strong>Client ID</strong> e o <strong>Client Secret</strong> — <strong>copie os dois agora</strong>, o secret só aparece essa vez.
            </li>
            <li class="mb-3">
                <strong>Criar (ou escolher) uma conta técnica</strong><br>
                Uma conta de usuário no GLPI dedicada a essa integração (não a sua conta pessoal), com um perfil que tenha permissão de criar/editar ativos e cadastros de apoio (localização, fabricante...) — o mais simples é usar o perfil <strong>Super-Admin</strong>. Anote o login e a senha.
            </li>
            <li>
                <strong>Preencher o formulário ao lado</strong><br>
                URL base do GLPI, Client ID, Client Secret, e o usuário/senha da conta técnica. Salve e clique em <strong>"Testar Conexão"</strong> pra confirmar que está tudo certo antes de importar qualquer ativo.
            </li>
        </ol>
    </div>
</div>
