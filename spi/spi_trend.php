<?php
error_reporting(0);
include_once 'common.php';

$func=@$_POST['func'];
$disp_flag = 0;
$query = "select count(*) from customers";
$result = $dbObj->singleValue($query);

if ( $result > 1 )
  $disp_flag = 1;

$query = "select distinct traffic_case as tc from traffic_case_agg order by traffic_case";
  // echo $query;die;
$result = $dbObj->resultset($query);
foreach ($result as $row) {
  $tcs[] = $row['tc'];
}

if(!isset($func))
  goto exit_it;
$chart_hour = $_POST['chart_hour'] != "" ? $_POST['chart_hour'] : (!IS_LOCAL ? date('H',strtotime('now')-60*60) : 20);
$chart_date = $_POST['chart_date'];
  global $customer_ids;
  
  $cust = isset($_POST['cust']) && $_POST['cust'] != '' ? $_POST['cust'] : $customer_ids;

switch ($func){

        case 0: 
                $res =  array();
                $query = "select traffic_case, value from traffic_case_agg where date like '$chart_date' and date_format(time,'%H') = $chart_hour order by traffic_case";
                $result = $dbObj->resultset($query);
                if ( $dbObj->rowCount($query) )
                {                
                  $query = "select traffic_case, value from traffic_case_agg where date like '$chart_date' and time like (select max(time) from traffic_case_agg where date like '$date')";
                  $result = $dbObj->resultset($query);
                }
                foreach ($result as $row) {
                  if (intval($row['value']) == 100)
                    $value=round($row['value']);
                  else
                    $value=$row['value'];
                  $res[make_string_column($row['traffic_case']).';'.$row['traffic_case']] = $value;
                }
                echo json_encode($res);
                break;
        case 1:
                $query = "select traffic_case, ta, tsa, tusa, time from traffic_case_agg where date like '$chart_date' and date_format(time,'%H') = {$chart_hour} order by traffic_case";
                echo getChartData($query, 'column', $chart_hour, $chart_date);
                    break; 
                        
}

function getChartData($query, $type, $hour, $date)
{
  global $dbObj;
  $res = $dbObj->resultset($query);
   if ( !mysqli_num_rows($res) )
    {      
      $query = "select traffic_case, ta, tsa, tusa, time from traffic_case_agg where date like '$date' and time like (select max(time) from traffic_case_agg where date like '$date')";
      $res = $dbObj->resultset($query);
    }
    $temp = $out = $data_send = $meta = array();
    $i=0;
    foreach ($res as $row) {
      if ($i===0)
      {
        $hour = date('H',strtotime($row['time']));
        $i++;
      }
      $meta[] = $row[0];
      $temp['Successful Attempts'][] = $row[2];
      $temp['Unsuccessful Attempts'][] = $row[3];
    }
    if ( count($temp) )
    {
      $out['Successful Attempts'] = implode(',', $temp['Successful Attempts']);
      $out['Unsuccessful Attempts'] = implode(',', $temp['Unsuccessful Attempts']);
      foreach ($out as $name => $val) {
        $data_out = $data = array();
          $data_send[] = "{name: '{$name}', data: [$val]}";
      }
      $meta = "'".implode("','", $meta)."'";
      $data_send = implode(',', $data_send);
      return '<%METASEND%>'.$meta.'<%DATASEND%>'.$data_send.'<%HOUR%>'.$hour;
      
    }
}
if(isset($func))
  myExit();
exit_it:
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
      Service Performance Indicators
    </title>

    <link href="css/main.css" rel="stylesheet">
    <link href="css/spi_trending.css" rel="stylesheet">
    <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" type="text/css" />
    <link href="css/theme.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/png" href="img/causecode.png"/>
    <style>p{font-size:13px;}</style>
</head>
<?php $date = isset($_GET['date']) ? xssafe($_GET['date']) : date('Y-m-d'); ?>
<body onload="displayOptions(0,<?php echo $disp_flag ?>,'<?php echo $date ?>','<?php xecho($_GET['module']); ?>');">
    <?php include('_getModuleNameAndImage.php');   ?>
    <?php include('sections/overlay.php'); ?>
    <?php include('sections/mainbar.php'); ?>
    
    <div class="clear"></div>
  <div id="wrapper">
    <span class="heading"><img src="<?php echo $imagePath; ?>"><?php echo $moduleName; ?></span>
        <div class="clear"></div>
         <table width="100%" style="display:<?php echo $disp_flag ? 'block' : 'none'; ?>">
         <tr>
        <td colspan="4" align="right">
            <p id="trendOpt" style="cursor:pointer;"><strong>Select Customer &raquo;</strong></p>
        </td>
    </tr></table>
        <div id="trendTbl" style="display:<?php echo $disp_flag ? 'block' : 'none'; ?>">
        <table width="100%" cellpadding="7">
    <tr>
        <td width="40%" align="right">
            <p>Customer</p>
        </td>
        <td width="60%">
            <select id="navsel0" size="3"> </select>
        </td>

    </tr>
   <tr>
        <td colspan="4" align="center">
            <input class="button" type="button" value="Submit" onclick="showVals(0,<?php echo $disp_flag; ?>)"/>
            <input class="button" type="button" value="Clear" onclick="window.location.reload()"/>
        </td>
    </tr>

</table>
    </div>
    <div class="container-fluid" id="dashBoard" style="display:none">


      <div class="dashboard-wrapper">

        <div class="main-container">

          <div class="row-fluid">

            <?php 
natcasesort($tcs);
$span = floor(12/count($tcs));
if ( $span < 2 ) 
$span=2;
foreach ($tcs as $tc) { ?>
   <div class="span<?php echo $span ?>">
              <a href="#">


                <div class="gauge" id="<?php echo make_string_column($tc); ?>"></div>             
            </a>
            </div>
<?php }?>                    
          </div>      
          <div class="row-fluid">
            <div class="span12" id="columnDiv">
              <div class="widget" style="margin-bottom:0">
                <div class="widget-header">
                  <div class="title" style="width:90%;float:left;font-size:13px" id="columnTitle">
                     
                  </div>
                 
            <img style="float:right; margin-right:5px" onclick="showFilter(2)" src="img/filter-icon.png" width="16" height="16"/>

                </div>
                <div class="widget-body"  style="overflow-x:scroll">
                      <table id="filterDiv2" class="filter" width="100%" cellpadding="7" style="display:none">   
    
                         <tr>
                            <td align="right">
                                <p>Date</p>
                            </td>
                            <td>
                               <input type="text" id="chart_date" readonly="readonly" placeholder="Select Date" style="margin-top:5px"/>
                            </td> 
                            <td align="right">
                                <p>Hour</p>
                            </td>
                            <td>
                               <input type="text" id="chart_hour" readonly="readonly" placeholder="Select Hour" style="margin-top:5px"/>
                            </td>                                                                          
                            <td>
                                <input class="button" type="button" value="Apply" onclick="drawColumnChart(1)"/>            
                                <input class="button" type="button" value="Clear" onclick="drawColumnChart(0)"/>
                                <input class="button" type="button" value="Close" onclick="closeFilter(2)"/>
                            </td>
                        </tr>
                        
                    </table>
                  <div id="column_chart"></div>
                </div>
              </div>
            </div>


          </div>
        </div>
      </div>
          
    </div>
</div>
<script src="js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="js/ajax_script.js"></script>
<script src="js/spi_trending.js"></script>
<script type="text/javascript" src="js/HighChart/highcharts.js"></script>
<script type="text/javascript" src="js/HighChart/highcharts-more.js"></script>

<script></script>

</body>

</html>
