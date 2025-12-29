<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Users Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background-color: #f8f9fa; }
  .table-container { margin-top: 50px; }
  .table-wrapper { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
  .progress { height: 22px; margin-bottom: 15px; }
</style>
</head>
<body>
<div class="container table-container d-flex justify-content-center">
  <div class="col-12 col-md-10 table-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <input type="number" id="user-count" class="form-control" placeholder="Qtd. Users" style="width:120px;">
        <button id="factory-btn" class="btn btn-primary">Factory Users</button>
      </div>
    </div>

    <div class="progress">
      <div id="factory-progress" class="progress-bar" role="progressbar" style="width: 0%;">0%</div>
    </div>

    <table class="table table-hover table-bordered text-center">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Data de Criação</th>
        </tr>
      </thead>
      <tbody id="user-table">
        @foreach($users as $user)
          <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const clientId = `${Date.now()}-${Math.floor(Math.random()*100000)}`;
const ws = new WebSocket("ws://localhost:9502");

ws.onopen = () => {
  console.log("Conectado ao WebSocket");
  ws.send(JSON.stringify({ action: "register", clientId }));
};

ws.onmessage = (event) => {
  try {
    const data = JSON.parse(event.data);
    const bar = document.getElementById('factory-progress');

    // Função para resetar a barra
    const resetProgressBar = () => {
      bar.style.width = '0%';
      bar.textContent = '0%';
      bar.classList.remove('bg-success', 'bg-warning', 'bg-info', 'bg-primary');
      bar.classList.add('bg-primary'); // cor padrão durante o progresso
    };

    // Função para atualizar a barra de progresso
    const updateProgressBar = (inserted, total) => {
      const percent = Math.round((inserted / total) * 100);
      bar.style.width = percent + '%';
      bar.textContent = `${percent}% — ${inserted}/${total}`;
    };

    // Função para finalizar a barra
    const finishProgressBar = (total) => {
      bar.style.width = '100%';
      bar.textContent = `Concluído — ${total} usuários`;
      bar.classList.remove('bg-primary', 'bg-warning', 'bg-info');
      bar.classList.add('bg-success');
    };

    // Função para atualizar a tabela de usuários
    const refreshUserTable = (users) => {
      const tbody = document.getElementById('user-table');
      tbody.innerHTML = '';

      users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${user.id}</td>
          <td>${user.name}</td>
          <td>${user.email}</td>
          <td>${user.created_at}</td>
        `;
        tbody.appendChild(tr);
      });
    };

    // Processamento das mensagens
    switch (data.type) {
      case 'progress':
        updateProgressBar(data.inserted, data.total);
        break;

      case 'finished':
        finishProgressBar(data.total);
        if (data.users) refreshUserTable(data.users);
        break;

      case 'registered':
        console.log('Registrado no WS com clientId:', data.clientId);
        resetProgressBar(); // reseta a barra quando um novo registro inicia
        break;

      default:
        console.warn('Tipo de mensagem WS desconhecido:', data.type);
    }

  } catch(err) {
    console.error('Erro ao processar mensagem WS:', err);
  }
};


ws.onclose = () => console.log("Conexão WebSocket fechada");
ws.onerror = (err) => console.error("Erro no WebSocket:", err);

// botão agora envia ação pelo WS
document.getElementById('factory-btn').addEventListener('click', () => {
  ws.send(JSON.stringify({
    action: 'create_users',
    count: parseInt(document.getElementById('user-count').value) || 100,
    clientId
  }));
});

</script>
</body>
</html>
