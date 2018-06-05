<?php
include_once 'common.php';
global $dbObj;
global $dateLongDelta;
$func=@$_POST['func'];
$disp_flag = 0;
$query = "select count(*) from customers";
$result = $dbObj->singleValue($query);

if ( $result > 1 )
  $disp_flag = 1;

if(!isset($func))
  goto exit_it;

$where=1;
  $node_arr = array();
  global $customer_ids;
  $tc = @$_POST['tc'] ;
  $node = @$_POST['node'];
  $date = @$_POST['date'];
  $hour = @$_POST['hour'];
  $hour = intval($hour); 
  // $hour = 17;
  $parts = explode('_', $node);
  $node_type = $parts[0];
  $node = $parts[1];
  
  function getOrdinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
  }

switch ($func){
        case 0:
        $query = "select count(*) as error_count from spi_air where LOG_DATE = '{$date}' and LOG_HR = $hour and NODE_NAME like '$node' and EVENTINFO is not null";
                echo $error_count = $dbObj->singleValue($query);
                break;
        case 1:
               $query = "select error_info, error_info_CNT as error_count from spi_air_refill_error_info_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and error_info is not null group by error_info";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {
                  $temp[$row['error_info']] = $row['error_count'];                  
                }         
                arsort($temp);
                $count = count($temp);
                $data['draw'] = 1;
                $data['recordsTotal'] = $count;
                $data['recordsFiltered'] = $count;


                foreach ($temp as $key => $value) {                          
                  $data['data'][] = array($key, $value);
                }

                echo json_encode($data);
                
                break;
        case 2:
               $query = "select module, module_CNT error_count from spi_air_module_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and module is not null group by module";
               
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {  
                  $temp[$row['module']] = $row['error_count'];                  
                }         
                arsort($temp);               
                foreach ($temp as $name => $value) {
                  $data_send[] = "{name: '{$name}', data: [$value]}";
                }        
                $data_send = implode(',', $data_send);
                $meta = "'".implode("','", $meta)."'";
                echo '<%METASEND%>""<%DATASEND%>'.$data_send;
                break;        
        case 3:                
                $query = "select date_format(LOG_TIMESTAMP,'%Y-%m-%d %H:%i') as time,count(*) as error_count from spi_air where LOG_DATE = '{$date}' and LOG_HR = $hour and NODE_NAME like '$node' and EVENTINFO is not null group by date_format(LOG_TIMESTAMP,'%Y-%m-%d %H:%i') order by LOG_TIMESTAMP";

                $result=$dbObj->resultset($query); 
                $data_send="";
                $data_send.='{name:"Error Count",data:[';  
                $i=0;            
                foreach ($result as $row) {
                    $pointId='pointId'.$i++;
                    $dateLong=strtotime($row['time']);
                    $dataLabel='';
                    $name=",name:'".date('F, jS Y, H:i:s',$dateLong)."'";
                    $redit='';
                    $value=$row['error_count'];
                    $data_send.='{id:"'.$pointId.'",x:'.(($dateLong+$dateLongDelta*60*60) * 1000).',y:'.$value.$name.$dataLabel.$redit.'},';        
                }       
                $data_send=substr($data_send,0,strlen($data_send)-2);
                $data_send.=",dataLabels:{enabled:true,style:{fontWeight:'bold'}}}";
                $data_send.="]}";                                                        
                echo $data_send;
                break;        
        case 4:
                $query = "select name, errorcode, errorcode_CNT error_count from spi_air_refill_errorcode_hrly_agg g left join spi_air_refill_error_code_map as map on map.code=g.errorcode where LOG_DATE = '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and errorcode not like 'OTHER' group by errorcode";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {  
                  $temp[$row['errorcode'].';'.$row['name']] = $row['error_count'];                  
                }         
                arsort($temp);
                $_SESSION[$node]['err_code_arr'] = $temp;
                $i=0;
                foreach ($temp as $key => $value) {
                  $parts=explode(';', $key);    
                  $code = $parts[0];                              
                  $pie[]= "['".$code."',".$value."]"; 
                  $i++;
                  if ( $i==5 )
                    break;
                }        
                $pie = implode(',', $pie);
                echo "{name:'Error Count',data: [{$pie}]}";
                break;        
        case 5:
                $temp=$_SESSION[$node]['err_code_arr'];
                $count = count($temp);
                $data['draw'] = 1;
                $data['recordsTotal'] = $count;
                $data['recordsFiltered'] = $count;


                foreach ($temp as $key => $value) {
                  $parts=explode(';', $key);    
                  $code = $parts[0];
                  $name = $parts[1];    
                  $nametxt = $name == '' ? '' : " ($name)";             
                  $data['data'][] = array('<strong>'.$code.'</strong>'.$nametxt, $value);
                }
                echo json_encode($data);
                break;        

        case 6:            
                $query = "select msisdn, msisdn_CNT error_count from spi_air_refill_msisdn_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and msisdn not like 'OTHER' group by msisdn";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {  
                  $temp[$row['msisdn']] = $row['error_count'];                  
                }         
                arsort($temp);
                $count = count($temp);
                $data['draw'] = 1;
                $data['recordsTotal'] = $count;
                $data['recordsFiltered'] = $count;

                foreach ($temp as $key => $value) {                        
                  $data['data'][] = array($key, $value);
                }

                echo json_encode($data);
                break;

        case 7:            
                $query = "select serialno, serialno_CNT error_count from spi_air_refill_serialno_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and serialno not like 'OTHER' group by serialno";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {  
                  $temp[$row['serialno']] = $row['error_count'];                  
                }         
                arsort($temp);
                $count = count($temp);
                $data['draw'] = 1;
                $data['recordsTotal'] = $count;
                $data['recordsFiltered'] = $count;

                foreach ($temp as $key => $value) {                        
                  $data['data'][] = array($key, $value);
                }

                echo json_encode($data);
                break;        

        case 8: $query = "select event, event_CNT error_count from spi_air_event_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and event not like 'OTHER' group by event";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {
                  $temp[$row['event']] = $row['error_count'];                  
                }         
                foreach ($temp as $key => $value) {                  
                  $pie[]= "['".$key."',".$value."]"; 
                }        
                $pie = implode(',', $pie);
                echo "{name:'Error Count',data: [{$pie}]}";
                break;

        case 9: $query = "select type, type_CNT error_count from spi_air_type_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and type is not null group by type";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {
                  $temp[$row['type']] = $row['error_count'];                  
                }         
                foreach ($temp as $key => $value) {                  
                  $donut[]= "['".$key."',".$value."]"; 
                }        
                $donut = implode(',', $donut);
                echo "{name:'Error Count',data: [{$donut}]}";
                break;
        
        case 10:            
                $query = "select eventinfo, eventinfo_CNT error_count from spi_air_eventinfo_hrly_agg where LOG_DATE like '{$date}' and LOG_TIME like '{$hour}' and NODE_NAME like '$node' and eventinfo not like 'OTHER' group by eventinfo";
                $result=$dbObj->resultset($query);               
                foreach ($result as $row) {  
                  $temp[$row['eventinfo']] = $row['error_count'];                  
                }         
                arsort($temp);
                $count = count($temp);
                $data['draw'] = 1;
                $data['recordsTotal'] = $count;
                $data['recordsFiltered'] = $count;

                foreach ($temp as $key => $value) {                        
                  $data['data'][] = array($key, $value);
                }

                echo json_encode($data);
                break;  


                        
}

if(isset($func))
  myExit();
exit_it:
if (stristr($_SERVER['HTTP_REFERER'], 'error_log_analytics'))
  $home_url = 'error_log_analytics.php';
else
  $home_url = "spi_trend.php?date={$_GET['date']}&hour={$_GET['hour']}";
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>
      Service Performance Indicators
    </title>

    <link href="css/main.css" rel="stylesheet">
    <link href="css/table.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" type="text/css" />
    <link href="css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="css/theme.css" rel="stylesheet" type="text/css" />
    <link href="css/spi_trending.css" rel="stylesheet">
    <link href="css/spi_cca.css" rel="stylesheet">
    <link href="css/pace.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="img/causecode.png"/>
    <style>p{font-size:13px;}</style>
</head>
<body onload="showCca(0,'<?php xecho(@$_GET['node']) ?>','<?php xecho(@$_GET['hour']) ?>','<?php xecho(@$_GET['date']) ?>');">
  <?php include('_getModuleNameAndImage.php');   ?>
  
  <?php include('sections/mainbar.php');  ?>
    
    <div class="clear"></div>
  <div id="wrapper">
   <span class="heading" style="width:100%"><img src="<?php echo $imagePath; ?>"><div><a href="<?php echo $home_url ?>">Home</a><?php if (isset($_GET['tc'])){ ?>- <a href="spi_trend_tc.php?tc=<?php xecho($_GET['tc'].'&hour='.$_GET['hour'].'&date='.$_GET['date']) ?>"><?php xecho($_GET['tc']); ?></a> <?php } ?> - <a href="spi_trend_node.php?tc=<?php xecho($_GET['tc'].'&node='.$_GET['node'].'&hour='.$_GET['hour'].'&date='.$_GET['date']) ?>"><?php xecho($_GET['node']) ?></a></div><input id="dateTimeLbl" type="button"  value="<?php xecho($_GET['date'].', '.getOrdinal($_GET['hour']).' hour'); ?>"/></span>
        <div class="clear"></div>    
    <div class="container-fluid" id="dashBoard">
      <div class="dashboard-wrapper">
        <div class="main-container">

          <div class="row-fluid">
            <div class="span4">
              <div class="widget" style="margin-bottom:5px">
                <div class="widget-header">
                  <div class="title">
                     Error Count
                  </div>
                </div>
                <div class="widget-body">
                 <div class="error-count-air">
                    
                      <h1 id="error_count"></h1> 
                    
                  </div>
                </div>
              </div>
              
          </div>
            <div class="span8">
              <div class="widget">
                <div class="widget-header">
                  <div class="title" style="width: 90%;float: left;margin-left: 6%;">
                     Error Trend
                  </div>
                  <img src="img/filter-icon.png" width="16" height="16" style="float:right;margin:7px" onclick="showFilter(1)"/>
                </div>
                <div class="widget-body"> 
  <table id="filterDiv1" class="filter" width="100%" cellpadding="7" style="display:none">   
    
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
        
      
      
  
        <td colspan="6">
            <input class="button" type="button" value="Apply" onclick="showCca(1,'<?php echo @$_GET['node'] ?>', '<?php echo @$_GET['hour'] ?>', '<?php echo @$_GET['date'] ?>');"/>            
            <input class="button" type="button" value="Clear" onclick="showCca(0,'<?php echo @$_GET['node'] ?>', '<?php echo @$_GET['hour'] ?>', '<?php echo @$_GET['date'] ?>');"/>
            <input class="button" type="button" value="Close" onclick="closeFilter(1)"/>
        </td>
    </tr>
    
</table>
                 <div class="current-statistics">                                         
                      <div id="error_trend1"></div>                                     
                  </div>                 
                </div>
              </div>
            </div>
          </div>

          <div class="row-fluid">
            <div class="span12">
              <div class="widget">
                <div class="widget-header">
                  <div class="title">
                     Modules
                  </div>
                </div>
                <div class="widget-body"> 
                 <div class="current-statistics" style="margin-top:0">                  
                      <div id="module_chart"></div>                                     
                  </div>                 
                </div>
              </div>
            </div>

          </div>

           <div class="row-fluid">            
            <div class="span4" style="width:31.3%">
              <div class="widget">
                <div class="widget-header">
                  <div class="title">
                     Event Info
                  </div>
                </div>
                <div class="widget-body">                  
                    <div class="current-statistics">                                          
                      <table id="eventinfo_tab">
                        <thead>
                          <tr>
                              <th>Event Info</th>
                              <th>Error Count</th>                             
                          </tr>
                      </thead>
                      </table>  
                  </div>
                </div>
              </div>
            </div>
            <div class="span4" style="width:31.3%">
              <div class="widget">
                <div class="widget-header">
                  <div class="title">
                     Events
                  </div>
                </div>
                <div class="widget-body">                  
                    <div class="current-statistics">                                          
                      <div id="event_pie"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="span4" style="width:31.3%">
              <div class="widget">
                <div class="widget-header">
                  <div class="title">
                     Types
                  </div>
                </div>
                <div class="widget-body">                  
                    <div class="current-statistics">                                          
                      <div id="type_pie"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>                

          <div class="row-fluid">

          </div>


        </div>
      </div>
          
    </div>
</div>
<script src="js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="js/jquery.dataTables.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="js/pace.min.js"></script>
<script src="js/ajax_script.js"></script>
<script src="js/spi_cca.js"></script>
<script type="text/javascript" src="js/HighChart/highcharts.js"></script>
<script type="text/javascript" src="js/HighChart/modules/exporting.js"></script>
<script></script>

</body>

</html>