<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View/Add Comments</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
</head>
<body>
    <?php
        ini_set('display_errors', 1);
        require 'connect.php';
        $currentID = $_GET['subID'];
        echo "<table><tr><td class=headerLinks><a href=study.php>Subject List</a></td>";
        echo "<td class=headerLinks><a href=subject.php?sub=$currentID>Subject Info</a></td></tr></table>";
        echo "<h3>View Subject Comments</h3>";
        echo "<h4>Comments for subject ID $currentID:</h4>";
        //the following is the maximum possible value the coseq could be for this ID
        $maxcoseq = $redis->hGet("$me:DM:$studyid:$currentID", 'last_coseq');
        $indexcoseq = 1; //if there are any comments, the first coseq will be 1
        echo "<p><ul>";
        while ($indexcoseq <= $maxcoseq) {
          //set key
          $k = "$me:CO:$studyid:$currentID:$indexcoseq";
          if ($redis->exists($k)) {
            //if this key exists, print out its info
            $date = $redis->hGet($k, 'codtc');
            $commentVal = $redis->hGet($k, 'coval');
            echo "<li><b>Date:</b> $date <ul><li><b>Comment:</b> $commentVal</li></ul></li>";
          }
          $indexcoseq = $indexcoseq + 1;
        }
        echo "</ul></p>";
        if (isset($_POST["submit"])) {
          //get everything from input fields
          $coDay = $_POST["day"]; //day of comment
          $coMonth = $_POST["month"]; //month of comment
          $coYr = $_POST["yr"]; //year of comment
          $val = $_POST["comment"]; //comment contents
          //get date into standard format
          $pat = "/[^0-9]/";
          if (preg_match($pat, $coDay) == 1 || preg_match($pat, $coMonth) == 1 || preg_match($pat, $coYr) == 1) {
            echo '<script type="text/javascript">alert("Please ensure your inputs are correct (e.g., dates cannot contain letters)");</script>';
          }
          else {
            $coDate = new DateTime(date("$coYr-$coMonth-$coDay H:i:s"));
            $updatedDate = $coDate->format(DateTime::ATOM);
            //stick values into associative array
            $coSubmit = array(
              'codtc' => $updatedDate,
              'coval' => $val
            );
            //get the new last_coseq (last_coseq++)
            $key = "$me:DM:$studyid:$currentID";
            $last_coseq = $redis->hIncrBy($key, 'last_coseq', 1);
            $use_key = "$me:CO:$studyid:$currentID:$last_coseq";
            $redis->hMset($use_key, $coSubmit); //submit key val pair
          }
        }
        $redis->close();
    ?>
    <h4>Add a new comment for this subject:</h4>
    <table>
    <form method="post" action="">
        <tr><td>Date:</td><td><input type="text" name="month" placeholder="MM"/>
        <input type="text" name="day" placeholder="DD"/>
        <input type="text" name="yr" placeholder="YYYY"/></td></tr>
        <tr><td>Comment:</td><td><input type="text" name="comment"/></td></tr></table>
        <input type="submit" value="Submit" name="submit"/><br />
    </form>
</body>
</html>