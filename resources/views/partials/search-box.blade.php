{{--
    Barre de recherche réutilisable : soumet en GET vers $route avec le
    paramètre "search", et affiche un bouton de réinitialisation quand
    une recherche est active. $search doit être défini par la vue appelante.
--}}
<form method="GET" action="{{ route($route) }}" class="form-inline mr-2">
    <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ $placeholder }}" value="{{ $search }}" style="min-width:260px;">
    <button type="submit" class="btn btn-primary btn-sm ml-2">Rechercher</button>
    @if($search)
    <a href="{{ route($route) }}" class="btn btn-outline-secondary btn-sm ml-2" title="Réinitialiser la recherche">&times;</a>
    @endif
</form>
