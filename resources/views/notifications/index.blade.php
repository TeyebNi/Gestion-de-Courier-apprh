@extends('layouts.master')

@section('title')
Notifications
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <p class="category">
                    Notifications @if($userService) — Service : <strong>{{ $userService }}</strong> @else <span class="text-danger">(aucun service ne vous est assigné, contactez un administrateur)</span> @endif
                </p>
            </div>
            <div class="card-body">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Service</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                            @forelse($notifications as $n)
                            <tr style="{{ $n->is_read ? '' : 'font-weight:bold;' }}">
                                <td><span class="badge badge-primary">{{ $n->service }}</span></td>
                                <td>{{ $n->message }}</td>
                                <td>{{ $n->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($n->is_read)
                                        <span class="badge badge-secondary">Lue</span>
                                    @else
                                        <span class="badge badge-warning">Nouvelle</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($n->iddmd && is_numeric($n->iddmd) && in_array((int) $n->iddmd, $validDemandeIds))
                                    <a href="{{ route('circuit.historique', $n->iddmd) }}" class="btn btn-secondary btn-sm" title="Voir la demande">
                                        <i class="fas fa-eye"></i> Voir la demande
                                    </a>
                                    @endif
                                    @if(!$n->is_read)
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-info btn-sm" title="Marquer lue"><i class="fa fa-check"></i> Marquer lue</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune notification pour le moment.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $notifications->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
