<?php
include_once 'common.php';
$func=@$_POST['func'];
$disp_flag = 0;
$query = "select count(*) from customers";
$result = $dbObj->singleValue($query);

if ( $result > 1 )
  $disp_flag = 1;

$hour=0;
$where=1;
  
  global $customer_ids;
  global $dbObj;
  $database = $dbObj->getDbname();
  $tc = @$_GET['tc'] ;
   $query = "select TABLE_NAME as `table` from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '$database' and TABLE_NAME like '%_tc' ";
  $result = $dbObj->resultset($query);
  foreach ($result as $row) {
    $tables[] = $row['table'];
  }
  if(!isset($func))
  goto exit_it;
switch ($func){

        case 0:
                $tc = @$_POST['tc'] ;
                $date = @$_POST['date'] ;
                $chart_hour = @$_POST['chart_hour'];
                 foreach ($tables as $table) {                 
                
                  $query = "select node_name,sub_traffic_case, value from $table where date like '$date' and date_format(time,'%H') = $chart_hour and traffic_case like '$tc' order by node_name";
                  $result = $dbObj->resultset($query);
                  foreach ($result as $row) {
                    $sub_tc = substr($row['sub_traffic_case'], strpos($row['sub_traffic_case'], 'T'));
                    $temp[$row['node_name']][$sub_tc] = $row['value'];              
                  }            
                }
                foreach ($temp as $node_name => $sub_tc_arr) {
                  if ( $sub_tc_arr['TA'] == 0 )
                    $value='N/A';
                  else
                  {
                    $value = truncate_number($sub_tc_arr['TSA'] / $sub_tc_arr['TA'] * 100, 2);
                    if (intval($value) == 100)
                      $value=round($value);
                    $_SESSION['nodes'][$tc][] = $node_name;
                  }                  
                  $res[make_string_column($node_name)] = $value;                  
                }                
                echo json_encode($res);
                break;
        case 1:
                $tc=@$_POST['tc'];
                $date=@$_POST['date'];
                $node=$_POST['node'];
                $temp=array();
                if ( $node!='' )
                {
                  $fromdate=$_POST['fromdate'];
                  $todate=$_POST['todate'];
                  $where="node_name in ($node) and date between '$fromdate' and '$todate'";
                }
                  
                else
                  $where="date like '$date'";
                foreach ($tables as $table) {                 
                
                  $query = "select node_name,date,time,sub_traffic_case as sub_tc,sum(value) as value from $table where $where and traffic_case like '$tc' group by node_name,sub_traffic_case,time";
                  $query = "select node_name,date,time,sub_traffic_case as sub_tc,value from $table where $where and traffic_case like '$tc' order by node_name,date,time";
                  $result = $dbObj->resultset($query);
                  foreach ($result as $row) {   
                    $sub_tc = substr($row['sub_tc'], strpos($row['sub_tc'], 'T'));
                    $temp[$row['node_name']][$row['date'].' '.$row['time']][$sub_tc] = $row['value'];              
                  }            
                }
                $tc_data = array();
                foreach ($temp as $node_name => $time_arr) {
                  foreach ($time_arr as $datestr => $sub_tc_arr) {
                  
                    if ( $sub_tc_arr['TA'] != 0 )
                    {
                      $value = truncate_number($sub_tc_arr['TSA'] / $sub_tc_arr['TA'] * 100, 2);                                  
                      $tc_data[$node_name][$datestr] = $value;
                    }
                  }                 
                }   
                echo getTrendData($tc_data);
                    break;
         case 2: 
                $tc=@$_POST['tc'];
         sort($_SESSION['nodes'][$tc]);
           foreach (array_unique($_SESSION['nodes'][$tc]) as $node)               
                  echo '<option value="'.$node.'">'.$node.'</option>';
                break;                       
}

function getTrendData($data)
{
	global $dateLongDelta;
    $data_send = '';
  
    if ( count($data) )
    {
      foreach ($data as $node => $date_array) {
        $data_out = $data = array();
        $data_send.='{name:\''.$node.'\',data:[';
        $i=0;
        foreach ($date_array as $date_value => $value) {

              $pointId='pointId'.$i++;
              $dateLong=strtotime($date_value);
              $dataLabel='';
              $name=",name:'".date('F, jS Y, H:i:s',$dateLong)."'";
              $redit='';
              $value=number_format($value,2,".","");
              $data_send.='{id:"'.$pointId.'",x:'.(($dateLong+$dateLongDelta*60*60) * 1000).',y:'.$value.$name.$dataLabel.$redit.'},';//+5.5*60*60
        }
        $data_send=substr($data_send,0,strlen($data_send)-2);
        $data_send.="}";

        $data_send.="]},";
      }
      $data_send=substr($data_send,0,strlen($data_send)-1);
      }
      else{
        $data_send="";       
      }
    
    return $data_send;
}


function getChartData($data, $type, $hour)
{
    $temp = $out = $data_send = $meta = array();
    foreach ($data as $node => $value) {      
      $meta[] = $node;
      $temp['Successful Attempts'][] = $value['TSA'];
      $temp['Unsuccessful Attempts'][] = $value['TA']-$value['TSA'];
    }
      $out['Successful Attempts'] = implode(',', $temp['Successful Attempts']);
      $out['Unsuccessful Attempts'] = implode(',', $temp['Unsuccessful Attempts']);
    foreach ($out as $name => $val) {
      $data_out = $data = array();
        $data_send[] = "{name: '{$name}', data: [$val]}";
    }
    $meta = "'".implode("','", $meta)."'";
    $data_send = implode(',', $data_send);
    return '<%METASEND%>'.$meta.'<%DATASEND%>'.$data_send;
}

function truncate_number( $number, $precision = 2) {
    $precision = pow(10, $precision);
    return floor( $number * $precision ) / $precision;
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
<body onload="showTc('<?php xecho(@$_GET['tc']."','".$_GET['hour']."','".$_GET['date']) ?>');">
   <?php include('_getModuleNameAndImage.php');   ?>
    <?php include('sections/overlay.php'); ?>
    <?php include('sections/mainbar.php'); ?>
    
    <div class="clear"></div>
  <div id="wrapper">
    <span class="heading"><img src="<?php echo $imagePath; ?>"><a href="spi_trend.php?date=<?php xecho($_GET['date']) ?>">Home</a> - <?php xecho($_GET['tc']) ?></span>
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
    <div class="container-fluid">


      <div class="dashboard-wrapper">

        <div class="main-container">
          <div class="row-fluid">
            <div class="span12">
              <div class="widget">
                <div class="widget-header">
                  <div class="title" id="trendTitle" style="width:90%;float:left;font-size:13px">
                     
                  </div>
                  <?php $sess_id = session_id(); ?>
            <img src="img/filter-icon.png" width="16" height="16" style="float:right;margin-right:10px" onclick="showFilter(1)"/>
                </div>
                <div class="widget-body">
    <table id="filterDiv1" class="filter" width="100%" cellpadding="7" style="display:none">   
    
     <tr>
        <td width="5%" align="right">
            <p>Nodes</p>
        </td>
        <td width="25%">
            <select id="navsel1" size="3" onchange="displayOptions(1)" multiple="multiple"> </select>
            <img title="Select All" alt="Select All" src="img/check-all.png" style="cursor: pointer;text-decoration: underline" onclick="selectallfunc(1)"/>
        </td>
        
       
    
        <td width="20%">
            <p>From date</p>
             <input style="width:80px" type="text" id="fromdate" readonly="readonly" placeholder="From date"/>
            
        </td>
        <td width="20%">
            <p>To date</p>
            <input style="width:80px" type="text" id="todate" readonly="readonly" placeholder="To date"/>
        </td>
        
      
      
  
        <td colspan="6">
            <input class="button" type="button" value="Apply" onclick="drawTrend(1,'<?php xecho($_GET['tc']); ?>','<?php xecho($_GET['date']); ?>')"/>            
            <input class="button" type="button" value="Clear" onclick="drawTrend(0,'<?php xecho($_GET['tc']); ?>','<?php xecho($_GET['date']); ?>')"/>
            <input class="button" type="button" value="Close" onclick="closeFilter(1)"/>
        </td>
    </tr>
    
</table>
                  <div id="trend"></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row-fluid">

    <div class="widget group_widget" >
<?php 
$date=@$_GET['date'];
$hour=@$_GET['hour'];

foreach ($tables as $table) {                 
                
  $query = "select node_name,sub_traffic_case, value from $table where date like '$date' and hour(time) = $hour and traffic_case like '$tc'";

  $result = $dbObj->resultset($query);
  foreach ($result as $row) {
    $sub_tc = substr($row['sub_traffic_case'], strpos($row['sub_traffic_case'], 'T'));
    $temp[$row['node_name']][$sub_tc] = $row['value'];              
  }            
}
                
 foreach ($temp as $node_name => $sub_tc_arr) {
    if ( $sub_tc_arr['TA'] == 0 )
      $value='N/A';
    else
      $nodes[] = $node_name;                                    
  } 
sort($nodes);
$span = 12/count($nodes);
  $span=1;
  $prev_node_type='';
foreach ($nodes as $node) { 
  $parts = explode('_', $node);
  $node_type=$parts[0];
  $node_name=$parts[1];

if ($prev_node_type != $node_type && $prev_node_type !='')
          echo '</div><div class="widget group_widget">';    
  $prev_node_type=$node_type;
  ?>
   <div class="span<?php echo $span ?>">
              <a href="spi_trend_node.php?module=<?php xecho($_GET['module']) ?>&tc=<?php xecho($_GET['tc']) ?>&node=<?php echo $node ?>&hour=<?php xecho($_GET['hour']) ?>&date=<?php xecho($_GET['date']) ?>">
                <div class="widget-header" style="background:none; border:none; min-height:0; line-height:10px">
                  <div class="title">
                     <?php echo $node_name; ?>
                  </div>
                </div>
                <div class="widget-body">
                 
                    <div>
                      <h5 id='<?php echo make_string_column($node); ?>'></h5>
                    </div>
                 
                </div>
            </a>
            </div>

<?php }?>
            
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
