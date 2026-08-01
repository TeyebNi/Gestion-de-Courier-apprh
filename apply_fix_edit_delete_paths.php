<?php
/**
 * Corrige les formulaires Modifier/Supprimer casses quand l'appli
 * est servie dans un sous-dossier (ex: XAMPP /apprh).
 * A executer depuis la racine du projet Laravel : php apply_fix_edit_delete_paths.php
 */

$edits = [
    [
        'file' => 'resources/views/depot/index.blade.php',
        'search' => <<<'FEDIT0OLD'
$('#editDepotForm').attr('action', '/depot/' + id);
FEDIT0OLD,
        'replace' => <<<'FEDIT0NEW'
$('#editDepotForm').attr('action', '{{ url('depot') }}/' + id);
FEDIT0NEW,
    ],
    [
        'file' => 'resources/views/depot/index.blade.php',
        'search' => <<<'FEDIT1OLD'
$('#deleteDepotForm').attr('action', '/depot/' + id);
FEDIT1OLD,
        'replace' => <<<'FEDIT1NEW'
$('#deleteDepotForm').attr('action', '{{ url('depot') }}/' + id);
FEDIT1NEW,
    ],
    [
        'file' => 'resources/views/users/index.blade.php',
        'search' => <<<'FEDIT2OLD'
$('#editUserForm').attr('action', '/utilisateurs/' + id);
FEDIT2OLD,
        'replace' => <<<'FEDIT2NEW'
$('#editUserForm').attr('action', '{{ url('utilisateurs') }}/' + id);
FEDIT2NEW,
    ],
    [
        'file' => 'resources/views/users/index.blade.php',
        'search' => <<<'FEDIT3OLD'
$('#deleteUserForm').attr('action', '/utilisateurs/' + id);
FEDIT3OLD,
        'replace' => <<<'FEDIT3NEW'
$('#deleteUserForm').attr('action', '{{ url('utilisateurs') }}/' + id);
FEDIT3NEW,
    ],
    [
        'file' => 'resources/views/affectation/index.blade.php',
        'search' => <<<'FEDIT4OLD'
$('#editAffectationForm').attr('action', '/affectation/' + id);
FEDIT4OLD,
        'replace' => <<<'FEDIT4NEW'
$('#editAffectationForm').attr('action', '{{ url('affectation') }}/' + id);
FEDIT4NEW,
    ],
    [
        'file' => 'resources/views/affectation/index.blade.php',
        'search' => <<<'FEDIT5OLD'
$('#deleteAffectationForm').attr('action', '/affectation/' + id);
FEDIT5OLD,
        'replace' => <<<'FEDIT5NEW'
$('#deleteAffectationForm').attr('action', '{{ url('affectation') }}/' + id);
FEDIT5NEW,
    ],
    [
        'file' => 'resources/views/typedem/index.blade.php',
        'search' => <<<'FEDIT6OLD'
$('#editTypedemForm').attr('action', '/typedem/' + id);
FEDIT6OLD,
        'replace' => <<<'FEDIT6NEW'
$('#editTypedemForm').attr('action', '{{ url('typedem') }}/' + id);
FEDIT6NEW,
    ],
    [
        'file' => 'resources/views/typedem/index.blade.php',
        'search' => <<<'FEDIT7OLD'
$('#deleteTypedemForm').attr('action', '/typedem/' + id);
FEDIT7OLD,
        'replace' => <<<'FEDIT7NEW'
$('#deleteTypedemForm').attr('action', '{{ url('typedem') }}/' + id);
FEDIT7NEW,
    ],
    [
        'file' => 'resources/views/orientation/index.blade.php',
        'search' => <<<'FEDIT8OLD'
$('#editOrientationForm').attr('action', '/orientation/' + id);
FEDIT8OLD,
        'replace' => <<<'FEDIT8NEW'
$('#editOrientationForm').attr('action', '{{ url('orientation') }}/' + id);
FEDIT8NEW,
    ],
    [
        'file' => 'resources/views/orientation/index.blade.php',
        'search' => <<<'FEDIT9OLD'
$('#deleteOrientationForm').attr('action', '/orientation/' + id);
FEDIT9OLD,
        'replace' => <<<'FEDIT9NEW'
$('#deleteOrientationForm').attr('action', '{{ url('orientation') }}/' + id);
FEDIT9NEW,
    ],
];

$ok = 0; $fail = 0;
foreach ($edits as $e) {
    $path = $e['file'];
    if (!file_exists($path)) { echo "[SKIP] Fichier introuvable : $path\n"; $fail++; continue; }
    $content = file_get_contents($path);
    if (strpos($content, $e['search']) === false) {
        echo "[ATTENTION] Motif non trouve (deja modifie ?) dans $path\n";
        $fail++;
        continue;
    }
    file_put_contents($path, str_replace($e['search'], $e['replace'], $content));
    echo "[OK] $path\n";
    $ok++;
}
echo "\nTermine : $ok extraits appliques, $fail non appliques.\n";
