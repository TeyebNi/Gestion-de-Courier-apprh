
@extends('layouts.admin')
  
@section('content')
<div class="card-footer">
<a href="{{ route('exportpdf') }}" class="btn btn-primary">Export PDF</a>
      </div>
<div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">DataTable </h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="example2" class="table table-bordered table-hover">
                <thead>
                <tr>
                <td>id</td>
                <th>Name</th>
 <th>Email</th>
 <th>Username</th>
 <th>Code</th>
 <th>Image</th>
 <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($admin as $d)
 <tr>
 <td>{{$d->id}}</td>
 <td>{{$d->name}}</td>
 <td>{{$d->email}}</td>
 <td>{{$d->username}}</td>
 <td>{{$d->com_code}}</td>
 <td>
    <img src="{{asset('uploads/'.$d->photo)}}" width="70px" height="70px" alt="img">
</td>
<td><a href="" type="button" class="btn btn-success"> Show</a> 
  
  </td>
 
 </tr>
 @endforeach
 </tbody>
 

 </thead>
 
</table>


@endsection


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

