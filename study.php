<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Study</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
  <style type="text/css"> b { color: #f5ebae; } table.links { border-spacing: 1em 0em; } </style>
</head>
<body>
    <table><tr><td class="headerLinks"><a href="add.php">Add new subject</a></td></tr></table>
    <h3>List of Subjects in Study</h3>
    <?php
        require 'connect.php';
        $indexsubjid = $_GET['startindex'] ? $_GET['startindex'] :  1; //usubjid by default is 1, otherwise grab from url
        $prevstart = $_GET['prevind'] ? $_GET['prevind'] : 1;
        //last used usubjid MUST be the greatest (i.e., last) one
        $endindex = $redis->get("$me:$studyid:last_usubjid");
        $starterindex = $indexsubjid; //save this val for later
        echo "<p><ul>"; //place all subject info in a p tag
        //this loop gets all current subjects
        //only displaying 10 subjects
        $counter = 0; //loop counter var
        $maxcounter = 10;
        while ($counter < $maxcounter && $indexsubjid <= $endindex) {
          $k = "$me:DM:$studyid:$indexsubjid";
          if ($redis->exists($k)) {
            echo "<li><a href=subject.php?sub=$indexsubjid><b>Subject ID:</b> $indexsubjid</a></li>\n";
            $counter++;
          }
          $indexsubjid++;
        }
        echo "</ul></p>";
        $nextindex = $indexsubjid;
        $howfar = $endindex - $nextindex;
        //if starter index is at 1, we do not need to display prev button (bc there is no prev)
        if ($starterindex == 1) {
          echo "<table style='border-spacing: 2.75em 0em;'><tr><td></td><td><a href=study.php?startindex=$nextindex&prevind=$starterindex>Next</a></td></tr></table>";
        }
        else if ($howfar < 10 && $nextindex != $endindex+1) {
          echo "<table class='links'><tr><td><a href=study.php?startindex=$prevstart>Previous</a></td><td><a href=study.php?startindex=$nextindex&prevind=$starterindex>Next</a></tr></table>";
        }
        //if none of the above, we must be at the end, so only display prev button
        else {
          echo "<table><tr><td><a href=study.php?startindex=$prevstart>Previous</a></td></tr></table>";
        }
        $redis->close();
    ?>
</body>
</html>