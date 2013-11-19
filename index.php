<!DOCTYPE HTML>
<html>
<head>
    <title> 
        Automatic watering utility
    </title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resources/js/bootstrap-3.0.0.min.js"></script>
    <script type="text/javascript" src="/resources/js/jquery-ui-1.10.3.custom.min.js"></script>
    <link rel="stylesheet" type="text/css"  href="/resources/css/jquery-ui-1.10.3.custom.min.css"></script>
    <script type="text/javascript" src="/resources/js/jquery-ui-timepicker-addon.js"></script>
    <link rel="stylesheet" type="text/css" href="/resources/css/bootstrap-3.0.0.min.css">
</head>
<body>
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
  
  </div><!-- /.navbar-collapse -->
  </nav>
    <div class="container">
        <div class="jumbotron">
            <h1>Calculate watering schedule</h1>
            some small list of snapshots
            <div style="height: 200px"></div>
            <form id = "form" role="form" class="select-data">
                <div class="form-group">
                    <label for="OrigWeight">Current Weight:</label>
                    <input type="text" class="form-control" id="orig" name="OrigWeight"/>
                    <label for="MeasLabel">Measurement Label:</label>
                    <select name = "MeasLabel" class="form-control">
                        <option value="lable1">lable 1</option>
                        <option value="label2">label 2</option>
                        <option value="label2">label 2</option>
                    </select>
                    <label for="StartDate">Starting:</label>
                    <input type="text" class="form-control" name="StartDate" id="starting"/>           
                </div>
                <div class="result hidden"></div>
                <button type="submit" id="submit" class="btn btn-default btn-block btn-large">Submit</button>
             </form>
        </div>
    </div>
</body>
<script type="text/javascript">
$(document).ready(function(){
    $('#starting').datetimepicker();
    $("#submit").click(function(event){
        var formlist = $("#form").serializeArray();
        var OrigWeight = $("#orig").val();
        var NewWeight = calculateTarget(OrigWeight);
        str = "You submitted: \n\n";
        $.each( formlist, function(){ str+= this.name + ": " + this.value + "\n";  });
        str += "Computed watering amount: " + NewWeight + "\n\n";;       
        str += "Is this correct?\n";
        if (confirm( str  )){
            $.ajax({
                type: "POST",
                url: 'watering.php',
                data: $('#form').serialize(),
                success: function(data){
                    console.log("sup " + data);
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
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
