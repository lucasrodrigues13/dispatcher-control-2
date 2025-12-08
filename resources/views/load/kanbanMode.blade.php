{{-- resources/views/load/kanbanMode.blade.php --}}
@extends('layouts.app2')

@section('conteudo')

<div class="container">
  <div class="page-inner">
    <div class="page-header">
      <h3 class="fw-bold mb-3">Loads - Kanban Mode</h3>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home"><a href="#"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Loads</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Kanban</a></li>
      </ul>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">

          {{-- Include do cabeçalho com filtros --}}
          @include('load.partials.kanban-header')

        </div>

        {{-- Include do board Kanban --}}
        @include('load.partials.kanban-board')

        {{-- Include da paginação --}}
        @include('load.partials.kanban-pagination')
      </div>
    </div>
  </div>
</div>

{{-- Include dos modais --}}
@include('load.partials.kanban-modals')

{{-- Include dos scripts --}}
@include('load.partials.kanban-scripts')

@endsection
