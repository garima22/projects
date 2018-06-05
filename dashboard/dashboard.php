<?php include "_dashboardAjax.php"; ?>
<html>
<head>
    <meta charset="utf-8">
    <title>
      WME Dashboard
    </title>

    <link href="css/main.css" rel="stylesheet">
    <link href="css/table.css" rel="stylesheet" type="text/css" />
    <link href="css/theme.css" rel="stylesheet" type="text/css" />
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/pace.css" rel="stylesheet" />
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="img/Graph-icon.png"/>
    <style>
        body{background: none}
        .imageButton {
            height: 30px!important;
            width: 30px
        }
    </style>
</head>
<body>
<div class="white_overlay"></div>
    <?php include('sections/mainbar.php'); ?>
    
    <div class="clear"></div>
  <div id="wrapper">
        <div class="row">
                <div class="col-lg-6">
                    <span class="heading">
                        <img src="img/Graph-icon.png">
                        <p style="font-weight:normal">WME Dashboard</p>
                    </span>
                </div>
                <div class="col-lg-6" >
                    <span class="heading" style="float: right">
                        <a href="resellerKPIThresholdSetting.php" target="_blank">
                            <img src="img/settings.png" 
                                 id="setResellerThrModal"
                                 class="imageButton"
                                 title="Set Reseller Threshold Factor" /></a>
                        </span>
                </div>
        </div>

        <div class="clear"></div>    
    <div class="container-fluid" id="dashBoard">
      <div class="dashboard-wrapper">
        <div class="main-container">

          
 <div class="row-fluid">
    <div class="span2"></div>
<?php $catarr = array_keys($cat_arr['kpis']);
foreach ($catarr as $cat) {?>
  <div class="span1">
      <span class="label" ><?php echo trim($cat) ?></span>            
    </div>

<?php } ?>
 </div>         

<?php  foreach ($out as $reseller => $resArr) { ?>
      <div class="row-fluid row-fluid-border">

            <div class="span2">
              <div class="widget" style="background:none; text-align:left">
                <div class="widget-header" style="background:none; border:none; text-align:left">
                  <span class="res_img">
                  <?php   $base = "img/reseller_logo/";
                          $src = file_exists($base.make_string_column($reseller).'.png') ? $base.make_string_column($reseller).'.png' : $base.make_string_column($reseller).'.jpg';
                          if (file_exists($src))
                             echo "<img src='{$src}' width='40' height='40' alt=''/>"; ?>
                  </span>
                  <div class="title"><?php echo trim($reseller); ?></div>                 
                </div>
              </div>
              
          </div>
          <?php foreach ($resArr as $cat => $label) {
                   $parts = explode(';', $label);
                  $label = $parts[0];
                  $date = $parts[1];
                  $hour = $parts[2]; ?>
           
          <div class="span1">
            <?php if (!strstr($label, 'fa-ban'))
                    $event = 'onclick="showTrends(\''.trim($reseller)."','".trim($cat)."','".$date."','".$hour.'\')"';
                  else
                    $event='';   ?>
            <i class="<?php echo trim($label) ?>"  <?php echo $event ?>  aria-hidden="true"></i>            
          </div>
          <?php } ?>

          
      
      </div>
<?php } ?>      

  <div class="row-fluid" id="graphView" style="display:none">        
    <fieldset>
      <legend align="center"><img src="img/TrendingIcon.png" height="30" width="30" />  KPI trends <a href="trending-reseller.php" target="_blank"><img src="img/popout-icon.png" width="16" height="16" style="float:right;margin:7px"/></a></legend>
        <div id="trends">
          <div id="container" style="display:none"></div>             
        </div>
    </fieldset>
      </div>
  </div>
</div>
<a href="#" class="back-to-top" style="display: inline;"><i class="fa fa-arrow-circle-up"></i></a>
<script src="js/jquery-1.7.2.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="js/pace.min.js"></script>
<script src="js/ajax_script.js"></script>
<script src="js/dashboard.js"></script>
<script src="js/HighChart/highcharts.js"></script>
<script></script>

</body>

</html>

