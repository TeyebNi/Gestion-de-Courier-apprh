@extends('layouts.master')

@section('title')
Journal des comptes
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h4 class="card-title mb-0" style="font-weight: 700; color: #212529;">
                    Journal des comptes
                </h4>
                <div class="d-flex align-items-center flex-wrap">
                    @include('partials.search-box', ['route' => 'users.audit-log', 'placeholder' => 'Rechercher par acteur, compte, détails...', 'minWidth' => 300])
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour aux utilisateurs
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Date</th>
                            <th>Action</th>
                            <th>Compte concerné</th>
                            <th>Effectué par</th>
                            <th>Détails</th>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td><span class="badge {{ $log->actionBadgeClass() }}">{{ $log->actionLabel() }}</span></td>
                                <td>{{ $log->target_name }}</td>
                                <td>{{ $log->actor_name }}</td>
                                <td class="text-truncate" style="max-width:320px;" title="{{ $log->details }}">{{ $log->details ?: '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucune action enregistrée pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
