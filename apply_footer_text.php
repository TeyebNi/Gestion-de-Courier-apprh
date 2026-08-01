<?php
/**
 * Remplace le texte du footer (attribution du theme) par
 * 'Commune de Tevragh Zeina'.
 * A executer depuis la racine du projet Laravel :
 *   php apply_footer_text.php
 */

$file = 'resources/views/layouts/master.blade.php';

$old = <<<'OLDFOOTER'
                    <div class="copyright">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script>, Designed by
                        <a href="https://www.invisionapp.com" target="_blank">Invision</a>. Coded by
                        <a href="https://www.creative-tim.com" target="_blank">Creative Tim</a>.
                    </div>
OLDFOOTER;

$new = <<<'NEWFOOTER'
                    <div class="copyright">
                        &copy;
                        <script>
                            document.write(new Date().getFullYear())
                        </script>
                        Commune de Tevragh Zeina
                    </div>
NEWFOOTER;

if (!file_exists($file)) {
    echo "[SKIP] Fichier introuvable : $file\n";
    exit(1);
}
$content = file_get_contents($file);
if (strpos($content, $old) === false) {
    echo "[ATTENTION] Footer non trouve (deja modifie ?) dans $file\n";
    exit(1);
}
file_put_contents($file, str_replace($old, $new, $content));
echo "[OK] $file (footer remplace)\n";
