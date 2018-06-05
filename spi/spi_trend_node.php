<?php
include_once 'common.php';
$func=@$_POST['func'];
$disp_flag = 0;
$query = "select count(*) from customers";
$result = $dbObj->singleValue($query);

if ( $result > 1 )
  $disp_flag = 1;

if(!isset($func))
  goto exit_it;
  global $customer_ids;
  $tc = @$_POST['tc'] ;
  $node = @$_POST['node'] ;
  $chart_hour = @$_POST['chart_hour'] ;
switch ($func){

        case 0: 
                global $script_dir;
                unset($_SESSION['counters'][$tc]);
                $counter=$_POST['counter'];
                $date=$_POST['date'];
                $chart_hour = $_POST['chart_hour'] != "" ? $_POST['chart_hour'] : 0;
                $node_type = substr($node, 0,strpos($node,'_'));
                $table=make_string_column("{$node_type}_counter_info");                                 
                if ( $counter!='' )                
                  $where[]="counter_name in ($counter)";                            
                else              
                  $where[]="1";
                if ( $chart_hour==0 )
                  $where[]="time like (select max(time) from $table where date like '$date')";
                else
                  $where[]="date_format(time,'%H') = {$chart_hour}";
                $where = implode(' and ', $where);
                $query = "select counter_name, sum(value) as value from $table where date like '$date' and $where and traffic_case like '$tc' and node_name='$node' group by counter_name, date_format(time,'%H') order by value desc";
               
                $result = $dbObj->resultset($query);
                    $tab='<table class="rounded-corner1" width="100%" height="100%" cellpadding="7" cellspacing="7"><tr><th>Counter Name<th>Count';
                $bar=$pie='[';


                $colorArr = array();
                $f=fopen($script_dir.DS."Conf".DS."spi_counter_map.txt", "r");
                if($f)while(!feof($f)){
                    $line = trim(fgets($f));
                    if($line!=''){
                        $line = explode(":", $line);
                        $colorArr[$line[0]]=$line[1];
                    }
                }
                fclose($f);
                $pie = array();
                foreach ($result as $row) {                
                      $class="green";
                      $key=$row['counter_name'];
                      $value=$row['value'];
                      foreach ($colorArr as $k => $v) {
                        if (stristr($key, $k))
                        {
                            $class=$v;
                            break;
                        }    
                      }
                      $temp[] = $row;
                                         
                      $tab .= "<tr><td class='$class'>".$key."<td class='$class'>".$value;
                      $_SESSION['counters'][$tc][] = $key;
                }            
                $drilldown = array();
                if ( count($temp) > 10 )
                {
                  $pie = array();
                   foreach ($temp as $valuearr) { 
                      $counter_name=$valuearr['counter_name'];
                      $value=$valuearr['value'];
                      $parts = explode('-', $counter_name);
                      $count = count($parts);
                      $counter_part = $parts[$count-2].'-'.$parts[$count-1];
                      $out[$counter_part] = isset($out[$counter_part]) ? $out[$counter_part]+$value : $value;
                      $temp1[$counter_part][] = "['$counter_name',$value]";
                  }
                  asort($temp1);
                  foreach ($out as $key => $value) {                
                      $drill_val = implode(',', $temp1[$key]);   
                      $drilldown []= "{name:'$key',id:'$key',data:[$drill_val ]}";                                          
                      $pie[] = "{name:'$key',y:$value,drilldown:'$key'}";                
                  }

                }
                else
                {
                  $pie = array();
                  foreach ($temp as $valuearr) {                                  
                      $key=$valuearr['counter_name'];
                      $value=$valuearr['value'];
                      $pie[]= "['".$key."',".$value."]";                   
                  }
                }
                $pie = implode(',', $pie);
                $drilldown = implode(',', $drilldown);
                $tab .= '</table>';
                if (ini_get("zlib.output_compression") == 1)
                    ob_start();
                else
                    ob_start("ob_gzhandler");
                echo "{name:'Counter Categories',data: [$pie]}".'<%SEP%>'.$drilldown.'<%SEP%>'.$tab;
                ob_end_flush();                             
                break;
        
         case 1: 
           foreach (array_unique($_SESSION['counters'][$tc]) as $node)               
                  echo '<option value="'.$node.'">'.$node.'</option>';
                break;                       
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
    <link rel="stylesheet" type="text/css" href="css/table.css" />
    <link href="css/spi_trending.css" rel="stylesheet">
    <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" type="text/css" />
    <link href="css/theme.css" rel="stylesheet" type="text/css" />
    <link rel="icon" type="image/png" href="img/causecode.png"/>
    <style>p{font-size:13px;}</style>
</head>
<body onload="showNode(0,'<?php xecho(@$_GET['tc']) ?>', '<?php xecho(@$_GET['node']) ?>', '<?php xecho(@$_GET['hour']) ?>', '<?php xecho(@$_GET['date']) ?>', '<?php xecho(@$_GET['module']) ?>');">
   <?php include('_getModuleNameAndImage.php');   ?>    
    <?php include('sections/overlay.php'); ?>
    <?php include('sections/mainbar.php'); ?>
    
    <div class="clear"></div>
  <div id="wrapper">
    <span class="heading"><img src="<?php echo $imagePath; ?>"><a href="spi_trend.php?date=<?php xecho($_GET['date']) ?>">Home</a> - <a href="spi_trend_tc.php?tc=<?php xecho($_GET['tc']); ?>&hour=<?php xecho($_GET['hour']) ?>&date=<?php xecho($_GET['date']) ?>"><?php xecho($_GET['tc']); ?></a> - <?php xecho($_GET['node']) ?></span>
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
                  <div class="title" id="pieTitle" style="width:76%;float:left;font-size:13px; padding-left:11%">
                     
                  </div>
                  <?php $sess_id = session_id(); ?>
            
            <img src="img/filter-icon.png" width="16" height="16" style="float:right;margin:7px" onclick="showFilter(1)"/>
            <?php $node_type = substr($_GET['node'], 0, strpos($_GET['node'], '_')); ?>
              <a target="_blank" href="#"><input id="errorLogBtn" type="button" class="button" value="Error Log Analytics"/></a>

                </div>
                <div class="widget-body">
    <table id="filterDiv1" class="filter" width="100%" cellpadding="7" style="display:none">   
    
     <tr>
        <td width="5%" align="right">
            <p>Counters</p>
        </td>
        <td width="30%">
            <select id="navsel1" size="3" multiple="multiple"> </select>
            <img title="Select All" alt="Select All" src="img/check-all.png" style="cursor: pointer;text-decoration: underline" onclick="selectallfunc(1)"/>
        </td>
        
       
    
       <td align="right">
          <p>Hour</p>
      </td>
      <td>
         <input type="text" id="chart_hour" readonly="readonly" placeholder="Select Hour" style="margin-top:5px"/>
      </td>  
        
      
      
  
        <td colspan="6">
            <input class="button" type="button" value="Apply" onclick="showNode(1,'<?php xecho(@$_GET['tc']) ?>', '<?php xecho(@$_GET['node']) ?>', '<?php xecho(@$_GET['hour']) ?>', '<?php xecho(@$_GET['date']) ?>');"/>            
            <input class="button" type="button" value="Clear" onclick="showNode(0,'<?php xecho(@$_GET['tc']) ?>', '<?php xecho(@$_GET['node']) ?>', '<?php xecho(@$_GET['hour']) ?>', '<?php xecho(@$_GET['date']) ?>');"/>
            <input class="button" type="button" value="Close" onclick="closeFilter(1)"/>
        </td>
    </tr>
    
</table>
                  <div id="pieDiv"></div>
                </div>
              </div>
            </div>
</div>
<div class="row-fluid">
            <div class="span12">

              <div class="widget">
                <div class="widget-header">
                  <div class="title" id="tabTitle" style="font-size:13px">
                     
                  </div>
                </div>
                <div class="widget-body">
                  <div id="tabDiv"></div>
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
<script src="js/HighChart/modules/drilldown.js"></script>
<script></script>

</body>

</html>
