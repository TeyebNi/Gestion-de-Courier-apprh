@if($serviceLabels->isNotEmpty())
<div class="row">
    <div class="col-md-12">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Charge de travail</h5>
                <h4 class="card-title">Demandes par Service</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($maireAdjointLabels->isNotEmpty() || $conseillerLabels->isNotEmpty())
<div class="row">
    @if($maireAdjointLabels->isNotEmpty())
    <div class="col-md-6">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Charge de travail</h5>
                <h4 class="card-title">Demandes par Adjoint au Maire</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="maireAdjointChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($conseillerLabels->isNotEmpty())
    <div class="col-md-6">
        <div class="card card-chart">
            <div class="card-header">
                <h5 class="card-category">Charge de travail</h5>
                <h4 class="card-title">Demandes par Conseiller</h4>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="conseillerChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
