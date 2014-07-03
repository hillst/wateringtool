<?php 
	  include("resources.php");
	  include("WateringUtil.php");
?>
<!DOCTYPE HTML>
<html>
<head>
    <title> 
        Automatic watering utility
    </title>
    <!--  load first to avoid conflicts -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resources/js/bootstrap-3.0.0.min.js"></script>
    <script type="text/javascript" src="/resources/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet" type="text/css"  href="/resources/css/jquery-ui-1.10.3.custom.min.css">
    <script type="text/javascript" src="/resources/js/jquery-ui-timepicker-addon.js"></script>
    <link rel="stylesheet" type="text/css" href="/resources/css/bootstrap-3.0.0.min.css">
    <link rel="stylesheet" type="text/css" href="/resources/css/watering.css">
    <script type="text/javascript" src="/resources/js/jquery.fastLiveFilter.js"></script>
    <script type="text/javascript" src="/resources/js/angular.min.js"></script>
    <script type="text/javascript" src="app.js"></script>
    <link href="/resources/images/favicon.ico" rel="icon" type="image/x-icon" />   	
</head>
<body>
<div ng-app="wateringResources">
  <nav class="navbar navbar-default" role="navigation">
  <!-- Brand and toggle get grouped for better mobile display -->
  <div class="navbar-header">
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </button>
    </div>
    <div class="container">
        <a class="navbar-brand" href="#">Auto Watering Scheduler</a>
    </div>
  
  </nav>
    <div class="container">
        <div class="jumbotron" style="font-size: 14px">
            <h1>Calculate watering schedule</h1>
            <h3 class="alert alert-warning" >SUBMISSIONS ARE CURRENTLY DISABLED</h3>
            <p>
            	This tool schedules jobs in the WATERING TABLE based on several parameters. These parameters are, field capacity wet weight, car barcode, last measured weight, average car weight,
            	biomass, a set of dates, and a set of pumps. It will submit a watering job for each of these, starting with the selected starting date, and ending (but not including)
            	the end date.
            	
            	<ol>
            		<li>Select field capacity wet weight</li>
            		<li>Choose the starting date (BEGINS on this day)</li>
            		<li>Choose the ending date (exclusive)</li>
            		<li>Select the pumps to execute the job</li>
            		<li>Create a filter of cars to include</li>
            		<li>Submit the job!</li>
            	</ol>
            </p>
            <form id = "form" role="form" class="select-data">
                <div class="form-group">
                    <label for="FWet">Wet Weight at Field Capacity</label>
                    <input type="text" class="form-control" id="orig" name="FWet"/>
                    <label for="FDry">Dry Weight</label>
                    <input type="text" class="form-control" id="orig" name="FDry"/>
                    <label for="pTarget">Target (% of field capacity)</label>
                    <input type="text" class="form-control" id="orig" name="pTarget"/>
                    <h3>Measurement Label</h3>
                   <?php 
                    	foreach(getAllPumps($system) as $pump){ 
							echo '<div class="checkbox">'
							. '  <label>'
							. $pump["pumpname"] . "<input type='checkbox' name='pumps[]' value ='". $pump["id"]. "'>"
							."</label>
							</div>";      
					 }
					 ?>
					<div class="checkbox" >
						<label>
							Check All <input id="selectall" type="checkbox">
						</label>
					</div>
                    <label for="StartDate">Starting Date:</label>
                    <input type="text" class="form-control" name="StartDate" id="starting"/>           
                    <label for="EndDate">Ending Date (exclusive):</label>
                    <input type="text" class="form-control" name="EndDate" id="ending"/>           
                </div>
                <div>
                    <h5>Apply cart fileters (displayed carts will be scheduled for watering)</h5>
                	<div class="form-group filterform" action="#">
					    <label for="FilterInput">Contains cart filter</label>
		       			<input type="text" class="filterinput form-control" placeholder="Filter carts..." ng-model="query.identcode">
		   		 	</div>
                    <div class="form-group filterform" action="#">
                        <label for="FilterInput">Begins-with filter</label>
                        <input type="text" class="filterinput form-control" placeholder="Filter carts..." ng-model="query2">
                    </div>
		    		<div id="searcher" ng-controller="cartListCtrl as carts">
                        <div ng-repeat="cart in carts.carts | filter: beginsWith(query2) | filter: query"> 
                            <div class="ngrow">{{ cart.identcode }} </div>
                        </div>
                	</div>
                </div>
                <div class="result hidden"></div>
                <button type="submit" id="submit" class="btn btn-default btn-block btn-large" style="background-color: darkred; color: white;">Submit</button>
             </form>
            <br/>
			
        </div>
         
    </div>
</div>
</body>
<script type="text/javascript">
$(document).ready(function(){
    $('#starting').datetimepicker();
    $('#ending').datetimepicker();
    $('#selectall').click(function(){
		$(":checkbox").prop('checked', this.checked);
    });
    $("#submit").click(function(event){
        var formlist = $("#form").serializeArray();
        var OrigWeight = $("#orig").val();
        var NewWeight = calculateTarget(OrigWeight);
        var datas = $("#form").serialize() + "&cars=" + $(".ngrow").filter(":visible").text()
                                                                                      .trim().split(" ").join("\t");
        str = "You submitted: \n\n";
        $.each( formlist, function(){ str+= this.name + ": " + this.value + "\n";  });
        str += "Computed watering amount: " + NewWeight + "\n\n";;       
        str += "Is this correct?\n";
        if (confirm( str  )){
            $.ajax({
                type: "POST",
                url: 'watering.php',
                data: datas,
                success: function(data){
                	$('.result').removeClass('hidden alert alert-danger alert-warning');
                    $('.result').addClass("alert alert-success");
                    $('.result').text(data);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    $('.result').addClass("hidden alert alert-success");                  
                    $('.result').addClass('alert alert alert-danger alert-warning');
                    $('.result').text("Error!");
                   
                }
            }); 
        }
        event.preventDefault();
            
    });
    function calculateTarget(OrigWeight){
        //put function here
        var NewWeight = OrigWeight;
        return NewWeight;
    } 
        
});
</script>
</html>
