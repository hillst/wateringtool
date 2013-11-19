<?php
    include_once("resources.php");
    
    if (!$test) {
      echo "An error occurred.\n";
      exit;
    }

    $result = pg_query($test, "SELECT * FROM snapshot LIMIT 10;");
    if (!$result) {
      echo "An error occurred.\n";
      exit;
    }

    while ($row = pg_fetch_row($result)) {
        for ($i = 0; $i < sizeof($row); $i++){
            echo "$row[$i]  ";
        }
      echo "<br />\n";
    }
?>
