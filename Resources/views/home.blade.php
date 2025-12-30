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

      <button id="export-btn" class="btn btn-success">
          Exportar CSV
      </button>
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

    const bar = document.getElementById('factory-progress');

    /* =======================
      Funções utilitárias
    ======================= */
    const resetProgressBar = (label = '0%') => {
      bar.style.width = '0%';
      bar.textContent = label;
      bar.className = 'progress-bar bg-primary';
    };

    const updateProgressBar = (current, total, prefix = '') => {
      const percent = Math.round((current / total) * 100);
      bar.style.width = percent + '%';
      bar.textContent = `${prefix}${percent}% — ${current}/${total}`;
    };

    const finishProgressBar = (label) => {
      bar.style.width = '100%';
      bar.textContent = label;
      bar.className = 'progress-bar bg-success';
    };

    const setInfoBar = (label) => {
      bar.className = 'progress-bar bg-info';
      bar.textContent = label;
    };

    /* =======================
      WebSocket
    ======================= */
    ws.onopen = () => {
      ws.send(JSON.stringify({ action: "register", clientId }));
    };

    ws.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);

        switch (data.type) {

          /* ===== Factory ===== */
          case 'progress':
            updateProgressBar(data.inserted, data.total);
            break;

          case 'finished':
              finishProgressBar(`Concluído — ${data.total} usuários`);
              if (data.users) refreshUserTable(data.users); // <-- aqui atualiza a tabela
              break;


          /* ===== Export ===== */
          case 'export_progress':
            updateProgressBar(
              data.exported,
              data.total,
              'Exportando — '
            );
            bar.className = 'progress-bar bg-info';
            break;

          case 'export_ready':
            finishProgressBar('Exportação concluída');

            // cria um link temporário para download
            const a = document.createElement('a');
            a.href = data.url;        // URL que vem do WebSocket
            a.target = '_blank';      // abre em nova aba (opcional)
            a.download = '';          // força download
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            break;


          case 'registered':
            resetProgressBar();
            break;

          default:
            console.warn('Mensagem WS desconhecida:', data);
        }

      } catch (err) {
        console.error('Erro WS:', err);
      }
    };

    ws.onclose = () => console.log("WS fechado");
    ws.onerror = (err) => console.error("Erro WS:", err);

    /* =======================
      Tabela
    ======================= */
function refreshUserTable(users) {
    const tbody = document.getElementById('user-table');
    tbody.innerHTML = ''; // limpa a tabela

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
}
  function refreshUserTable(users) {
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
  }


    /* =======================
      Botões
    ======================= */
    document.getElementById('factory-btn').addEventListener('click', () => {
      resetProgressBar('Criando usuários...');
      ws.send(JSON.stringify({
        action: 'create_users',
        count: parseInt(document.getElementById('user-count').value) || 100,
        clientId
      }));
    });

    document.getElementById('export-btn').addEventListener('click', () => {
      resetProgressBar('Iniciando exportação...');
      ws.send(JSON.stringify({
        action: 'export_users',
        clientId
      }));
    });
</script>

</body>
</html>
