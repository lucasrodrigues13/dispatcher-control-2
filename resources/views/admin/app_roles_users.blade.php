@extends('layouts.app')

@section('conteudo')

<!-- jQuery must be loaded first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- then Toastr -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>



<br><br><br>
<div class="card">

    <div class="card-header py-2">
        <h3 class="card-title">
            Define user roles
        </h3>
    </div>

    <div class="card-body">
        <form action="/salvar_roles_users" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="table-responsive bg-white">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Users</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>
                                    <select class="form-control" name="user_id" disabled>
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <select
                                        class="form-control custom-select"
                                        name="role_id"
                                        id="select_{{ $loop->index }}"
                                        data-value="{{ $item->id }}"
                                    >
                                        <option value="">-- select --</option>

                                        @foreach ($roles as $role)
                                            <option
                                                value="{{ $role->id }}"
                                                {{ (isset($userRoleMap[$item->id]) && $userRoleMap[$item->id] == $role->id)
                                                    ? 'selected' : '' }}
                                            >
                                                {{ $role->id }} – {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div>
                    {{-- <button type="submit" name="Submit" class="btn btn-primary">Save</button> --}}
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    document.querySelectorAll('.custom-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var id_role = this.value;
            var selectId = this.id;
            var id_user = this.getAttribute('data-value');
            // console.log(id_role);
            // console.log(selectId);
            // console.log(id_user);
            $.ajax({
                type: 'POST',
                url: '/actualizar_roles_users',
                data: { role_id: id_role, user_id: id_user },
                success: function(response) {
                    // console.log('Value updated successfully: ', response);
                    toastr.success("User role updated successfully");
                },
                error: function(error) {
                    console.error('Error updating the value: ', error);
                }
            });
        });
    });
</script>

@endsection
