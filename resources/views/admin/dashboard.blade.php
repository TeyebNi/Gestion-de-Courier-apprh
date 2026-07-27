@extends('layouts.master')

@section('title')
Tableau de bord
@endsection

@section('content')
<div class="panel-header panel-header-lg" style="margin: -30px -15px 20px -15px; border-radius: 0;">
    <canvas id="bigDashboardChart"></canvas>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Ventes Globales</h5>
                <h4 class="card-title">Produits Expédiés</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="lineChartExample"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons arrows-1_refresh-69"></i> Mis à jour à l'instant
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Ventes 2018</h5>
                <h4 class="card-title">Tous les produits</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="lineChartExampleWithNumbersAndGrid"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons arrows-1_refresh-69"></i> Mis à jour à l'instant
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Statistiques Email</h5>
                <h4 class="card-title">Performance des dernières 24 heures</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="barChartSimpleGradientsNumbers"></canvas>
                </div>
            </div>
            <div class="card-footer">
                <div class="stats">
                    <i class="now-ui-icons ui-2_time-alarm"></i> Les 7 derniers jours
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="card card-tasks">
      <div class="card-header">
        <h5 class="card-category">Développement Backend</h5>
        <h4 class="card-title">Tâches</h4>
      </div>
      <div class="card-body">
        <div class="table-full-width table-responsive">
          <table class="table">
            <tbody>
                <tr>
                    <td>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" checked="">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </td>
                    <td class="text-left">Signer le contrat pour "What are conference organizers afraid of?"</td>
                    <td class="td-actions text-right">
                        <button type="button" rel="tooltip" title="" class="btn btn-info btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Modifier la tâche">
                            <i class="now-ui-icons ui-2_settings-90"></i>
                        </button>
                        <button type="button" rel="tooltip" title="" class="btn btn-danger btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Supprimer">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </td>
                    <td class="text-left">Lignes de la grande littérature russe ? Ou emails de mon patron ?</td>
                    <td class="td-actions text-right">
                        <button type="button" rel="tooltip" title="" class="btn btn-info btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Modifier la tâche">
                            <i class="now-ui-icons ui-2_settings-90"></i>
                        </button>
                        <button type="button" rel="tooltip" title="" class="btn btn-danger btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Supprimer">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" checked="">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </td>
                    <td class="text-left">Inondations : un an après, bilan des pertes et des retrouvailles suite aux pluies torrentielles à Detroit</td>
                    <td class="td-actions text-right">
                        <button type="button" rel="tooltip" title="" class="btn btn-info btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Modifier la tâche">
                            <i class="now-ui-icons ui-2_settings-90"></i>
                        </button>
                        <button type="button" rel="tooltip" title="" class="btn btn-danger btn-round btn-icon btn-icon-mini btn-neutral" data-original-title="Supprimer">
                            <i class="now-ui-icons ui-1_simple-remove"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <hr>
        <div class="stats">
          <i class="now-ui-icons loader_refresh spin"></i> Mis à jour il y a 3 minutes
        </div>
      </div>
  </div>
  </div>
  <div class="col-md-6">
      <div class="card">
        <div class="card-header">
            <h5 class="card-category">Liste de toutes les personnes</h5>
            <h4 class="card-title">Statistiques des employés</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
              <table class="table">
                <thead class=" text-primary">
                  <th>Nom</th>
                  <th>Pays</th>
                  <th>Ville</th>
                  <th class="text-right">Salaire</th>
                </thead>
                <tbody>
                  <tr>
                    <td>Dakota Rice</td>
                    <td>Niger</td>
                    <td>Oud-Turnhout</td>
                    <td class="text-right">36 738 €</td>
                  </tr>
                  <tr>
                    <td>Minerva Hooper</td>
                    <td>Curaçao</td>
                    <td>Sinaai-Waas</td>
                    <td class="text-right">23 789 €</td>
                  </tr>
                  <tr>
                    <td>Sage Rodriguez</td>
                    <td>Pays-Bas</td>
                    <td>Baileux</td>
                    <td class="text-right">56 142 €</td>
                  </tr>
                  <tr>
                    <td>Doris Greene</td>
                    <td>Malawi</td>
                    <td>Feldkirchen in Kärnten</td>
                    <td class="text-right">63 542 €</td>
                  </tr>
                  <tr>
                    <td>Mason Porter</td>
                    <td>Chili</td>
                    <td>Gloucester</td>
                    <td class="text-right">78 615 €</td>
                  </tr>
                </tbody>
              </table>
            </div>
        </div>
      </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
<script src="../assets/js/plugins/chartjs.min.js"></script>
<script src="../assets/demo/demo.js"></script>
<script>
    $(document).ready(function() {
        demo.initDashboardPageCharts();
    });
</script>
@endsection
