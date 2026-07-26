
<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<div class="container">
    <div class="row">
        <div class="col-xs-12">
			
			<div class="invoice-title">
    			<h3>Republique Islamique de Mauritanie</h3>
    		</div>
    		<hr>
    		<div class="row">
				
    			<div class="col-xs-6  text-left">
					
    				<address>
    				<strong>بلدية تفرغ زينة  </strong>  
					<br> 
					Commune de Tevragh Zeina<br>
    					<br>
    					<br>
    					
    					
    				</address>
    			</div>
				<img  class="avatar border-gray text-center" src="{{asset('assets/img//logoTVZ.jpg')}}" alt="..."> 
    			<div class="col-xs-6 text-right">
    				<address>
        			<strong></strong><br>
    					<br>
    					<br>
    					<br>
    					
    					
    				</address>
    			</div>
    		</div>
    		<div class="row">
    			<div class="col-xs-6">
    				<address>
    					<strong>Reçu N°: {{$detailf->id}}</strong><br>
    					<br>
    					
    				</address>
    			</div>
    			<div class="col-xs-6 text-right">
    				<address>
    					<strong></strong><br>
    					<br><br>
    				</address>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row">
    	<div class="col-md-12">
    		<div class="panel panel-default">
    			<div class="panel-heading">
    				<h3 class="panel-title"><strong>Information</strong></h3>
    			</div>
    			<div class="panel-body">
    				<div class="table-responsive">
    					<table class="table table-condensed">
    						<thead>
                                <tr>
        							<td><strong>Type de Demande</strong></td>
        							<td class="text-center"><strong>Nom</strong></td>
									<td class="text-center"><strong>NNI</strong></td>
									<td class="text-center"><strong>Tel</strong></td>
									<td class="text-center"><strong>Adresse</strong></td>
        							<td class="text-center"><strong>Date</strong></td>
        							
                                </tr>
    						</thead>
    						<tbody>
    							<!-- foreach ($order->lineItems as $line) or some such thing here -->
                               
    							@foreach($detailfac as $detail)
                                <tr>
    								<td>{{$detail->typdm}}
								</td>
    								<td class="text-center">{{$detail->nom}}</td>
									<td class="text-center">{{$detail->nni}}</td>
									<td class="text-center">{{$detail->tel}}</td>
									<td class="text-center">{{$detail->adresse}}</td>
    								<td class="text-center">{{$detail->daterecp}}</td>
    							</tr>
                                @endforeach
                                
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    
</div>

<script>
    window.print();
</script>