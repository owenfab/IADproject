<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View/Add Subject Visits</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
</head>
<body>
    <?php
        ini_set('display_errors', 1);
        require 'connect.php';
        $currentID = $_GET['subID'];
        echo "<table><tr><td class=headerLinks><a href=study.php>Subject List</a></td>";
        echo "<td class=headerLinks><a href=subject.php?sub=$currentID>Subject Info</a></td></tr></table>";
        echo "<h3>View Subject Visits</h3>";
        echo "<h4>Visits for subject ID $currentID:</h4>";
        //the following is the maximum possible value the visitnum could be for this ID
        $maxvisitnum = $redis->hGet("$me:DM:$studyid:$currentID", 'last_visitnum');
        $indexvisitnum = 1; //if there are any visits, the first visitnum will be 1
        echo "<p><ul>";
        while ($indexvisitnum <= $maxvisitnum) {
          //set key
          $k = "$me:SV:$studyid:$currentID:$indexvisitnum";
          if ($redis->exists($k)) {
            //if this key exists, print out its info
            $startdate = $redis->hGet($k, 'svstdtc');
            $enddate = $redis->hGet($k, 'svendtc');
            $visitcomment = $redis->hGet($k, 'visit');
            echo "<li><b>Visit Number:</b> $indexvisitnum<ul><li><b>Start:</b> $startdate</li><li><b>End:</b> $enddate</li><li><b>Comment:</b> $visitcomment</li></ul></li>";
          }
          $indexvisitnum = $indexvisitnum + 1;
        }
        echo "</ul></p>";
        if (isset($_POST["submit"])) {
          //get everything from input fields
          $sDay = $_POST["sday"]; //start day of visit
          $sMonth = $_POST["smonth"]; //start month of visit
          $sYr = $_POST["syr"]; //start year of visit
          $sHr = $_POST["shour"]; //start hour of visit
          $sMin = $_POST["smin"]; //start min of visit
          $eDay = $_POST["eday"]; //end day of visit
          $eMonth = $_POST["emonth"]; //end month of visit
          $eYr = $_POST["eyr"]; //end year of visit
          $eHr = $_POST["ehour"]; //end hour of visit
          $eMin = $_POST["emin"]; //end min of visit
          $val = $_POST["comment"]; //visit comment
          $pat = "/[^0-9]/";
          if (preg_match($pat, $sDay) == 1 || preg_match($pat, $sMonth) == 1 || preg_match($pat, $sYr) == 1 || pred_match($pat, $eDay) == 1 || preg_match($pat, $eMonth) == 1 || preg_match($pat, $eYr) == 1 || preg_match($pat, $sHr) == 1 || preg_match($pat, $sMin) == 1 || preg_match($pat, $eHr) == 1 || preg_match($pat, $eMin) == 1) {
            echo '<script type="text/javascript">alert("Please ensure your inputs are correct (e.g., dates cannot contain letters)");</script>';
          }
          else {
            //get dates into standard format
            $startDate = new DateTime(date("$sYr-$sMonth-$sDay $sHr:$sMin:s"));
            $trueSdate = $startDate->format(DateTime::ATOM);
            $endDate = new DateTime(date("$eYr-$eMonth-$eDay $eHr:$eMin:s"));
            $trueEdate = $endDate->format(DateTime::ATOM);
            //stick values into associative array
            $svSubmit = array(
              'svstdtc' => $trueSdate,
              'svendtc' => $trueEdate,
              'visit' => $val
            );
            //get the new last_visitnum (last_num++)
            $key = "$me:DM:$studyid:$currentID";
            $last_visitnum = $redis->hIncrBy($key, 'last_visitnum', 1);
            $use_key = "$me:SV:$studyid:$currentID:$last_visitnum";
            $redis->hMset($use_key, $svSubmit); //submit key val pair
          }
          $redis->close();
        }
    ?>
    <h3>Add a new visit for this subject:</h3>
    <table>
    <form method="post" action="">
        <tr><td>Start Date:</td><td><input type="text" name="smonth" placeholder="MM"/>
        <input type="text" name="sday" placeholder="DD"/>
        <input type="text" name="syr" placeholder="YYYY"/></td></tr>
        <tr><td>Start Time:</td><td><input type="text" name="shour" placeholder="HH"/>
        <input type="text" name="smin" placeholder="MM"/></td></tr>
        <tr><td>End Date:</td><td><input type="text" name="emonth" placeholder="MM"/>
        <input type="text" name="eday" placeholder="DD"/>
        <input type="text" name="eyr" placeholder="YYYY"/></td></tr>
        <tr><td>End Time:</td><td><input type="text" name="ehour" placeholder="HH"/>
        <input type="text" name="emin" placeholder="MM"/></td></tr>
        <tr><td>Comment:</td><td><input type="text" name="comment"/></td></tr></table>
        <input type="submit" value="Submit" name="submit"/><br />
    </form>
</body>
</html>