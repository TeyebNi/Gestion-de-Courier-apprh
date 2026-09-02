{{-- Badge Interne/Externe réutilisable, avec le détail (service ou institution) en sous-texte. $demande doit être défini par la vue appelante. --}}
@if($demande->origine === 'interne')
    <span class="badge badge-info">Interne</span>
    @if($demande->origine_detail)
        <br><small class="text-muted">{{ $demande->origine_detail }}</small>
    @endif
@elseif($demande->origine === 'externe')
    <span class="badge badge-secondary">Externe</span>
    @if($demande->origine_detail)
        <br><small class="text-muted">{{ $demande->origine_detail }}</small>
    @endif
@else
    <span class="text-muted">—</span>
@endif
