@extends('layouts.master')

@section('title')
Notifications
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <p class="category">Notifications par service</p>
                <form method="GET" action="{{ route('notifications.index') }}" class="mt-2">
                    <select name="service" class="form-control" style="max-width:250px;" onchange="this.form.submit()">
                        <option value="">Tous les services</option>
                        @foreach($services as $s)
                            <option value="{{ $s }}" {{ request('service') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </form>
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
                                <td>{{ $n->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($n->is_read)
                                        <span class="badge badge-secondary">Lue</span>
                                    @else
                                        <span class="badge badge-warning">Nouvelle</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if(!$n->is_read)
                                    <form method="POST" action="{{ route('notifications.read', $n->id) }}" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-info btn-sm">Marquer comme lue</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">Aucune notification pour le moment.</td></tr>
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
