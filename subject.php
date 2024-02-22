<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Subject</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
</head>
<body>
    <?php
        require 'connect.php';
        $currentsub = $_GET['sub'];
        $k = "$me:DM:$studyid:$currentsub";
        echo "<table><tr><td class=headerLinks><a href=study.php>Subject List</a></td>";
        echo "<td class=headerLinks><a href=comment.php?subID=$currentsub>View/Add Comments</a></td>";
        echo "<td class=headerLinks><a href=visit.php?subID=$currentsub>View/Add Visits</a></td></tr></table>";
        echo "<h3>View Subject Demographic Information</h3>";
        if ($redis->exists($k)) {
          echo "<h4>Info for subject $currentsub:</h4>";
          $subjectR = $redis->hGet($k, 'race');
          $subjectS = $redis->hGet($k, 'sex');
          $subjectC = $redis->hGet($k, 'country');
          $subjectDOB = $redis->hGet($k, 'brthdtc');
          echo "<p><ul><li><b>Date of Birth:</b> $subjectDOB</li><li><b>Race:</b> $subjectR</li><li><b>Sex:</b> $subjectS</li><li><b>Country:</b> $subjectC</li></ul></p>";
        }
        if (isset($_POST["submit"])) {
          //get everything from input fields
          $dobDay = $_POST["dobday"]; //day of dob
          $dobMonth = $_POST["dobmonth"]; //month of dob
          $dobYr = $_POST["dobyr"]; //year of dob
          $s = $_POST["sex"]; //subject sex
          $c = $_POST["country"]; //subject country
          $r = $_POST["race"]; //subject race
          $pat = "/[^0-9]/";
          if (preg_match($pat, $dobDay) == 1 || preg_match($pat, $dobMonth) == 1 || preg_match($pat, $dobYr) == 1) {
            echo '<script type="text/javascript">alert("Please ensure your inputs are correct (e.g., dates cannot contain letters)");</script>';
          }
          else {
            //get dob into standard format
            $dob = new DateTime("$dobYr-$dobMonth-$dobDay 23:21:46");
            $updatedDob = $dob->format(DateTime::ATOM);
            $cvm = $redis->hGet($k, 'last_visitnum');
            $ccs = $redis->hGet($k, 'last_coseq');
            //stick values into associative array
            $dmSubmit = array(
              'brthdtc' => $updatedDob,
              'sex' => $s,
              'race' => $r,
              'country' => $c,
              'last_visitnum' => $cvm,
              'last_coseq' => $ccs
            );
            $redis->hMset($k, $dmSubmit); //update this key with new DM info
          }
        }
        if (isset($_POST["delsub"])) {
          $hm = $redis->get("$me:$studyid:last_usubjid");
          //first, get last visit and last comment in case there are visits and comments assoc w subject
          $visitindex = 1;
          $commentindex = 1;
          $maxvisits = $redis->hGet($k, 'last_visitnum');
          $maxcomments = $redis->hGet($k, 'last_coseq');
          while ($visitindex <= $maxvisits) {
            $visit_key = "$me:SV:$studyid:$currentsub:$visitindex";
            $redis->del($visit_key);
            $visitindex = $visitindex + 1;
          }
          while ($commentindex <= $maxcomments) {
            $comment_key = "$me:CO:$studyid:$currentsub:$commentindex";
            $redis->del($comment_key);
            $commentindex = $commentindex + 1;
          }
          $redis->del($k);
          echo '<script type="text/javascript">alert("Subject Deleted");</script>';
        }
        $redis->close();
    ?>
    <h4>Update demographic info for this subject:</h4>
    <table>
    <form id="subjInput" method="post" action="">
        <tr><td>Date of Birth:</td><td><input type="text" name="dobmonth" placeholder="MM"/>
        <input type="text" name="dobday" placeholder="DD"/>
        <input type="text" name="dobyr" placeholder="YYYY"/></td></tr>
        <tr><td>Sex:</td><td><input type="text" name="sex"/></td></tr>
        <tr><td>Country:</td><td><input type="text" name="country"/></td></tr>
        <tr><td>Race:</td><td><input type="text" name="race"/></td></tr></table>
        <input type="submit" value="Submit" name="submit"/><br />
        <h4>Delete this subject:</h4>
        <input type="submit" value="Delete" name="delsub"/>
    </form>
</body>
</html>