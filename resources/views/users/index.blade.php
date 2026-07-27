@extends('layouts.master')

@section('title')
Les Utilisateurs
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <p class="category">
                    Gestion des comptes utilisateurs
                </p>
            </div>
            <div class="card-body">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Service</th>
                            <th>Inscrit le</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                            @foreach($users as $key => $u)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge badge-{{ $u->isAdmin() ? 'danger' : 'info' }}">
                                        {{ $u->isAdmin() ? 'Admin' : 'User' }}
                                    </span>
                                </td>
                                <td>{{ $u->service ?: '—' }}</td>
                                <td>{{ $u->created_at?->format('Y-m-d') }}</td>
                                <td class="text-right">
                                    <a data-id="{{ $u->id }}"
                                       data-name="{{ $u->name }}"
                                       data-role="{{ $u->role->value }}"
                                       data-service="{{ $u->service }}"
                                       data-toggle="modal" data-target="#editUserModal"
                                       type="button" class="btn btn-success btn-sm edit-user-btn" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>

                                    @if($u->id !== auth()->id())
                                    <a data-id="{{ $u->id }}"
                                       data-name="{{ $u->name }}"
                                       data-toggle="modal" data-target="#deleteUserModal"
                                       type="button" class="btn btn-danger btn-sm delete-user-btn" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $users->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-info" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Nom</span>
                        </div>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rôle</span>
                        </div>
                        <select class="form-control" name="role" id="edit_role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="input-group" id="edit_service_group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Service</span>
                        </div>
                        <select class="form-control" name="service" id="edit_service">
                            <option value="">Aucun</option>
                            @foreach($services as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-danger" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer l'utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteUserForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer <strong id="delete_user_name"></strong> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session('success'))
<!-- Modale de succès façon confirmation -->
<div id="successOverlay" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; display:flex; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:32px; width:90%; max-width:380px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <div style="margin:0 auto 16px; width:64px; height:64px; border-radius:50%; border:3px solid #28a745; display:flex; align-items:center; justify-content:center;">
            <span style="color:#28a745; font-size:32px;">&#10003;</span>
        </div>
        <p style="color:#333; margin-bottom:20px;">{{ session('success') }}</p>
        <button id="closeSuccessUsers" class="btn btn-info" style="width:100%;">OK</button>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
$('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var role = button.data('role');

    $('#editUserForm').attr('action', '/utilisateurs/' + id);
    $('#edit_name').val(button.data('name'));
    $('#edit_role').val(role);
    $('#edit_service').val(button.data('service'));

    var isLastAdmin = (role === 'admin' && {{ $adminCount }} <= 1);
    $('#edit_role option[value="user"]').prop('disabled', isLastAdmin);

    toggleServiceField(role);
});

$('#edit_role').on('change', function () {
    toggleServiceField($(this).val());
});

function toggleServiceField(role) {
    if (role === 'admin') {
        $('#edit_service_group').hide();
        $('#edit_service').val('');
    } else {
        $('#edit_service_group').show();
    }
}

$('#deleteUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#deleteUserForm').attr('action', '/utilisateurs/' + id);
    $('#delete_user_name').text(button.data('name'));
});

var closeBtn = document.getElementById('closeSuccessUsers');
if (closeBtn) {
    closeBtn.addEventListener('click', function () {
        document.getElementById('successOverlay').style.display = 'none';
    });
}
</script>
@endsection
