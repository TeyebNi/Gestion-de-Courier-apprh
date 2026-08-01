<?php
/**
 * Retire le lien de pied de page 'Courier' (reliquat du theme,
 * pointait vers creative-tim.com).
 * A executer depuis la racine du projet Laravel :
 *   php apply_remove_courier_footer_link.php
 */

$file = 'resources/views/layouts/master.blade.php';

$old = <<<'OLDLINK'
                            <li>
                                <a href="https://www.creative-tim.com">
                                    Courier
                                </a>
                            </li>
                            <li>
                                <a href="http://presentation.creative-tim.com">
                                    
                                </a>
                            </li>
OLDLINK;

$new = <<<'NEWLINK'
                            <li>
                                <a href="http://presentation.creative-tim.com">
                                    
                                </a>
                            </li>
NEWLINK;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}
$content = file_get_contents($file);
if (strpos($content, $old) === false) {
    echo "[ATTENTION] Lien 'Courier' non trouve (deja modifie ?) dans $file\n";
    exit(1);
}
file_put_contents($file, str_replace($old, $new, $content));
echo "[OK] $file (lien Courier retire)\n";
