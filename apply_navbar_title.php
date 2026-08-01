<?php
/**
 * Agrandit et fonce le titre 'Gestion de Courier' dans la barre de navigation.
 * A executer depuis la racine du projet Laravel :
 *   php apply_navbar_title.php
 */

$file = 'resources/views/layouts/master.blade.php';

$old = <<<'OLDNAV'
<a class="navbar-brand" href="{{ route('dashboard') }}">Gestion de Courier</a>
OLDNAV;

$new = <<<'NEWNAV'
<a class="navbar-brand" href="{{ route('dashboard') }}" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50;">Gestion de Courier</a>
NEWNAV;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}
$content = file_get_contents($file);
if (strpos($content, $old) === false) {
    echo "[ATTENTION] Titre navbar non trouve (deja modifie ?) dans $file\n";
    exit(1);
}
file_put_contents($file, str_replace($old, $new, $content));
echo "[OK] $file (titre navbar agrandi/fonce)\n";
