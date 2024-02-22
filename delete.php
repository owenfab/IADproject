<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>delete</title>
  <link rel="stylesheet" type="text/css" href="finalprojstyle.css"/>
</head>
<body>
    <?php
        require 'connect.php';
        $indexsubjid = $redis->get("$me:$studyid:last_usubjid");
        $store = $indexsubjid;
        //del that key; it's always there otherwise
        $redis->del("$me:$studyid:last_usubjid");
        echo "<!-- del all keys & assoc vals -->\n";
        $k = "$me:DM:$studyid:$indexsubjid";
        //del all current subjects in DM
        while ($indexsubjid >= 1) {
          if ($redis->exists($k)) {
            echo "DM : $indexsubjid \n";
            $k = "$me:DM:$studyid:$indexsubjid";
            echo "deleting $k\n";
            $redis->del($k);
          }
          $indexsubjid = $indexsubjid - 1;
          $k = "$me:DM:$studyid:$indexsubjid";
          echo "testing $k \n";
        }
        //delete all subjects currently in CO
        $indexsubjid = $store; //reset index
        $secondisubjid = 1;
        $k = "$me:CO:$studyid:$indexsubjid:$secondisubjid";
        while ($indexsubjid >= 1) {
          echo "CO: $indexsubjid \n";
          if ($redis->exists($k)) {
            $s_k = "$me:CO:$studyid:$indexsubjid:$secondisubjid";
            while ($redis->exists($s_k)) {
              echo "CO: $indexsubjid $secondisubjid \n";
              $redis->del($s_k);
              $secondisubjid = $secondisubjid + 1;
              $s_k = "$me:CO:$studyid:$indexsubjid:$secondisubjid";
            }
          }
          $indexsubjid = $indexsubjid - 1;
          $secondisubjid = 1;
          $k = "$me:CO:$studyid:$indexsubjid:$secondisubjid";
        }
        //delete all subjects currently in SV
        $indexsubjid = $store; //reset index
        $secondisubjid = 1;
        $k = "$me:SV:$studyid:$indexsubjid:$secondisubjid";
        while ($indexsubjid >= 1) {
          echo "SV: $indexsubjid \n";
          if ($redis->exists($k)) {
            $s_k = "$me:SV:$studyid:$indexsubjid:$secondisubjid";
            while ($redis->exists($s_k)) {
              echo "SV: $indexsubjid $secondisubjid \n";
              $redis->del($s_k);
              $secondisubjid = $secondisubjid + 1;
              $s_k = "$me:SV:$studyid:$indexsubjid:$secondisubjid";
            }
          }
          $indexsubjid = $indexsubjid - 1;
          $secondisubjid = 1;
          $k = "$me:SV:$studyid:$indexsubjid:$secondisubjid";
        }
        $redis->close();
    ?>
</body>
</html>