<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Auteur : {{ $author }}</p>
    <p>Ceci est un exemple simple d’export PDF avec Laravel.</p>
</body>
</html>


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

