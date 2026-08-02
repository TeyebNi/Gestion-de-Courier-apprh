<?php
/**
 * Corrige les 2 verifications d'acces obsoletes dans AffectationController.php
 * (elles ne connaissaient pas can_affectation, seulement admin/service).
 * A executer depuis la racine du projet Laravel :
 *   php apply_fix_affectation_controller_check.php
 */

$file = 'app/Http/Controllers/AffectationController.php';

$old = <<<'OLDCHECK'
        if (!auth()->user()->isAdmin() && empty(auth()->user()->service)) {
            abort(403, "Cette page est réservée aux administrateurs et aux utilisateurs rattachés à un service.");
        }
OLDCHECK;

$new = <<<'NEWCHECK'
        if (!auth()->user()->canAccessAffectation()) {
            abort(403, "Cette page est réservée aux administrateurs et aux utilisateurs ayant accès au module Affectation.");
        }
NEWCHECK;

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

$content = str_replace($old, $new, $content);
file_put_contents($file, $content);
echo "[OK] $file : $count occurrence(s) remplacee(s)\n";
