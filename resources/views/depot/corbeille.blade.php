@extends('layouts.master')

@section('title')
Corbeille - Dépôt des Demandes
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Corbeille — Demandes supprimées
                </h4>
                <a href="{{ route('depot.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour au Dépôt
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Les demandes supprimées restent ici et peuvent être restaurées. Seule une demande encore à l'accueil
                    peut être supprimée depuis "Gestion des Demandes".
                </p>

                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Objet</th>
                            <th>Type</th>
                            <th>Supprimée le</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                            @forelse($tabdepot as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->nom ?: ($item->origine_detail ?: '—') }}</td>
                                <td>{{ $item->objet ?: '—' }}</td>
                                <td>{{ $item->typdm ?: '—' }}</td>
                                <td>{{ $item->deleted_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    <form action="{{ route('depot.restore', $item->id) }}" method="post" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" title="Restaurer">
                                            <i class="fas fa-undo"></i> Restaurer
                                        </button>
                                    </form>
                                    @if(auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-danger btn-sm force-delete-btn"
                                        data-id="{{ $item->id }}" data-nom="{{ $item->nom ?: ($item->origine_detail ?: 'cette demande') }}"
                                        data-toggle="modal" data-target="#forceDeleteModal" title="Supprimer définitivement">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">La corbeille est vide.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $tabdepot->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<div class="modal fade" id="forceDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-lg modal-right modal-danger" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supprimer définitivement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="forceDeleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Voulez-vous vraiment supprimer <strong id="force_delete_nom"></strong> définitivement ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if(auth()->user()->isAdmin())
<script>
$('#forceDeleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var id = button.data('id');
    $('#forceDeleteForm').attr('action', '{{ url('/depot-corbeille') }}/' + id);
    $('#force_delete_nom').text(button.data('nom'));
});
</script>
@endif
@endsection
