@extends('layouts.app')

@section('conteudo')

<br>
<center>
  @if (session('erro'))
  {{-- expr --}}
  <div class="alert alert-danger" role="alert">
    {{session('erro')}}
  </div>
  @endif
</center>



        <!-- Hero -->
        <div class="bg-image" style="background-image: url('assets/media/photos/photo17@2x.jpg');">
          <div class="bg-black-75">
            <div class="content content-full">
              <div class="py-5 text-center">
                <a class="img-link" href="javascript::void(0)">
                  <img id="img-avatar" class="img-avatar img-avatar96 img-avatar-thumb" src="{{$user->foto ?? 'assets/media/avatars/avatar10.jpg'}}" alt="">
                </a>
                <h1 class="fw-bold my-2 text-white">Meu Perfil</h1>
                <h2 class="h4 fw-bold text-white-75">
                  {{$user->name}}
                </h2>
                <a class="btn btn-primary" href="/baixar_apostila">
                   Baixar Minha Apostila
                </a>
              </div>
            </div>
          </div>
        </div>
        <!-- END Hero -->


          <div class="block block-rounded">
            <div class="block-content">
              <form id="profile-form" action="/actualizar_perfil" method="POST" enctype="multipart/form-data">
                <!-- Perfil do Usuário -->
                @csrf
                <h2 class="content-heading pt-0">
                  <i class="fa fa-fw fa-user-circle text-muted me-1"></i> Perfil do Usuário
                </h2>
                <div class="row push">
                  <div class="col-lg-4">
                    <p class="text-muted">
                      Visualize e altere as tuas configurações de perfil de Usuário.
                    </p>
                  </div>
                  <div class="col-lg-8 col-xl-5">

                    <div class="mb-4">
                      <label class="form-label" for="name">Nome Completo</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Teu Nome.." value="{{$user->name}}">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="email">Endereço de Email</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Teu Email.." value="{{$user->email}}">
                    </div>

                    <div class="mb-4">
                      <label class="form-label">Teu Avatar</label>
                      <div class="push">
                        <img class="img-avatar" src="{{$user->foto ?? 'assets/media/avatars/avatar10.jpg'}}" alt="">
                      </div>
                      <label class="form-label" for="dm-profile-edit-avatar">Escolha uma Foto</label>
                      <input class="form-control" type="file" name="foto" id="dm-profile-edit-avatar">
                    </div>
                  </div>
                </div>
                <!-- END Perfil do Usuário -->

                <!-- Change Password -->
                <h2 class="content-heading pt-0">
                  <i class="fa fa-fw fa-asterisk text-muted me-1"></i> Alterar Palavra Passe
                </h2>
                <div class="row push">
                  <div class="col-lg-4">
                    <p class="text-muted">
                      Altere a tua palavra passe referente ao teu perfil de usuário
                    </p>
                  </div>
                  <div class="col-lg-8 col-xl-5">
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-password">Senha Atual</label>
                      <input type="password" class="form-control" id="dm-profile-edit-password" name="dm-profile-edit-password">
                    </div>
                    <div class="row mb-4">
                      <div class="col-12">
                        <label class="form-label" for="dm-profile-edit-password-new">Nova Atual</label>
                        <input type="password" class="form-control" id="dm-profile-edit-password-new" name="dm-profile-edit-password-new">
                      </div>
                    </div>
                    <div class="row mb-4">
                      <div class="col-12">
                        <label class="form-label" for="dm-profile-edit-password-new-confirm">Confirmar Senha</label>
                        <input type="password" class="form-control" id="dm-profile-edit-password-new-confirm" name="dm-profile-edit-password-new-confirm">
                      </div>
                    </div>
                  </div>
                </div>
                <!-- END Change Password -->


                <!-- Submit -->
                <div class="row push">
                  <div class="col-lg-8 col-xl-5 offset-lg-4">
                    <div class="mb-4">
                      <button type="submit" class="btn btn-alt-primary">
                        <i class="fa fa-check-circle opacity-50 me-1"></i> Atualizar Perfil
                      </button>
                    </div>
                  </div>
                </div>
                <!-- END Submit -->
              </form>
            </div>
          </div>


          <script>
  document.addEventListener("DOMContentLoaded", function() {
    // Get references to the elements
    const imgAvatar = document.getElementById("img-avatar");
    const profileEditAvatarInput = document.getElementById("dm-profile-edit-avatar");
    const profileForm = document.getElementById("profile-form");

    // Add a click event listener to the img-avatar
    imgAvatar.addEventListener("click", function() {
      // When the img-avatar is clicked, trigger the click event on the input element
      profileEditAvatarInput.click();
    });

    // Add a change event listener to the file input field
    profileEditAvatarInput.addEventListener("change", function() {
      // When a file is selected, automatically submit the form
      profileForm.submit();
    });
  });
</script>


@endsection
