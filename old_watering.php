<?php
    include ("resources.php");
    //figure dis out this is format for DBDBDBDBDBDBDBDB
   //this is all select stuff which is probably more of a snapshot thing
    // TargetWeight means water until x weight.
    if ( $_POST['FWet'] == ""){
        header("HTTP/1.1 400");
        echo "FWet not set";
        exit;
    }
    if ( ! is_numeric($_POST['FWet'])){
        header("HTTP/1.1 400");
        echo "Field Capacity Wet Weight is not an Integer.";
        exit;
    }
    if ( $_POST["StartDate"] == ""){
        header("HTTP/1.1 400");
        echo "Start Date not set";
        exit;
    } 
    if ( $_POST["EndDate"] == ""){
        header("HTTP/1.1 400");
        echo "End Date not set";
        exit;
    }
    $StartDate = $_POST["StartDate"];
    $EndDate = $_POST["EndDate"];
    $fcapacity_wet_weight = $_POST['FWet'];
    
    // get all cars on system, carid and identcode
    $cars = "
            SELECT 
                plants.carid, 
                plants.identcode
            FROM 
                public.plants
            WHERE 
                plants.active = true AND plants.on_system = true";
    //optionally get a subset of all

    //get most recent weight value for each
    $result = pg_query($system, $cars);
    if (!$result) {
        header("HTTP/1.1 400");
        echo "An error occurred.\n";
        exit;
    }
    $all_cars = pg_fetch_all($result); //carid identcode
    $assoc_cars = array();
    foreach($all_cars as $car){
        $assoc_cars[$car["carid"]] = $car["identcode"];
    }
    $our_cars = "";
    foreach($all_cars as $car){
        $our_cars .= " car_tag = '" . $car["carid"] . "' or";
    }
    $our_cars = substr($our_cars, 0, -2);
    $snap_query = "
            SELECT DISTINCT ON (car_tag)
                   time_stamp, weight_before, car_tag
            FROM   snapshot
            WHERE 
             ". $our_cars ." 
            ORDER BY
            car_tag, time_stamp DESC, time_stamp";
    $result = pg_query($test, $snap_query);
    if (!$result) {
        header("HTTP/1.1 400");
        echo pg_last_error($test);
        echo "An error occurred.\n";
        exit;
    }
    $snapshots = pg_fetch_all($result); //carid identcode 
    
    //oid   identcode   time   starthour endhour quantity blankidk type blankagain done status watered processed deficiency deflimit creator created pumpconfig commentary   
   //42498;"DAAA000693";"2013-09-25 00:00:00-05";0;24;40;"";"Absolute";"''";FALSE;"Waiting";0;"1999-01-01 00:00:00-06";"Skip";-1;"LTAdmin";"2013-09-24 15:32:37.593-05";"5";""
    $testvals = "'DAAA000693','2013-09-25 00:00:00-05',0,24,40,'Absolute',FALSE,'Waiting',0,'1999-01-01 00:00:00-06','Skip',-1,'LTAdmin','2013-09-24 15:32:37.593-05','5', ''";

    $notcomplete = "'1999-01-01 00:00:00-06'";
    $pump1 = "'5,7,9,12,14'";
    $pump2 = "'6,8,10,13,15'"; 
    $query = "INSERT INTO watering  ( identcode, time, starthour, finishhour, quantity, type, done, status, watered, processed, deficency, deflimit, creator, created, pumpconfigids, \"FormulaTW\" ) VALUES ";
    $testquery = $query . "( " . $testvals . " )";
    $interval = new DateInterval('P1D');
    $period = new DatePeriod(new DateTime($StartDate), $interval, new DateTime($EndDate) );
    foreach ( $period as $dt ){
        echo $dt->format( "Y-m-d H:i:sO" ) . "\n";
    }
    echo "rip\n";
    $fd = fopen("/scratch/massquery","w");
    foreach($snapshots as $snapshot){
        $calculated_amount = CalculateWatering($fcapacity_wet_weight, $snapshot["weight_before"]); 
        $car_insert = $query . "( '".$assoc_cars[$snapshot["car_tag"]]."', '$StartDate',0,24,$calculated_amount,'TargetWeight',FALSE,'Waiting',0,$notcomplete,'Skip',-1,'LTAdmin','". date("Y-m-d H:i:sO")."',$pump1,'' )";
        $car_insert2 = $query . "( '".$assoc_cars[$snapshot["car_tag"]]."', '$StartDate',0,24,$calculated_amount,'TargetWeight',FALSE,'Waiting',0,$notcomplete,'Skip',-1,'LTAdmin','". date("Y-m-d H:i:sO")."',$pump2,'' )";
        fwrite($fd, $car_insert1 ."\n");
        fwrite($fd, $car_insert2 . "\n");
    }
    fclose($fd);
    
    $result = pg_query($system, $testquery);
    if (!$result) {
        echo "An error occurred.\n";
        echo $testquery;
        echo pg_result_error($result) . "\n";
        die();
    } 
    echo "completed query";
    //       printf "%s;%s;0;24;%i;TargetWeight;0;Skip;0;5,7,9,12,14\n", snapshot(sampleid?irrelevant?), datetime, target; \
function CalculateWatering($fcapacity_wet_weight, $prev_weight, $biomass=0){
    $avg_car_weight = 400; //static value that is unknown right now
    $biomass = 0; //future option
    $tot_watering_amount = $fcapacity_wet_weight + $avg_car_weight - $prev_weight - $biomass;
    return $tot_watering_amount;

}
?>
