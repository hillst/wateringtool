<?php
	include("WateringUtil.php");
	include("resources.php");
	
	$EVERYDAY = False;
	if ($_POST['FWet'] == "") {
		header("HTTP/1.1 400");
		echo "FWet not set";
		exit;
	}
	if (!is_numeric($_POST['FWet'])) {
		header("HTTP/1.1 400");
		echo "Field Capacity Wet Weight is not an Integer.";
		exit;
	}
    if ($_POST["FDry"] == "") {
        header("HTTP/1.1 400");
        echo "FDry not set";
        exit;
    }
	if ($_POST["StartDate"] == "") {
		header("HTTP/1.1 400");
		echo "Start Date not set";
		exit;
	}
	if ($_POST["EndDate"] == "") {
		header("HTTP/1.1 400");
		echo "End Date not set";
		exit;
	}
	
	if($_POST["pumps"] == ""){
		header("HTTP/1.1 400");
		echo "No pumps selected";
		exit;
	} 
    
    if($_POST["pTarget"] == ""){
        echo "Empty target, using 100%";
        $percentTarget = 1.;
    } else{
        $percentTarget  = $_POST["pTarget"];
    }
	$pumps = implode(',', $_POST["pumps"]);
	if($_POST["cars"] == ""){
		echo "empty cars, using all";
		$cars_query = $Util->cars_query;
	} else{
		$cars = explode("\t", $_POST["cars"]);
		$cars_query = "SELECT plants.carid, plants.identcode FROM public.plants WHERE plants.active = true AND plants.on_system = true AND (";
		foreach($cars as $car ){
            if($car != ""){
				$cars_query .= " plants.identcode = '". $car . "' or";
			}
		}
		$cars_query = substr($cars_query, 0, -2);
		$cars_query .= " )";
	}
	$StartDate = $_POST["StartDate"];
	$EndDate = $_POST["EndDate"];
	$fcapacity_wet_weight = $_POST['FWet'];
	$dry_weight = $_POST["FDry"];
	$Util = new WateringUtil($StartDate, $EndDate, $fcapacity_wet_weight);
	
	$result = pg_query($system, $cars_query);
	
	if (!$result) {
		header("HTTP/1.1 400");
		echo "An error getting all cars has occured.\n";
		exit;
	}
	//cars/plants
    $all_cars = pg_fetch_all($result); 
	if ($all_cars == ""){
        header("HTTP/1.1 200");
        exit;
    }
    $Util->buildAssocCars($all_cars);
	$Util->buildSnapQuery();
    //get associated snapshots
    if (!$result) {
		header("HTTP/1.1 400");
		echo "An error occurred getting all the most recent snapshots.\n";
		exit;
	}
	$snapshots = pg_fetch_all($result);
    $counter = 0;
    foreach ($all_cars as $car){
        $date = $StartDate;
        $amount = $Util->CalculateWatering($fcapacity_wet_weight, $dry_weight, $percentTarget);
        $insertquery = $Util->buildInsertQuery($car["carid"], $date, $pumps, $amount);
        echo $insertquery;
        continue;
        //$result = pg_query($system, $insertquery);
        if (!$result){
            header("HTTP/1.1 400");
            echo "An error occured.";
            echo pg_result_error($result);
            exit;
        }
    }
//this function encapsulates the old procedure of getting the current weight of each car from the database.
function allSnapshots(){
	foreach ($snapshots as $snapshot) {
		if (!$EVERYDAY ) {
			$date = $StartDate;
	        //builds our insertion query using the prev_weight field and some other info
			$snapquery = $Util->buildSnapshotVals($snapshot, $date, $pumps);
	        echo $snapquery;
     		$result = pg_query($system, $snapquery);
			if (!$result) {
				echo "An error occurred.\n";
				echo pg_result_error($result) . "\n";
				die();
			}
			$counter += 2;
		} else {
			//if we are setting for every day until completion just use the DateTime iterator.
		}
	}
	echo "Executed " . $counter . " insertions.";
}
?>
