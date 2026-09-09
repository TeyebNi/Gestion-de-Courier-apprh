@extends('layouts.master')

@section('title')
Les Utilisateurs
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Gestion des Comptes Utilisateurs
                    <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#createUserModal">Nouvel Utilisateur</button>
                </h4>
                <div class="d-flex align-items-center flex-wrap">
                    @include('partials.search-box', ['route' => 'users.index', 'placeholder' => 'Rechercher par nom ou email...'])
                    <a href="{{ route('users.audit-log') }}" class="btn btn-secondary btn-sm mr-2" title="Journal des actions sur les comptes"><i class="fas fa-history"></i> Journal</a>
                    <a href="{{ route('users.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </div>
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
                            @forelse($users as $key => $u)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge badge-{{ $u->isAdmin() ? 'danger' : ($u->isMaireAdjoint() ? 'warning' : 'info') }}">
                                        {{ $u->isAdmin() ? 'Admin' : ($u->isMaireAdjoint() ? 'Adjoint au Maire' : 'User') }}
                                    </span>
                                </td>
                                <td>{{ $u->service ?: '—' }}</td>
                                <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <a data-id="{{ $u->id }}"
                                       data-name="{{ $u->name }}"
                                       data-email="{{ $u->email }}"
                                       data-role="{{ $u->role->value }}"
                                       data-service="{{ $u->service }}"
                                       data-can-manage-users="{{ $u->can_manage_users !== false ? '1' : '0' }}"
                                       data-can-access-cabinet="{{ $u->can_access_cabinet !== false ? '1' : '0' }}"
                                       data-can-access-all-services="{{ $u->can_access_all_services !== false ? '1' : '0' }}"
                                       data-toggle="modal" data-target="#editUserModal"
                                       type="button" class="btn btn-success btn-sm edit-user-btn" title="Modifier"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>

                                    <a data-id="{{ $u->id }}"
                                       data-name="{{ $u->name }}"
                                       data-toggle="modal" data-target="#resetPasswordModal"
                                       type="button" class="btn btn-warning btn-sm" title="Réinitialiser le mot de passe"><i class="fas fa-key"></i></a>

                                    @if($u->id !== auth()->id())
                                    <a data-id="{{ $u->id }}"
                                       data-name="{{ $u->name }}"
                                       data-toggle="modal" data-target="#deleteUserModal"
                                       type="button" class="btn btn-danger btn-sm delete-user-btn" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    @if($search)
                                        Aucun utilisateur ne correspond à « {{ $search }} ».
                                    @else
                                        Aucun utilisateur enregistré pour le moment.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
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

<!-- Modal Créer -->
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-success" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvel utilisateur</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createUserForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="role_kind" id="create_role_kind" value="user">
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Nom</span>
                        </div>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Email</span>
                        </div>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Mot de passe</span>
                        </div>
                        <input type="password" class="form-control" name="password" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Confirmer</span>
                        </div>
                        <input type="password" class="form-control" name="password_confirmation" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rôle</span>
                        </div>
                        <select class="form-control" name="role" id="create_role">
                            <option value="user" data-kind="user">User</option>
                            <option value="user" data-kind="maire_adjoint">Adjoint au Maire</option>
                            <option value="admin" data-kind="admin">Admin</option>
                            <option value="fatou" data-kind="fatou">Cabinet de Maire</option>
                        </select>
                    </div>
                    <div class="input-group" id="create_service_group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Service</span>
                        </div>
                        <select class="form-control" name="service" id="create_service">
                            <option value="">Aucun</option>
                            @foreach($services as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-muted mb-0" id="create_adjoint_note" style="display:none; font-size:0.85em;">
                        Ce compte partagera la file commune "Adjoint au Maire" (comme tout autre Adjoint).
                    </p>
                    <div id="create_admin_permissions_group" style="display:none;">
                        <hr>
                        <p class="text-muted mb-2" style="font-size:0.85em;">Droits admin supplémentaires (décochez pour restreindre ce compte, ex: Accueil) :</p>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_manage_users" id="create_can_manage_users" value="1">
                            <label class="form-check-label" for="create_can_manage_users">Peut gérer "Les Utilisateurs" (voir/modifier/supprimer des comptes)</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_access_cabinet" id="create_can_access_cabinet" value="1">
                            <label class="form-check-label" for="create_can_access_cabinet">Accès aux pages Cabinet de Maire</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_access_all_services" id="create_can_access_all_services" value="1">
                            <label class="form-check-label" for="create_can_access_all_services">Voit les demandes de tous les services (Suivi du Circuit / Notifications)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-success" title="Créer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
                </div>
            </form>
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
                <input type="hidden" name="role_kind" id="edit_role_kind" value="user">
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Nom</span>
                        </div>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Email</span>
                        </div>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Rôle</span>
                        </div>
                        <select class="form-control" name="role" id="edit_role">
                            <option value="user" data-kind="user">User</option>
                            <option value="user" data-kind="maire_adjoint">Adjoint au Maire</option>
                            <option value="admin" data-kind="admin">Admin</option>
                            <option value="fatou" data-kind="fatou">Cabinet de Maire</option>
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
                    <p class="text-muted mb-0" id="edit_adjoint_note" style="display:none; font-size:0.85em;">
                        Ce compte partagera la file commune "Adjoint au Maire" (comme tout autre Adjoint).
                    </p>
                    <div id="edit_admin_permissions_group" style="display:none;">
                        <hr>
                        <p class="text-muted mb-2" style="font-size:0.85em;">Droits admin supplémentaires (décochez pour restreindre ce compte, ex: Accueil) :</p>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_manage_users" id="edit_can_manage_users" value="1">
                            <label class="form-check-label" for="edit_can_manage_users">Peut gérer "Les Utilisateurs" (voir/modifier/supprimer des comptes)</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_access_cabinet" id="edit_can_access_cabinet" value="1">
                            <label class="form-check-label" for="edit_can_access_cabinet">Accès aux pages Cabinet de Maire</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="can_access_all_services" id="edit_can_access_all_services" value="1">
                            <label class="form-check-label" for="edit_can_access_all_services">Voit les demandes de tous les services (Suivi du Circuit / Notifications)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-success" title="Enregistrer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Réinitialiser mot de passe -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Réinitialiser le mot de passe de <span id="reset_password_name"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Nouveau mot de passe</span>
                        </div>
                        <input type="password" class="form-control" name="password" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Confirmer</span>
                        </div>
                        <input type="password" class="form-control" name="password_confirmation" minlength="8" required autocomplete="new-password">
                    </div>
                    <small class="text-muted">L'utilisateur devra utiliser ce mot de passe à sa prochaine connexion. Communiquez-le lui directement.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-success" title="Réinitialiser"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
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
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Annuler"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-danger" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></button>
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
var MAIRE_ADJOINT_LABEL = @json(\App\Models\User::MAIRE_ADJOINT_LABEL);

$('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var role = button.data('role');
    var service = button.data('service');
    var kind = role === 'user' && service === MAIRE_ADJOINT_LABEL ? 'maire_adjoint' : role;

    $('#editUserForm').attr('action', '{{ url('/utilisateurs') }}/' + id);
    $('#edit_name').val(button.data('name'));
    $('#edit_email').val(button.data('email'));
    $('#edit_role option[data-kind="' + kind + '"]').prop('selected', true);
    $('#edit_can_manage_users').prop('checked', button.data('can-manage-users') == 1);
    $('#edit_can_access_cabinet').prop('checked', button.data('can-access-cabinet') == 1);
    $('#edit_can_access_all_services').prop('checked', button.data('can-access-all-services') == 1);

    var isLastAdmin = (role === 'admin' && {{ $adminCount }} <= 1);
    $('#edit_role option[value="user"]').prop('disabled', isLastAdmin);

    toggleServiceField(kind, 'edit');
    if (kind === 'user') {
        $('#edit_service').val(service);
    }
});

$('#edit_role').on('change', function () {
    var kind = this.options[this.selectedIndex].dataset.kind;
    toggleServiceField(kind, 'edit');
});

$('#create_role').on('change', function () {
    var kind = this.options[this.selectedIndex].dataset.kind;
    toggleServiceField(kind, 'create');
});

function toggleServiceField(kind, prefix) {
    document.getElementById(prefix + '_role_kind').value = kind === 'maire_adjoint' ? 'maire_adjoint' : 'user';

    // "Adjoint au Maire" partage une file commune (comme Cabinet) : aucun nom
    // à choisir, donc pas de champ Service du tout pour ce choix de rôle.
    $('#' + prefix + '_service_group').toggle(kind === 'user');
    document.getElementById(prefix + '_adjoint_note').style.display = kind === 'maire_adjoint' ? '' : 'none';
    if (kind !== 'user') {
        $('#' + prefix + '_service').val('');
    }

    $('#' + prefix + '_admin_permissions_group').toggle(kind === 'admin');
    if (kind !== 'admin') {
        $('#' + prefix + '_can_manage_users').prop('checked', false);
        $('#' + prefix + '_can_access_cabinet').prop('checked', false);
        $('#' + prefix + '_can_access_all_services').prop('checked', false);
    }
}

$('#createUserModal').on('hidden.bs.modal', function () {
    var form = this.querySelector('form');
    if (form) { form.reset(); }
    toggleServiceField('user', 'create');
});

toggleServiceField('user', 'create');

$('#resetPasswordModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#resetPasswordForm').attr('action', '{{ url('/utilisateurs') }}/' + id + '/mot-de-passe');
    $('#reset_password_name').text(button.data('name'));
    $('#resetPasswordForm')[0].reset();
});

$('#deleteUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#deleteUserForm').attr('action', '{{ url('/utilisateurs') }}/' + id);
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
