<?php
/**
 * Aligne le bouton Excel a droite, sur la meme ligne que le bouton "Nouveau/Nouvelle".
 * A executer depuis la racine du projet Laravel : php apply_excel_button_alignment.php
 */

$oldBlock = <<<'BLOCKOLD'
<!-- BEGIN EXCEL BUTTON POSITION -->
<style>
    /*
     * نفس السطر لزر الإضافة وأيقونة Excel
     */
    .page-actions-aligned {
        width: 100%;
        display: flex !important;
        align-items: center !important;
        gap: 10px;
        min-height: 42px;
    }

    /*
     * دفع أيقونة Excel إلى أقصى اليمين
     */
    .page-actions-aligned .excel-export-toolbar {
        margin: 0 0 0 auto !important;
        padding: 0 !important;
        width: auto !important;
        display: flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
    }

    .page-actions-aligned .excel-export-button {
        margin: 0 !important;
        float: none !important;
        position: static !important;
    }

    /*
     * منع وجود مساحة كبيرة بين الأزرار والجدول
     */
    .excel-export-toolbar {
        margin-top: 0 !important;
        margin-bottom: 12px !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toolbar = document.querySelector('.excel-export-toolbar');

    if (!toolbar) {
        return;
    }

    const excelButton = toolbar.querySelector(
        '.excel-export-button, a, button'
    );

    if (!excelButton) {
        return;
    }

    /*
     * البحث عن زر الإضافة الموجود أعلى كل صفحة:
     * Nouvelle Demande
     * Nouvelle Affectation
     * Nouvelle Orientation
     * Nouveau Type de Demande
     */
    const buttons = Array.from(
        document.querySelectorAll('a, button')
    );

    const actionButton = buttons.find(function (element) {
        if (element === excelButton) {
            return false;
        }

        const text = String(
            element.textContent || ''
        ).trim().toLowerCase();

        return (
            text.includes('nouveau') ||
            text.includes('nouvelle') ||
            text.includes('ajouter')
        );
    });

    if (!actionButton) {
        /*
         * في الصفحات التي لا تحتوي على زر Ajouter،
         * وضع Excel في أعلى اليمين داخل البطاقة.
         */
        const card =
            toolbar.closest('.card-body') ||
            toolbar.closest('.card') ||
            document.querySelector('.card-body') ||
            document.querySelector('.card');

        if (card) {
            card.style.position = 'relative';
            toolbar.style.display = 'flex';
            toolbar.style.justifyContent = 'flex-end';
            toolbar.style.marginTop = '0';
        }

        return;
    }

    /*
     * استعمال الحاوية الأصلية التي يوجد فيها زر Nouveau/Nouvelle،
     * حتى يبقى النص الموجود بجانبه في نفس السطر.
     */
    const actionContainer = actionButton.parentElement;

    if (!actionContainer) {
        return;
    }

    actionContainer.classList.add('page-actions-aligned');

    /*
     * نقل شريط Excel إلى نفس حاوية زر الإضافة.
     */
    actionContainer.appendChild(toolbar);

    toolbar.style.display = 'flex';
    toolbar.style.marginLeft = 'auto';
});
</script>
<!-- END EXCEL BUTTON POSITION -->
BLOCKOLD;

$newBlock = <<<'BLOCKNEW'
<!-- BEGIN EXCEL BUTTON POSITION -->
<style>
    /*
     * Le titre, le bouton "Nouveau/Nouvelle" et le bouton Excel
     * sont sur la meme ligne. Le bouton Excel est pousse a l'extreme
     * droite grace a margin-left:auto (flexbox).
     */
    .page-actions-aligned {
        width: 100%;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap;
        gap: 10px;
    }

    .page-actions-aligned .excel-export-button {
        margin-left: auto !important;
    }
</style>
<!-- END EXCEL BUTTON POSITION -->
BLOCKNEW;

$blockFiles = [
    'resources/views/affectation/index.blade.php',
    'resources/views/depot/index.blade.php',
    'resources/views/orientation/index.blade.php',
    'resources/views/typedem/index.blade.php',
    'resources/views/users/index.blade.php',
];

$headerEdits = [
    [
        'file' => 'resources/views/affectation/index.blade.php',
        'search' => <<<'HEDIT0OLD'
                <p class="category">
                    Gestion des Affectations
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouvelle Affectation</button>
                    @endif
                    <a href="{{ route('affectation.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT0OLD,
        'replace' => <<<'HEDIT0NEW'
                <p class="category page-actions-aligned">
                    <span>Gestion des Affectations</span>
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouvelle Affectation</button>
                    @endif
                    <a href="{{ route('affectation.export') }}" class="btn btn-success btn-sm excel-export-button" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT0NEW,
    ],
    [
        'file' => 'resources/views/depot/index.blade.php',
        'search' => <<<'HEDIT1OLD'
               <p class="category">
                <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouvelle Demande</button>
                 <a href="{{ route('depot.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT1OLD,
        'replace' => <<<'HEDIT1NEW'
               <p class="category page-actions-aligned">
                <button class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Nouvelle Demande</button>
                 <a href="{{ route('depot.export') }}" class="btn btn-success btn-sm excel-export-button" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT1NEW,
    ],
    [
        'file' => 'resources/views/orientation/index.blade.php',
        'search' => <<<'HEDIT2OLD'
                <p class="category">
                    Gestion des Orientations
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouvelle Orientation</button>
                    <a href="{{ route('orientation.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT2OLD,
        'replace' => <<<'HEDIT2NEW'
                <p class="category page-actions-aligned">
                    <span>Gestion des Orientations</span>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouvelle Orientation</button>
                    <a href="{{ route('orientation.export') }}" class="btn btn-success btn-sm excel-export-button" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT2NEW,
    ],
    [
        'file' => 'resources/views/typedem/index.blade.php',
        'search' => <<<'HEDIT3OLD'
                <p class="category">
                    Gestion des Types de Demande
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouveau Type de Demande</button>
                    <a href="{{ route('typedem.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT3OLD,
        'replace' => <<<'HEDIT3NEW'
                <p class="category page-actions-aligned">
                    <span>Gestion des Types de Demande</span>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModal">Nouveau Type de Demande</button>
                    <a href="{{ route('typedem.export') }}" class="btn btn-success btn-sm excel-export-button" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT3NEW,
    ],
    [
        'file' => 'resources/views/users/index.blade.php',
        'search' => <<<'HEDIT4OLD'
                <p class="category">
                    Gestion des comptes utilisateurs
                    <a href="{{ route('users.export') }}" class="btn btn-success btn-sm" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT4OLD,
        'replace' => <<<'HEDIT4NEW'
                <p class="category page-actions-aligned">
                    <span>Gestion des comptes utilisateurs</span>
                    <a href="{{ route('users.export') }}" class="btn btn-success btn-sm excel-export-button" title="Exporter en Excel"><i class="fas fa-file-excel"></i></a>
                </p>
HEDIT4NEW,
    ],
];

$ok = 0; $fail = 0;

foreach ($blockFiles as $path) {
    if (!file_exists($path)) { echo "[SKIP] Fichier introuvable : $path\n"; $fail++; continue; }
    $content = file_get_contents($path);
    if (strpos($content, $oldBlock) === false) {
        echo "[ATTENTION] Bloc CSS/JS non trouve (deja modifie ?) dans $path\n";
        $fail++;
        continue;
    }
    file_put_contents($path, str_replace($oldBlock, $newBlock, $content));
    echo "[OK] $path (bloc CSS/JS simplifie)\n";
    $ok++;
}

foreach ($headerEdits as $e) {
    $path = $e['file'];
    if (!file_exists($path)) { echo "[SKIP] Fichier introuvable : $path\n"; $fail++; continue; }
    $content = file_get_contents($path);
    if (strpos($content, $e['search']) === false) {
        echo "[ATTENTION] En-tete non trouve (deja modifie ?) dans $path\n";
        $fail++;
        continue;
    }
    file_put_contents($path, str_replace($e['search'], $e['replace'], $content));
    echo "[OK] $path (en-tete aligne)\n";
    $ok++;
}

echo "\nTermine : $ok extraits appliques, $fail non appliques.\n";
