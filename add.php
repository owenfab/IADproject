<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Subject</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
</head>
<body>
    <table><tr><td class="headerLinks"><a href="">Subject List</a></td></tr></table>
    <h3>Add a new subject</h3>
    <?php
        ini_set('display_errors', 1);
        require 'connect.php';
        $l = $redis->get("$me:$studyid:last_usubjid");
        $currentID = $l + 1;
        echo "<h4>Enter information for subject $currentID:</h4>";
        if (isset($_GET["submit"])) {
          //get everything from input fields
          $dobDay = $_GET["dobday"]; //day of dob
          $dobMonth = $_GET["dobmonth"]; //month of dob
          $dobYr = $_GET["dobyr"]; //year of dob
          $s = $_GET["sex"]; //subject sex
          $c = $_GET["country"]; //subject country
          $r = $_GET["race"]; //subject race
          $pat = "/[^0-9]/";
          if (preg_match($pat, $dobDay) == 1 || preg_match($pat, $dobMonth) == 1 || preg_match($pat, $dobYr) == 1) {
            echo '<script type="text/javascript">alert("Please ensure your inputs are correct (e.g., dates cannot contain letters)");</script>';
          }
          else {
            //get dob into standard format
            $dob = new DateTime("$dobYr-$dobMonth-$dobDay 23:21:46");
            $updatedDob = $dob->format(DateTime::ATOM);
            //stick values into associative array
            $dmSubmit = array(
              'brthdtc' => $updatedDob,
              'sex' => $s,
              'race' => $r,
              'country' => $c,
              'last_visitnum' => 0,
              'last_coseq' => 0
            );
            //get the new usubjid (last_usubjid++)
            $usubjid = $redis->incr("$me:$studyid:last_usubjid");
            $key = "$me:DM:$studyid:$usubjid";
            $redis->hMset($key, $dmSubmit); //submit key val pair
          }
        }
        $redis->close();
    ?>
    <table>
    <form method="get" action="">
        <tr><td>Date of Birth:</td><td><input type="text" name="dobmonth" placeholder="MM"/>
        <input type="text" name="dobday" placeholder="DD"/>
        <input type="text" name="dobyr" placeholder="YYYY"/></td></tr>
        <tr><td>Sex:</td><td><input type="text" name="sex"/></td></tr>
        <tr><td>Country:</td><td><input type="text" name="country"/></td></tr>
        <tr><td>Race:</td><td><input type="text" name="race"/></td></tr></table>
        <input type="submit" value="Submit" name="submit"/><br />
    </form>
</body>
</html>