<!DOCTYPE html>
<html lang="pt_BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Planilha de Loads</title>
    <link 
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
      rel="stylesheet" 
      integrity="sha384-9ndCyUaO1EUs02Xw19Qe3bUcNdBBIYxLOhCbQ1ccf0z1IWEp+sySGf5BfLY1NBr4" 
      crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Importar Planilha de Loads</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('loads.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="arquivo" class="form-label">Selecione o arquivo Excel (*.xlsx ou *.xls):</label>
                <input class="form-control" type="file" id="arquivo" name="arquivo" required>
            </div>
            <button class="btn btn-primary" type="submit">Importar Planilha</button>
            <a href="{{ route('loads.index') }}" class="btn btn-secondary ms-2">Ver registros</a>
            <a href="{{ route('loads.create') }}" class="btn btn-success ms-2">Cadastro manual</a>
        </form>
    </div>
</body>
</html>
