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
		//exit;
	}
	$StartDate = $_POST["StartDate"];
	$EndDate = $_POST["EndDate"];
	$fcapacity_wet_weight = $_POST['FWet'];
	
	$Util = new WateringUtil($StartDate, $EndDate, $fcapacity_wet_weight);
	
	//get all cars
	$result = pg_query($system, $Util->cars_query);
	
	if (!$result) {
		header("HTTP/1.1 400");
		echo "An error getting all cars has occured.\n";
		exit;
	}
	
	$all_cars = pg_fetch_all($result); //carid identcode

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
				
			$snapquery1 = $Util->buildSnapshotVals($snapshot, $date, $Util->pump1);
			$snapquery2 = $Util->buildSnapshotVals($snapshot, $date, $Util->pump2);
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
