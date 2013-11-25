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
	
	$Util = new WateringUtil($StartDate, $EndDate, $fcapacity_wet_weight);
	
	$result = pg_query($system, $cars_query);
	
	if (!$result) {
		header("HTTP/1.1 400");
		echo "An error getting all cars has occured.\n";
		exit;
	}
	
	$all_cars = pg_fetch_all($result); //carid identcode
    //need to select specific cars probably
	$Util->buildAssocCars($all_cars);
	
	$Util->buildSnapQuery();
	
	$result = pg_query($test, $Util->snap_query);
	if (!$result) {
		header("HTTP/1.1 400");
		echo "An error occurred getting all the most recent snapshots.\n";
		exit;
	}
	$snapshots = pg_fetch_all($result);

	$counter = 0;
	foreach ($snapshots as $snapshot) {
		if (!$EVERYDAY ) {
			$date = $StartDate;
				
			$snapquery1 = $Util->buildSnapshotVals($snapshot, $date, $pumps);
			$result = pg_query($system, $snapquery1);
			if (!$result) {
				echo "An error occurred.\n";
				echo pg_result_error($result) . "\n";
				die();
			}
			$result = pg_query($system, $snapquery1);
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

?>
