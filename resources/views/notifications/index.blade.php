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
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary">
                            <th>Service</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Réponse au demandeur</th>
                            <th class="text-right">Action</th>
                        </thead>
                        <tbody>
                            @forelse($notifications as $n)
                            <tr style="{{ $n->is_read ? '' : 'font-weight:bold;' }}">
                                <td><span class="badge badge-primary">{{ $n->service }}</span></td>
                                <td>{{ $n->message }}</td>
                                <td>{{ $n->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($n->is_read)
                                        <span class="badge badge-secondary">Lue</span>
                                    @else
                                        <span class="badge badge-warning">Nouvelle</span>
                                    @endif
                                </td>
                                <td>
                                    @if($n->response === 'accepted')
                                        <span class="badge badge-success">Acceptée (SMS envoyé)</span>
                                    @elseif($n->response === 'rejected')
                                        <span class="badge badge-danger">Refusée (SMS envoyé)</span>
                                    @else
                                        <span class="text-muted">En attente</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(!$n->is_read)
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-info btn-sm">Marquer lue</button>
                                    </form>
                                    @endif

                                    @if(!$n->response)
                                    <form method="POST" action="{{ route('notifications.respond', $n->id) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="response" value="accepted">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Envoyer un SMS d\'acceptation au demandeur ?')">Accepter</button>
                                    </form>
                                    <form method="POST" action="{{ route('notifications.respond', $n->id) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="response" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Envoyer un SMS de refus au demandeur ?')">Refuser</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">Aucune notification pour le moment.</td></tr>
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
