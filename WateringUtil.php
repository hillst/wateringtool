<?php
/**
 * This class is simply an organizational tool.
 * @author shill
 *
 */
Class WateringUtil{

	public $cars_query = "SELECT plants.carid, plants.identcode FROM public.plants WHERE plants.active = true AND plants.on_system = true";
	public $our_cars = "";
	public $assoc_cars = array();
	public $snap_query = "";
	public $notcomplete = "'1999-01-01 00:00:00-06'";
	
	//field just for testing
	
	// identcode   time   starthour endhour quantity blankidk type blankagain done status watered processed deficiency deflimit creator created pumpconfig commentary	
	private $testvals = "'DAAA000693','2013-09-25 00:00:00-05',0,24,40,'Absolute',FALSE,'Waiting',0,'1999-01-01 00:00:00-06','Skip',-1,'LTAdmin','2013-09-24 15:32:37.593-05','5', ''";
	public $pump1 = "'5,7,9,12,14'";
	public $pump2 = "'6,8,10,13,15'";
	private $water_insert_query = "INSERT INTO watering  ( identcode, time, starthour, finishhour, quantity, type, done, status, watered, processed, deficency, deflimit, creator, created, pumpconfigids, \"FormulaTW\" ) VALUES ";
	public $StartDate;
	public $EndDate;
	public $interval;
	public $period;
	public $fcapacity_wet_weight;
	
	
	function __construct($StartDate, $EndDate, $fcapacity_wet_weight){
		
		$this->StartDate = $StartDate;
		$this->EndDate = $EndDate;		
		$this->interval =  new DateInterval('P1D');
		$this->period = new DatePeriod(new DateTime($StartDate), $this->interval, new DateTime($EndDate) );
		$this->fcapacity_wet_weight = $fcapacity_wet_weight;
		
	}
	/**
	 * sets our cars and assoc cars fields
	 * @param unknown_type $all_cars
	 */
	function buildAssocCars($all_cars){
		foreach($all_cars as $car){
			$this->assoc_cars[$car["carid"]] = $car["identcode"];
		}
		foreach($all_cars as $car){
			$this->our_cars .= " car_tag = '" . $car["carid"] . "' or";
		}
		$this->our_cars = substr($this->our_cars, 0, -2);
		
	}
    //Gets cars	
	function buildSnapQuery(){
        if ($this->our_cars == ""){				
			throw new Exception("our cars is not intialized");
		}
		$this->snap_query = "SELECT DISTINCT ON (car_tag) time_stamp, weight_before, car_tag FROM snapshot WHERE ". $this->our_cars ." ORDER BY car_tag, time_stamp DESC, time_stamp";
	}
	
	function buildWaterInsertQuery($values){
		$testquery = $this->water_insert_query . "( " . $values . " )";
	}
	
    function buildSnapshotVals($snapshot, $date, $pump){
		$calculated_amount = $this->CalculateWatering($this->fcapacity_wet_weight);
		$car_insert = $this->water_insert_query . "( '".$this->assoc_cars[$snapshot["car_tag"]]."', '$date',0,24,$calculated_amount,'TargetWeight',FALSE,'Waiting',0,$this->notcomplete,'Skip',-1,'LTAdmin','". date("Y-m-d H:i:sO")."','$pump','' )";
		return $car_insert; //:( need to return both
	}

    //insertutility that takes less setup and pushes more of the work to the other script
    function buildInsertQuery($cartag, $date, $pump, $amount){
        $car_insert = $this->water_insert_query . "( '". $cartag ."', '$date',0,24,$amount,'TargetWeight',FALSE,'Waiting',0,$this->notcomplete,'Skip',-1,'WebTool','". date("Y-m-d H:i:sO")."','$pump','' )";
        return $car_insert; 
    }
	//targetPercent should be greater than zero and less than one.
    // Field capacity = [(wet weight @ fc) – (dry weight)] + [(avg weight of carrier) + (dry weight)]
    // $percentField capacity = $percient * field capacity
    function CalculateWatering($fcapacity_wet_weight, $dry_weight, $targetPercent=1, $biomass=0){
		$field_capacity = ($fcapacity_wet_weight - $dry_weight) * $targetPercent + $avg_car_weight + $dry_weight;
		return $field_capacity * $targetPercent ;
	
	}
}
/**
 * returns all of the cars in a 2d list, 0 => (carid => 1, identcode => MSWplant)
 */
function getAllCars($db){
	$query = "SELECT plants.carid, plants.identcode FROM public.plants WHERE plants.active = true AND plants.on_system = true";
	$result = pg_query($db, $query);
	if (!$result) {
		echo "An error getting all cars has occured.\n";
		return;
	}
	return pg_fetch_all($result); //carid identcode
}
/**
 * returns a 2d list, 0 =>(pumpname=>BARCODE, id=>14)
 */
function getAllPumps($db){
	$query = "SELECT pumpname, id from pumps where state != 1";
	$result = pg_query($db, $query);
	if (! $result ){
		echo "An error fetching pumps has occured.\n";
		return;
	}
	return pg_fetch_all($result);
}
