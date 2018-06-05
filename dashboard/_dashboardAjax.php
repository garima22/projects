<?php
include_once 'common.php';
global $dateLongDelta;
global $dashboard_frontend_delta;
global $dashboard_frontend_delta_emm;
$func=@$_POST['func'];
$get_date = @$_GET['date'];
$get_hour = @$_GET['hour'];

if (!is_null($get_date) && !is_null($get_hour)){
  $timestamp = strtotime($get_date.' '.$get_hour.date('i'));
}
elseif (!is_null($get_date)) {
  $cur_hour = date('H');
  $timestamp = strtotime($get_date.' '.$cur_hour.date('i'));
}
elseif (!is_null($get_hour)) {
  $cur_date = date('Y-m-d');
  $timestamp = strtotime($cur_date.' '.$get_hour.date('i')); 
}
else
{
  $timestamp = time();
}
$cat_arr = array();
$cat_arr['kpis'] = getDashboardKpis('dashboard_config.txt',1);
if(!isset($func))
  goto exit_it;
  
switch ($func){
      case 1: 
            $res = array();
            $l='';
            $date = @$_POST['date'];
            $reseller = @$_POST['reseller'];
            $cat = @$_POST['cat'];
            $kpinames = $cat_arr['kpis'][$cat];
            $table = $_SESSION['tables'][$cat];
            $hour = @$_POST['hour'];
            $temp = $data = array();
            $kpis = array();
            $kpinames = explode(',', $kpinames);
            foreach ($kpinames as $kpi) {
              $kpis[] =  str_replace(array(':0',':1',':H'),'',$kpi);
            }

            $kpi_values_array=array();
            $kpi_values_array_max=array();
            foreach ($kpis as $kpi) {
              $work_order_logic_key = "$node_type-$reseller-".make_string_column($kpi);
            
                $where = "node_name='{$reseller}' and kpi_name = '".make_string_column($kpi)."'";
              $wo_qry = "select work_order_id from work_order where $where and kpi_date like '$date {$_SESSION['max_time'][$cat]}%'";
              $wo_id = execute_query_one_value($wo_qry);
              if ($wo_id)
                $ticket = $wo_id;
              else
                $ticket = '';

              $label = $cat.' - '.$reseller;
              $kpi = make_string_column($kpi);
              
                $qry = "select $kpi,node_name,date,time from $table where date = '$date' and date_format(time,'%H') <= $hour and node_name like '%$reseller%' order by node_name,time";
              $result = execute_query($qry);
              while ($r=mysqli_fetch_assoc($result)) {
                if ( stristr($kpi, 'data') )
                {
                  $t = $r[$kpi];
                  if (!isFieldNull($t)){
                    
                    if ( $t >= 1024 && $t < 1024*1024 )
                    {
                      $t = round($t/1024,2);
                      $l = 'KB';
                    }
                    elseif ( $t >= 1024*1024 && $t < 1024*1024*1024 )
                    {
                      $t = round($t/(1024*1024),2);
                      $l = 'MB';
                    }
                    else{
                      $t = round($t/(1024*1024*1024),2);
                      $l = 'GB';
                    }
                  }

                  $r[$kpi] = $t;
                }            

                $data[$r['date'].' '.$r['time']] = $r[$kpi];                
                $kpi_values_array[]=$r[$kpi];
                $kpi_values_array_max[]=$r[$kpi];
              }
            
            if (count($data) > 0){                
                             
            $thr_max = -99;
            $thr_min = -99;
              $thrkey='<%THRSEND%>'.$thr_min.'<%THRSEP%>'.$thr_max;
              $data_send='';
              $i=0;
              
                  $data_send.='{name:\''.make_column_string($kpi).'\',' . 'data:[';
                  $kpi_values_array=array();
                  ksort($data);
                  foreach ($data as $date_value => $value) {
                    $pointId='pointId'.$i++;            
                    $dateLong=strtotime(date('Y-m-d H:i',strtotime($date_value)));
                    $dataLabel='';$name=",name:'".date('\W\e\e\k W, l, F, jS Y, H:i:s',$dateLong)."'";
                   
                    $events='';
                    $redit='';

                    if (!is_numeric($value)){
                      if (in_array($kpi, $_SESSION['exclude_from_null'])){
                        $min = date('i',strtotime($date_value));
                        // var_dump($min);
                        if (intval($min) == 0)
                          $value = 'null';
                        else
                          continue;
                      }
                      elseif (stristr($kpi, 'portin_failed_duplicate'))
                        $value = '0.00';
                      else
                        $value = 'null';

                    }
                    else
                      $value = number_format($value,2,".","");
                   
                    $redit=',marker:{enabled:true,lineWidth:1,radius:3,lineColor:"#FFFFFF"}';
                    $data_send.='{id:"'.$pointId.'",x:'.(($dateLong+$dateLongDelta*60*60) * 1000).',y:'.$value.$name.$dataLabel.$redit.$events.'},';
                   
                  }
                  $data_send=substr($data_send,0,strlen($data_send)-2);
                  $data_send.=",dataLabels:{enabled:true,style:{fontWeight:'bold'}}}";

                  $data_send.="]},";
             
                $data_send=substr($data_send,0,strlen($data_send)-1);
                $kpi_values_array_max[]=$thr_max;
                $max=max($kpi_values_array_max);
                if ($l!='')
                  $res[$kpi] = $label.';'.$l.';'.$ticket.'<%MAXSEND%>'.$max.$thrkey.'<%DATASEND%>'.$data_send;
                else
                  $res[$kpi] = $label.';;'.$ticket.'<%MAXSEND%>'.$max.$thrkey.'<%DATASEND%>'.$data_send;


            }
          }
            echo json_encode($res);
            break;         
        case 2: 

            $date = $_POST['date'];
            $hour = $_POST['hour'];

            $file = "BSCS_STUCK_REPORT_{$date}-{$hour}.csv";
            $path = $bscs_stuck_report_dir.DS.$file;
            if (file_exists($path)){

              $content = file_get_contents($path);  
              $file_content = '<?php header("Content-type: application/vnd.ms-excel");header("Content-Disposition: attachment;Filename='.$file.'"); ?>'.$content;
              $file = 'Data/download_report_'.session_id().'.php';
              $chk = file_put_contents($file, $file_content);         
              echo ROOT_URL.$file;
              
            }
            else
              echo '';
            break; 
}

if(isset($func))
  myExit();
exit_it:
$meta = $exclude_from_null = array();
  $out = array();
  unset($_SESSION['exclude_from_null']);
  unset($_SESSION['tables']);
  unset($_SESSION['max_time']);
  unset($_SESSION['datetime']);
foreach ($cat_arr['kpis'] as $cat => $kpinames) {
  $kpis = $dataArr = array();
  $kpinames = explode(',', $kpinames);
  foreach ($kpinames as $kpi) {
    if (strstr($kpi, ':H'))
      $_SESSION['exclude_from_null'][] = make_string_column(str_replace(array(':0',':1',':H'),'',$kpi));
    $kpis[] =  str_replace(array(':0',':1',':H'),'',$kpi);

  }
  $kpi_col = "'".implode("','", $kpis)."'";
  $query="select node_type,cp from node_tbl, kpi_tbl where kpi in ($kpi_col) and kpi_tbl.node_type_id=node_tbl.node_type_id limit 1";
  // echo $query;
  $row = execute_query_one_row($query);
  $node_type = $row['node_type'];
  $cp = $row['cp'];
  $table = make_string_column($node_type.'-'.$cp);
  $_SESSION['tables'][$cat] = $table;
  
  //GET NODE TYPE WISE DATE/HOUR
  $timestamp_delta = $node_type == 'EMM' ? $dashboard_frontend_delta_emm : $dashboard_frontend_delta;
  $db_timestamp = $timestamp-$timestamp_delta*60;
  $date = date('Y-m-d',$db_timestamp);
  $hour = date('H',$db_timestamp);
  $_SESSION['datetime'][$cat] = $date.';'.$hour; 

  // GET MAX TIME
  $date_qry = "select max(time) as time from $table where date like '$date' and hour(time) = $hour order by time desc limit 1";
    $res = execute_query($date_qry);
    if ($res)
    while ($r=mysqli_fetch_assoc($res)) {
      $max_time = substr($r['time'],0,5);
      if (!isset($_SESSION['max_time'][$cat]))
        $_SESSION['max_time'][$cat] = $max_time;
    }

  $kpi_list = array();
  foreach ($kpis as $kpi) {
    $kpi = make_string_column($kpi);

    $qry = "select $kpi,node_name,date,time from $table where date = '$date' and time like '{$_SESSION['max_time'][$cat]}%' and node_name !='' order by node_name, time desc";
    $res = execute_query($qry);
    while ($r=mysqli_fetch_assoc($res)) {
      if (stristr($kpi, 'portin_failed_duplicate') && !is_numeric($r[$kpi]))
          $r[$kpi] = '0.00';
      $dataArr[$r['node_name']][$cat][$node_type.';'.$kpi][$r['date'].' '.$r['time']]=$r[$kpi];
      $meta[$r['node_name']] = $r['node_name'];
    }
      $kpi_list[] = $kpi;
  }
  $kpi_list = "'".implode("','", $kpi_list)."'";  
  $excp_arr = getExceptionArr($kpi_list,$date,$_SESSION['max_time'][$cat]);//continue;
  foreach ($dataArr as $node_name => $nodeArr) {
    foreach ($cat_arr['kpis'] as $cat => $kpis) { 
      $key = $cat;
      if ( isset($nodeArr[$cat]) )
      {  
        $out[$node_name][$key] = 'fa fa-check-circle fa-5;'.$_SESSION['datetime'][$cat];
        foreach ($nodeArr[$cat] as $node_type_kpi => $arrbrk) {
          $node_type_kpi_arr = explode(";", $node_type_kpi);
          $node_type = $node_type_kpi_arr[0];
          $kpi = $node_type_kpi_arr[1]; 

          $chk = isset($excp_arr[$kpi][$node_name]);   
          if ($chk == true)
          {
            $out[$node_name][$key] = 'fa fa-exclamation-triangle fa-5;'.$_SESSION['datetime'][$cat];
            break 2;

          }                
          elseif (isFieldNull($arrbrk[$date.' '.$_SESSION['max_time'][$cat].':00'])){           
            if (!in_array($kpi, $_SESSION['exclude_from_null'])){
                $out[$node_name][$key] = 'fa fa-exclamation-circle fa-5;'.$_SESSION['datetime'][$cat];                 
            }            
          }            
        }
      }
      elseif (!isset($out[$node_name][$key]))
        $out[$node_name][$key] = 'fa fa-ban;'.$_SESSION['datetime'][$cat];
    }

  }
  asort($meta);
  $_SESSION['resellers'] = $meta;
}

function getExceptionArr($kpis,$date,$hour){
  global $date;
  $temp = array();
    $qry = "select work_order_id,node_name,kpi_name,work_order_logic_key,kpi_date from work_order where kpi_name in ({$kpis}) and kpi_date like '{$date} {$hour}%' group by node_name order by node_name,kpi_date desc";
    $result = execute_query($qry);
    while($row = mysqli_fetch_assoc($result)){
      $temp[make_string_column($row['kpi_name'])][$row['node_name']] = $row['work_order_id'].';'.$row['work_order_logic_key'].';'.$row['kpi_date'];
    }
  return $temp;
}

?>