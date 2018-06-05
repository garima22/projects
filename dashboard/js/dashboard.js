var host= $(location).attr('host');
var path= $(location).attr('pathname');
path = path.substr(0,path.indexOf('dashboard')); 

var d = new Date();
var hour = d.getHours();
var cur_hour = hour==0 ? 23 : hour-1;
var month = d.getMonth()+1;
var day = d.getDate();
var cur_date = d.getFullYear() + '-' +(month<10 ? '0' : '') + month + '-' +(day<10 ? '0' : '') + day;
var chart_date=cur_date;
var out_width = $( '#wrapper' ).width();
var div_width=0;
var chart_width=0;

function showWoData(date){
    chart_date = date;
    params="func=0&date="+date;
    res1 = ajaxCall(params,"post","dashboard.php",null,0,false,1,0);
     var obj = jQuery.parseJSON( res1 );
    $.each(obj,function(index,value){      
      var parts = index.split(';');
      if (value == '')
            $('#pie_'+parts[0]).parent('div').hide();
      else
        plot_pie_graph(parts[0],value,parts[1]);
       
    });     
}

function showTrends(reseller,cat,date,hour){
     
    chart_date = date;
    params="func=1&date="+chart_date+"&hour="+hour+"&reseller="+reseller+"&cat="+cat;    
    res=ajaxCall(params,"post","dashboard.php",null,0,false,1,0);
    $('#trends').html('<div id="container" style="display:none"></div>');
    $('#graphView').show();

    var obj = jQuery.parseJSON(res);
    var prev = '';
    $.each(obj,function(index,value){
      
      if (prev=='')
        target = 'container';
      else
        target = prev;
      $('<div id="'+index+'"></div>').insertAfter('#'+target);
      $('#'+target).after('<div id="'+index+'"></div>');
      prev = index;
      plot_trend(value,true,index);
       
    });  



    $('html, body').animate({
        scrollTop: $("#graphView").offset().top
    }, 500);     
}

function plot_trend(series,animation,id){
  var colors = ['#ff7930','#509db4', '#f4df62', '#ff988c', '#6cdcdf', '#7f81f9', '#a9e185', '#54b42d'];
  shuffle(colors);
  plotLines=null;
  maxsendpos=series.indexOf('<%MAXSEND%>') + 11 ;
  thrsendpos=series.indexOf('<%THRSEND%>') + 11;
  thrseprpos=series.indexOf('<%THRSEP%>') + 10;
  datasendpos=series.indexOf('<%DATASEND%>') + 12;
  label = series.substr(0,maxsendpos - 11);
  parts = label.split(';');
  label=parts[0];
  l = parts[1];
  ticket = parts[2];
  if (ticket != ''){

    subtitle = '<font style="color:red; font-weight:bold">Work order generated - <a style="text-decoration:underline; font-weight:bold" target="_blank" href="http://'+host+path+'work_flow.php?t='+ticket+'">T'+padLeft(ticket,6)+'</a></font>';
  }
  else
    subtitle = '';
  if (l!='')
    l=' ('+l+')';
  max = parseFloat(series.substr(maxsendpos,thrsendpos - maxsendpos - 10)) + 10;
  if (max=='' || max<=0)
    max=null;
  thr_min=parseFloat(series.substr(thrsendpos,thrseprpos - thrsendpos - 9));
  thr_max=parseFloat(series.substr(thrseprpos ,datasendpos -thrseprpos - 11));
 
  series=series.substr(datasendpos);
  series=eval('['+series+']');
  if (series.length == 0)
    return false;
  type='spline'; 
 
chart = $('#'+id).highcharts({
    chart:{
      animation:animation,
      type: type,
      height: 250,
      zoomType:"xy",     
    },
     title: {
            text: label
        },
    subtitle: {      
      text: subtitle,
      useHTML: true
    },
    plotOptions:{
      areaspline:{
        marker:{
          enabled:false
        },
        fillColor : {
          linearGradient : {
            x1: 0, 
            y1: 0, 
            x2: 0, 
            y2: 1
          },
          stops : [[0, Highcharts.getOptions().colors[0]], [1, 'rgba(0,0,0,0)']]
        },
        turboThreshold: 50000     
      },
      spline:{
        marker:{
          enabled:false
        },
        turboThreshold: 50000
      },
      series:{
        dataLabels:{
          align:'right'
        }
      }
    },
    scrollbar:{
      enabled:false
    },
    rangeSelector : {
      enabled:false
    },
    navigator: {
          enabled: false,
          xAxis:{
        minorGridLineColor: '#F0F0F0',    
        minorTickInterval: 'auto',  
          type: 'datetime',
          
      },

      },
    credits:{
      enabled:false
    },
    legend:{
      enabled:true,
      layout:'horizontal',
      align: 'center',
      verticalAlign: 'bottom',
      maxHeight:300,
      backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
    },


    tooltip:{     
      crosshair:true,
      shared:false,
      pointFormat: '{series.name}: <b>{point.y}'+l+'</b>',
    },
    yAxis:{
      showLastLabel:true,
      labels:{
        align:'right',
        y:4,
        x:-8
      },
      min:0,
      minorGridLineColor: '#F0F0F0',
      plotLines:plotLines,
      title: {
            enabled: true,
            text: 'Count'+l
        }
      
    },
    xAxis:{
      minorGridLineColor: '#F0F0F0',    
      minorTickInterval: 'auto',
       title: {
            enabled: true,
            text: 'Timestamp'
        },
        type: 'datetime',
       
    },   
     colors: colors, 
    series : series,
     exporting: {
            enabled:false,
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },

  });

}

function plot_pie_graph(id,series,name){ 
  var colors = ['#6cdcdf', '#a9e185', '#f4df62', '#ff988c', '#ff7930', '#7f81f9', '#509db4', '#54b42d', '#a73ae7'];
  series = eval("["+series+"]");
  $('#pie_'+id).highcharts({
        chart: {
            margin: 0,
            type:'pie',
            width:150,
            height:300
        },
     
        credits:{
          enabled:false
        },
        title:{
          text:name,
          style:{"font-family":"Arial","fontSize":"15px","font-weight":"bold"},
          verticalAlign:'top'
        },
        tooltip: {
          headerFormat: ' <b>{point.key}</b><br>',
          pointFormat: 'Count: <b>{point.y}</b>'
        },
         legend:{
          enabled:true,
          layout:'horizontal',
          align: 'center',
          verticalAlign: 'bottom',
          
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    distance:-10,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.y +'</b>';
                    }
                },
                showInLegend: true,
                point:{
                      events: {
                        click: function () {
                                window.open('work_flow.php?r='+name+'&s='+this.name);
                    }
                  },
                  
                }
          }
        },
         colors: colors,
        exporting:{
          enabled:false
        },
        series: series
  });
}

function showOverlay()
{
  $('.white_overlay').show();
}

function removeOverlay()
{
  $('.white_overlay').fadeOut();
}

Pace.on('done', function() {
    removeOverlay();
});

function shuffle(a) {
    var j, x, i;
    for (i = a.length; i; i -= 1) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
}

$(function () {
    var offset = 250;
    var duration = 300;   
    $(window).scroll(function() {   
    if ($(this).scrollTop() > offset) {   
      $('.back-to-top').fadeIn(duration);   
    } else {   
      $('.back-to-top').fadeOut(duration);   
    } 
  });
   
  $('.back-to-top').click(function(event) {  
    event.preventDefault();
    $('html, body').animate({scrollTop: 0}, duration); 
    return false; 
  });
  
  refreshAt(00,10);
  refreshAt(10,0);
  refreshAt(30,10);
  refreshAt(40,0);
});

$( ".row-fluid-border" ).hover(
  function() {
    $(this).find(".widget").css("border","none");
  }, function() {
    $(this).find(".widget").css("border","1px solid #bfbfbf");
  }
);

function refreshAt(minutes, seconds) {
    var now = new Date();
    var then = new Date();
    if((now.getMinutes() > minutes) ||  now.getMinutes() == minutes && now.getSeconds() >= seconds) {
        then.setDate(now.getDate() + 1);
    }
    then.setMinutes(minutes);
    then.setSeconds(seconds);

    var timeout = (then.getTime() - now.getTime());
    setTimeout(function() { window.location.reload(true); }, timeout);
}

function padLeft(nr, n, str){
    return Array(n-String(nr).length+1).join(str||'0')+nr;
}
