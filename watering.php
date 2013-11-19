<?php
    include ("resources.php");
    //figure dis out this is format for DBDBDBDBDBDBDBDB
   //this is all select stuff which is probably more of a snapshot thing

   /*
    Fertilizer is 8x more concentrated
    
    so 1ml of fertilizer = 8ml of water?

    start = 100 and target = 150:
    
    Weight of pot + plant = 500g
    Weight of plant = 100g (or whatever biomass calc here)

    Actual weight of pot = 400g (snapshot.real_before) or previous w/e
    Target weight = 600g
    Target – actual pot = 200g = 200ml

    In a 2-step process (?):
    12, 14 are probably important pumps

    but, the script kevin sent uses all the pumps/
     
    1.      water to target weight 425g (pump 2, fertilizer) pump2a001?
    2.      water to target weight 600g (pump 1, water) pump1a001?
    */

    /*
         formula taken from bash shell script
         if SW in [501..600] -> TV=50"
         if SW in [601..700] -> TV=60"
         if SW in [701..800] -> TV=80" 
         if SW in [801..900] -> TV=100"    
    */

    // Note, Absolute means, water x amount
    // TargetWeight means water until x weight.
    if ( $_POST['OrigWeight'] == ""){
        echo "invalid variable";
        exit;
    }
    if ( ! is_numeric($_POST['OrigWeight'])){
        echo "not an integer";
        exit;
    }
    $sw = ($_POST['OrigWeight']);
    if ( $sw > 800 && $sw <= 900){
        $tv = 100;
    } 
    else if($sw > 700){
        $tv = 80;
    }
    else if($sw > 600){
        $tv = 60;
    }
    else if($sw > 500){
        $tv = 50;
    } else{
        $tv = 0;
    }
    echo "start " . $sw . " target" . $tv;
    //       printf "%s;%s;0;24;%i;TargetWeight;0;Skip;0;5,7,9,12,14\n", snapshot(sampleid?irrelevant?), datetime, target; \
?>
