<?php
/**
 * Corrige le filtrage des Affectations : un utilisateur sans service
 * mais avec can_affectation doit voir TOUTES les affectations (comme
 * un admin), pas seulement celles avec service=NULL.
 * A executer depuis la racine du projet Laravel :
 *   php apply_fix_affectation_scope.php
 */

$file = 'app/Http/Controllers/AffectationController.php';

$old = <<<'OLDSCOPE'
        if (!auth()->user()->isAdmin()) {
            $query->where('sevice', auth()->user()->service);
        }
OLDSCOPE;

$new = <<<'NEWSCOPE'
        if (!auth()->user()->isAdmin() && !empty(auth()->user()->service)) {
            $query->where('sevice', auth()->user()->service);
        }
NEWSCOPE;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}

$content = file_get_contents($file);
$count = substr_count($content, $old);

if ($count === 0) {
    echo "[ATTENTION] Aucune occurrence trouvee (deja modifie ?) dans $file\n";
    exit(1);
}

if ($count !== 2) {
    echo "[ATTENTION] $count occurrence(s) trouvee(s) au lieu de 2 attendues - verifiez manuellement avant de continuer.\n";
    echo "Aucune modification appliquee par securite.\n";
    exit(1);
}

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "[OK] $file : $count occurrence(s) remplacee(s) (index() et exportExcel())\n";
