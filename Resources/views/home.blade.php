<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Users Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .table-container {
      margin-top: 50px;
    }
    .table-wrapper {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
<div class="container table-container d-flex justify-content-center">
  <div class="col-12 col-md-10 table-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <input type="number" class="form-control" placeholder="Qtd. Users" id="user-count" style="width:120px;">
        <button class="btn btn-primary" id="factory-btn">Factory Users</button>
      </div>
      <button class="btn btn-success" id="export-btn">Export to Excel</button>
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
        
        {{-- @forelse($users as $user)
          <tr>
            <td>{{ $user['id'] }}</td>
            <td>{{ $user['name'] }}</td>
            <td>{{ $user['email'] }}</td>
            <td>{{ $user['created_at'] }}</td>
          </tr>
        @empty
          <h1>Nenhum usuário encontrado.</h1>
        @endforelse --}}
      </tbody>
    </table>

    {{-- Paginação --}}
    {{-- <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        @if($currentPage > 1)
          <li class="page-item">
            <a class="page-link" href="?page={{ $currentPage - 1 }}">Anterior</a>
          </li>
        @endif

        @for($i = 1; $i <= $totalPages; $i++)
          <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
            <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
          </li>
        @endfor

        @if($currentPage < $totalPages)
          <li class="page-item">
            <a class="page-link" href="?page={{ $currentPage + 1 }}">Próximo</a>
          </li>
        @endif
      </ul>
    </nav> --}}
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
  document.getElementById('factory-btn').addEventListener('click', function() {
    const count = document.getElementById('user-count').value;
    alert(`Você vai criar ${count} usuários (implemente o backend)!`);
  });

  document.getElementById('export-btn').addEventListener('click', function() {
    alert('Export to Excel (implemente o backend)!');
  });
</script>
</body>
</html>
