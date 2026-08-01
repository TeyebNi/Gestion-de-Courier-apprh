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
                </h4>
                <a href="{{ route('users.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
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
                                       data-email="{{ $u->email }}"
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
                            <span class="input-group-text">Email</span>
                        </div>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
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
                    <button type="button" class="btn btn-warning" data-dismiss="modal" title="Fermer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    <button type="submit" class="btn btn-success" title="Enregistrer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></button>
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
$('#editUserModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    var role = button.data('role');

    $('#editUserForm').attr('action', '{{ url('/utilisateurs') }}/' + id);
    $('#edit_name').val(button.data('name'));
    $('#edit_email').val(button.data('email'));
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


<!-- BEGIN EXCEL BUTTON POSITION -->
<style>
    /*
     * نفس السطر لزر الإضافة وأيقونة Excel
     */
    .page-actions-aligned {
        width: 100%;
        display: flex !important;
        align-items: center !important;
        gap: 10px;
        min-height: 42px;
    }

    /*
     * دفع أيقونة Excel إلى أقصى اليمين
     */
    .page-actions-aligned .excel-export-toolbar {
        margin: 0 0 0 auto !important;
        padding: 0 !important;
        width: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
    }

    .page-actions-aligned .excel-export-button {
        margin: 0 !important;
        float: none !important;
        position: static !important;
    }

    /*
     * منع وجود مساحة كبيرة بين الأزرار والجدول
     */
    .excel-export-toolbar {
        margin-top: 0 !important;
        margin-bottom: 12px !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toolbar = document.querySelector('.excel-export-toolbar');

    if (!toolbar) {
        return;
    }

    const excelButton = toolbar.querySelector(
        '.excel-export-button, a, button'
    );

    if (!excelButton) {
        return;
    }

    /*
     * البحث عن زر الإضافة الموجود أعلى كل صفحة:
     * Nouvelle Demande
     * Nouvelle Affectation
     * Nouvelle Orientation
     * Nouveau Type de Demande
     */
    const buttons = Array.from(
        document.querySelectorAll('a, button')
    );

    const actionButton = buttons.find(function (element) {
        if (element === excelButton) {
            return false;
        }

        const text = String(
            element.textContent || ''
        ).trim().toLowerCase();

        return (
            text.includes('nouveau') ||
            text.includes('nouvelle') ||
            text.includes('ajouter')
        );
    });

    if (!actionButton) {
        /*
         * في الصفحات التي لا تحتوي على زر Ajouter،
         * وضع Excel في أعلى اليمين داخل البطاقة.
         */
        const card =
            toolbar.closest('.card-body') ||
            toolbar.closest('.card') ||
            document.querySelector('.card-body') ||
            document.querySelector('.card');

        if (card) {
            card.style.position = 'relative';
            toolbar.style.display = 'flex';
            toolbar.style.justifyContent = 'flex-end';
            toolbar.style.marginTop = '0';
        }

        return;
    }

    /*
     * استعمال الحاوية الأصلية التي يوجد فيها زر Nouveau/Nouvelle،
     * حتى يبقى النص الموجود بجانبه في نفس السطر.
     */
    const actionContainer = actionButton.parentElement;

    if (!actionContainer) {
        return;
    }

    actionContainer.classList.add('page-actions-aligned');

    /*
     * نقل شريط Excel إلى نفس حاوية زر الإضافة.
     */
    actionContainer.appendChild(toolbar);

    toolbar.style.display = 'flex';
    toolbar.style.marginLeft = 'auto';
});
</script>
<!-- END EXCEL BUTTON POSITION -->

