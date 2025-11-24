{{-- Componente reutilizável para exibir todas as mensagens flash --}}

@if(session('success'))
    <x-flash-message type="success">
        {{ session('success') }}
    </x-flash-message>
@endif

@if(session('error'))
    <x-flash-message type="error">
        {{ session('error') }}
    </x-flash-message>
@endif

@if(session('warning'))
    <x-flash-message type="warning">
        {{ session('warning') }}
    </x-flash-message>
@endif

@if(session('info'))
    <x-flash-message type="info">
        {{ session('info') }}
    </x-flash-message>
@endif

{{-- Exibir erros de validação do Laravel --}}
@if($errors->any())
    <x-flash-message type="error">
        <div class="fw-bold mb-2">Por favor, corrija os seguintes erros:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-flash-message>
@endif

