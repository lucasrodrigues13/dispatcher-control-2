@extends('layouts.app')

@section('conteudo')


<main id="main-container">

    <div class="content">
        <div class="block block-rounded">
<div class="card">

  <div class="card-header py-2">

    <h3 class="card-title">
      Defina as permissões para as determinadas funções
    </h3>
  </div>

</div class="card-body">

<form action="/salvar_permissions_roles" method="POST" enctype="multipart/form-data">
  @csrf
  <div class="table-responsive bg-white">

    <table class="table table-striped table-bordered">

      <thead>
        <tr>
          <th>Perfil {{$role_id}}</th>
        </tr>
      </thead>
      <tbody>
        <td>
          <select class="form-control" id="select_role" name="role_id">
            @foreach($roles as $item)
            <option value="{{$item->id}}" {{$item->id == $role_id ? 'selected' : ''}}>{{$item->name}}</option>
            @endforeach
          </select>
        </td>
      </tbody>
    </table>


  </div>
<br><br>

<table class="table">
  <thead>
  <tr>
            <th scope="col">Premissões</th>
            <th scope="col">Visualização </th>
            <th scope="col">Inclusão </th>
            <th scope="col">Edição </th>
            <th scope="col">Exclusão </th>
        </tr>
  </thead>
  <tbody>
    {{-- Select All --}}
    <tr class="fw-semibold">
        <th scope="row">Select All</th>
        <td><input type="checkbox" class="checkAll" data-column="visualizacao"></td>
        <td><input type="checkbox" class="checkAll" data-column="inclusao"></td>
        <td><input type="checkbox" class="checkAll" data-column="edicao"></td>
        <td><input type="checkbox" class="checkAll" data-column="exclusao"></td>
    </tr>

    {{-- Dashboard --}}
    <tr>
        <th scope="row">Dashboard</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_dashboard" {{ in_array("pode_visualizar_dashboard", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_dashboard" {{ in_array("pode_registrar_dashboard", old('inclusao',  $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_dashboard"    {{ in_array("pode_editar_dashboard",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_dashboard"  {{ in_array("pode_eliminar_dashboard",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Dispatchers --}}
    <tr>
        <th scope="row">Dispatchers</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_dispatchers" {{ in_array("pode_visualizar_dispatchers", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_dispatchers" {{ in_array("pode_registrar_dispatchers", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_dispatchers"    {{ in_array("pode_editar_dispatchers",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_dispatchers"  {{ in_array("pode_eliminar_dispatchers",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Employees --}}
    <tr>
        <th scope="row">Employees</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_employees" {{ in_array("pode_visualizar_employees", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_employees" {{ in_array("pode_registrar_employees", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_employees"    {{ in_array("pode_editar_employees",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_employees"  {{ in_array("pode_eliminar_employees",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Carriers --}}
    <tr>
        <th scope="row">Carriers</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_carriers" {{ in_array("pode_visualizar_carriers", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_carriers" {{ in_array("pode_registrar_carriers", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_carriers"    {{ in_array("pode_editar_carriers",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_carriers"  {{ in_array("pode_eliminar_carriers",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Drivers --}}
    <tr>
        <th scope="row">Drivers</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_drivers" {{ in_array("pode_visualizar_drivers", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_drivers" {{ in_array("pode_registrar_drivers", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_drivers"    {{ in_array("pode_editar_drivers",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_drivers"  {{ in_array("pode_eliminar_drivers",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Brokers --}}
    <tr>
        <th scope="row">Brokers</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_brokers" {{ in_array("pode_visualizar_brokers", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_brokers" {{ in_array("pode_registrar_brokers", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_brokers"    {{ in_array("pode_editar_brokers",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_brokers"  {{ in_array("pode_eliminar_brokers",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Deals --}}
    <tr>
        <th scope="row">Deals</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_deals" {{ in_array("pode_visualizar_deals", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_deals" {{ in_array("pode_registrar_deals", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_deals"    {{ in_array("pode_editar_deals",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_deals"  {{ in_array("pode_eliminar_deals",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Commissions --}}
    <tr>
        <th scope="row">Commissions</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_commissions" {{ in_array("pode_visualizar_commissions", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_commissions" {{ in_array("pode_registrar_commissions", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_commissions"    {{ in_array("pode_editar_commissions",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_commissions"  {{ in_array("pode_eliminar_commissions",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Loads --}}
    <tr>
        <th scope="row">Loads</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_loads" {{ in_array("pode_visualizar_loads", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_loads" {{ in_array("pode_registrar_loads", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_loads"    {{ in_array("pode_editar_loads",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_loads"  {{ in_array("pode_eliminar_loads",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- New Invoice --}}
    <tr>
        <th scope="row">New Invoice</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_invoices.create" {{ in_array("pode_visualizar_invoices.create", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_invoices.create" {{ in_array("pode_registrar_invoices.create", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_invoices.create"    {{ in_array("pode_editar_invoices.create",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_invoices.create"  {{ in_array("pode_eliminar_invoices.create",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Time Line Charges --}}
    <tr>
        <th scope="row">Time Line Charges</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_invoices.index" {{ in_array("pode_visualizar_invoices.index", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_invoices.index" {{ in_array("pode_registrar_invoices.index", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_invoices.index"    {{ in_array("pode_editar_invoices.index",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_invoices.index"  {{ in_array("pode_eliminar_invoices.index",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Charge Setup --}}
    <tr>
        <th scope="row">Charge Setup</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_charges_setups.index" {{ in_array("pode_visualizar_charges_setups.index", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_charges_setups.index" {{ in_array("pode_registrar_charges_setups.index", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_charges_setups.index"    {{ in_array("pode_editar_charges_setups.index",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_charges_setups.index"  {{ in_array("pode_eliminar_charges_setups.index",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Permissions and Roles --}}
    <tr>
        <th scope="row">Permissions and Roles</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_permissions_roles" {{ in_array("pode_visualizar_permissions_roles", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_permissions_roles" {{ in_array("pode_registrar_permissions_roles", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_permissions_roles"    {{ in_array("pode_editar_permissions_roles",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_permissions_roles"  {{ in_array("pode_eliminar_permissions_roles",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>

    {{-- Roles and Users --}}
    <tr>
        <th scope="row">Roles and Users</th>
        <td><input class="form-check-input" type="checkbox" name="visualizacao[]" value="pode_visualizar_roles_users" {{ in_array("pode_visualizar_roles_users", old('visualizacao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="inclusao[]"       value="pode_registrar_roles_users" {{ in_array("pode_registrar_roles_users", old('inclusao', $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="edicao[]"         value="pode_editar_roles_users"    {{ in_array("pode_editar_roles_users",    old('edicao',    $selected)) ? 'checked' : '' }}></td>
        <td><input class="form-check-input" type="checkbox" name="exclusao[]"       value="pode_eliminar_roles_users"  {{ in_array("pode_eliminar_roles_users",  old('exclusao',  $selected)) ? 'checked' : '' }}></td>
    </tr>
</tbody>

</table>
<center> <button type="submit" name="Enviar" class="btn btn-primary">Salvar</button> </center>
<br>
</form>


<script>
    document.querySelector('#select_role').addEventListener('change', function() {
      let value = this.value;
      // use o método fetch para enviar a requisição
      window.location.href = "/permissions_roles_by_id/" + value;
    });
</script>


<script>
    // Adicione event listeners para os checkboxes de marcar/desmarcar colunas
    const checkAllCheckboxes = document.querySelectorAll('.checkAll');
    checkAllCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const column = this.getAttribute('data-column');
            const checkboxes = document.querySelectorAll(`input[name="${column}[]"]`);
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    });
</script>
</div>
</main>
@endsection
