var host= $(location).attr('host');
var path= $(location).attr('pathname');
path = path.substr(0,path.indexOf('analytics')); 

var d = new Date();
var hour = d.getHours();
var cur_hour = hour==0 ? 23 : hour-1;
var month = d.getMonth()+1;
var day = d.getDate();
var cur_date = d.getFullYear() + '-' +(month<10 ? '0' : '') + month + '-' +(day<10 ? '0' : '') + day;
var cust="";
var chk1="";
var chk2="";
var traf_case="";
var traf_node="";
var node_type='';
var counter="";
var chart_hour="";
var chart_date=cur_date;
var out_width = $( '#wrapper' ).width();
var div_width=0;
var chart_width=0;
var tile_class="tile";

function showCca(flag,node,hour,date)
{
   
    Pace.start();
    if (flag)
    {
     
      if($('#chart_hour').val()=='' || $('#chart_hour').val()==null || $('#chart_hour').val()==undefined){
        alert("Please select an hour value!");
        return; 
      }
      hour=$('#chart_hour').val();
      if($('#chart_date').val()=='' || $('#chart_date').val()==null || $('#chart_date').val()==undefined){
        alert("Please select a date value!");
        return; 
      }
      date=$('#chart_date').val();
    }
    parts=node.split('_');
    node_type=parts[0].toLowerCase();
    
   $('#dateTimeLbl').attr('value',date+', '+getOrdinal(hour)+' hour'); 

    
    if (node_type=='ccn'){

      params="func=0&node="+node+"&hour="+hour+"&date="+date;
      ajaxCall(params,"post","spi_"+node_type+"_analytics.php",document.getElementById('error_count'),0,false,1,0);

        if (typeof table1 !== 'undefined') table1.destroy();

        table1 = $('#error_info_tab').DataTable( {
            "processing": true,
            "paging":   false,
            "info":     false,
            "bFilter": false,
            "scrollX":        "200px",
            "scrollCollapse": true,
            "autoWidth": false,
            "ajax": {
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '1',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
             "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );  

        params="func=2&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res2){
          plot_donut(res2,'protocol_donut');
        });    

        params="func=3&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res3){
          plot_trend(res3,'error_trend',250);
        });    

        params="func=4&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res4){
          plot_pie(res4,'result_code_pie');       
        });    
        
        if (typeof table2 !== 'undefined') table2.destroy();
        table2=$('#result_code_tab').DataTable( {
            "processing": true,
            "paging":   false,
            "info":     false,
            "scrollY":        "200px",
            "scrollCollapse": true,
            "bFilter": true,
            "ajax": {
                "sAjaxDataProp":"",
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '5',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
            "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );

        params="func=6&node="+node+"&hour="+hour+"&date="+date;  
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res5){
          plot_chart(res5,true,'component_id', 'bar');
        });

        if (typeof table3 !== 'undefined') table3.destroy();
        table3=$('#main_session_id_tab').DataTable( {
            "processing": true,       
            "paging":   false,
            "info":     false,
            "scrollY":        "200px",
            "scrollCollapse": true,
            "bFilter": true,
            "ajax": {
                "sAjaxDataProp":"",
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '7',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
            "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );

        if (typeof table4 !== 'undefined') table4.destroy();
        table4=$('#error_code_tab').DataTable( {
            "processing": true,       
            "paging":   false,
            "info":     false,
            "scrollY":        "200px",
            "scrollCollapse": true,
            "bFilter": true,
            "ajax": {
                "sAjaxDataProp":"",
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '8',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
            "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );   

        params="func=9&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res6){
          plot_half_donut(res6,'error_type_donut');
        });    

        params="func=10&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res7){
          plot_pie(res7,'alert_type_pie');
        });    
    }
    else if (node_type=='air'){

         params="func=0&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",document.getElementById('error_count'),0,false,1,0);
      
        params="func=2&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res2){
          plot_chart(res2,true,'module_chart', 'column');
        });    

        params="func=3&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res3){
          plot_trend(res3,'error_trend1');
        });    

        params="func=8&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res6){
          plot_pie(res6,'event_pie');
        });    

        params="func=9&node="+node+"&hour="+hour+"&date="+date;
        ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res){
          plot_pie(res,'type_pie');
        });    

         if (typeof table3 !== 'undefined') table3.destroy();
        table3=$('#eventinfo_tab').DataTable( {
            "processing": true,       
            "paging":   false,
            "info":     false,
            "scrollY":        "200px",
            "scrollCollapse": true,
            "bFilter": true,
            "ajax": {
                "sAjaxDataProp":"",
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '10',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
            "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );    

    }
    else if(node_type=='sdp'){

      params="func=0&node="+node+"&hour="+hour+"&date="+date;
      ajaxCall(params,"post","spi_"+node_type+"_analytics.php",document.getElementById('error_count'),0,false,1,0);

      params="func=1&node="+node+"&hour="+hour+"&date="+date;  
      ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res1){
        plot_chart(res1,true,'module_trend', 'column');
      });

      params="func=2&node="+node+"&hour="+hour+"&date="+date;
      ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res2){
        plot_trend(res2,'error_trend1',150);
      });    

      params="func=3&node="+node+"&hour="+hour+"&date="+date;
      ajaxCall(params,"post","spi_"+node_type+"_analytics.php",null,0,false,1,0,function(res3){
        plot_pie(res3,'type_pie');
      });    

      if (typeof table5 !== 'undefined') table5.destroy();
        params="func=4&node="+node+"&hour="+hour+"&date="+date;
        table5 = $('#events_tab').DataTable( {
            "processing": true,
            "paging":   false,
            "info":     false,
            "bFilter": false,
            "scrollX":        "200px",
            "scrollCollapse": true,
            "autoWidth": false,
            "ajax": {
                "url": "spi_"+node_type+"_analytics.php",
                "type": "POST",
                "data": {
                   func: '4',
                   node: node,
                   date: date,
                    hour: hour               
                },
            },
             "aaSorting": [[1,'desc']],
            "bSort": true,
            "aoColumns": [{ "bSearchable": true },
                  { "bSearchable": false }],
        } );   
    }
}

function plot_donut(series,id){  
  series = eval("["+series+"]");
  $('#'+id).highcharts({
            chart: {
                renderTo: id,
                type: 'pie',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                borderRadius: 0,
                borderWidth:0,    
                margin: 0,
                backgroundColor: 'transparent',
                height:300

            },  
            credits: {
              enabled: false
            },  
            exporting: {
              enabled: false
            },
            legend: {
              enabled: true,
              layout:'horizontal',
          align: 'center',
          verticalAlign: 'top',
          
            },
            colors: [
              '#6cdcdf',
            '#ff988c',
            '#a9e185',
            '#e75555',
            '#f4df62',
              ],  
              title: false,               
            plotOptions: {
                pie: {
                    slicedOffset: 0,
                    shadow: false,
                    size:'100%',
                },
                series: {
                    size: '55%',
                    innerSize: '30%',
                    showInLegend:true,
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            series: series
        });
}

function plot_half_donut(series,id){  
  series = eval("["+series+"]");
  $('#'+id).highcharts({
            chart: {
                renderTo: id,
                type: 'pie',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                borderRadius: 0,
                borderWidth:0,    
                margin: 0,
                backgroundColor: 'transparent',
                height:150

            },  
            credits: {
              enabled: false
            },  
            exporting: {
              enabled: false
            },
            legend: {
              enabled: true,
              layout:'horizontal',
              align: 'center',
              verticalAlign: 'top',
              y:-15,
              borderWidth:0,
              itemStyle:{
                "fontSize":"10px"
              }
            },
            colors: [
            '#ff988c',
            '#a9e185',
            '#f4df62',
            '#e75555',
              '#6cdcdf'
              ],  
              title: false,               
            plotOptions: {
            pie: {
                dataLabels: {
                    enabled: false,
                    distance: -10,
                    style: {                        
                        color: '#333333',                      
                    },                                          
                },
                showInLegend: true,
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '75%']
            },
            series: {
                    type: 'pie',
                    name: 'Count',
                    innerSize: '50%',                    
                }
        },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            series: series
        });
}

function plot_trend(series,id,height){
  plotLines=null;
  series=eval('['+series+']');
  if (series.length == 0)
    return false;  
 
chart = $('#'+id).highcharts({
    chart:{
      animation:true,
      type: 'line',
      height: height,      
      renderTo: 'error_trend',              
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        borderRadius: 0,
        borderWidth:0,    
    
        backgroundColor: 'transparent',  
    },  
            credits: {
              enabled: false
            },
     title: {
            text: false
        },
    plotOptions:{
      areaspline:{
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
      enabled:false,
      layout:'vertical',
      align: 'right',
      verticalAlign: 'middle',
      maxHeight:300,
      backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
      borderWidth:0
    },

    tooltip:{     
      crosshair:true,
      shared:false,
    },
    yAxis:{
      showLastLabel:true,
      labels:{
        align:'right',
        y:4,
        x:-8
      },
      minorGridLineWidth:0,
      plotLines:plotLines,
      title: {
            enabled: true,
            text: 'Error Count'
        }
      
    },
    xAxis:{
      gridLineWidth: 0,
      minorGridLineWidth:0,      
      minorTickInterval: 'auto',
       title: {
            enabled: true,
            text: 'Timestamp'
        },
        type: 'datetime',
       
    },    
    series : series,
     exporting: {
            enabled: false,
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },

  });
 return true;
}

function plot_pie(series,div){ 

  series = eval("["+series+"]");
  $('#'+div).highcharts({
            chart: {
                renderTo: div,
                type: 'pie',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                borderRadius: 0,
                borderWidth:0,    
                margin: 0,
                backgroundColor: 'transparent',
                height:300

            },  
            credits: {
              enabled: false
            },  
            exporting: {
              enabled: false
            },
            legend: {
                enabled: true,
                layout:'vertical',
                align: 'left',
                verticalAlign: 'top',
                borderWidth:0,
                y:-10,
                x:-15,
                itemStyle:{
                  "fontSize":"10px"
                }
            },
            colors: [
            '#f4df62',
            '#a9e185',
              '#6cdcdf',
            '#ff988c',
            '#e75555',
              ],  
              title: false,               
            plotOptions: {
                pie: {
                    slicedOffset: 0,
                    shadow: false,
                    size:'100%',
                },
                series: {
                    size: '70%',
                    innerSize: '0%',
                    showInLegend:true,
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            series: series
        });
}

function plot_chart(series,animation, cont, type){
  metasendpos=series.indexOf('<%METASEND%>') + 12 ;
 
  datasendpos=series.indexOf('<%DATASEND%>');
 

  meta = series.substr(metasendpos,datasendpos-12);
  meta=eval('['+meta+']');
  series=series.substr(datasendpos+12);
  if (series.length == 0)
    return false;
  series=eval('['+series+']');
 container = $('#'+cont);
  container.highcharts({
        chart: {
            type: type,          
            height: 250,
             plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        borderRadius: 0,
        borderWidth:0,    
    
        backgroundColor: 'transparent',  
    },
    credits: {
              enabled: false
            },
     title: {
            text: false
        },
        xAxis: {
            categories: meta,
            minorGridLineWidth:0 ,        
        },
        yAxis: {

            min: 0,
            title: {
                text: 'Number of errors'
            },
            minorGridLineWidth:0,
            stackLabels: {
                enabled: true,
                style: {
                    fontWeight: 'bold',
                    color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                }
            }
        },
        legend: {
            align: 'right',
            x: 0,
            verticalAlign: 'top',            
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
            borderColor: '#CCC',
            borderWidth: 0,
            shadow: false
        },
        tooltip: {
            formatter: function () {
                return '<b>'+this.series.name+':'+this.y+'</b><br/>'; 
            }
        },
        plotOptions: {
            column: {
                
                dataLabels: {
                    enabled: false,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    style: {
                        textShadow: '0 0 3px black'
                    }
                }
            },
           
        },
         exporting: {
            enabled: false,
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },
        colors: [
            '#ff988c',
            '#f4df62',
            '#a9e185',
            '#6cdcdf',
            '#e75555',
              ],
        series: series
    });
  return true;
}

function closeFilter(id){
  $('#filterDiv'+id).slideUp();
}
function showFilter(id){
    $('#filterDiv'+id).slideToggle();
}
$( "#chart_date" ).datepicker({ dateFormat: "yy-mm-dd" }).datepicker("setDate", new Date());


$("#chart_hour").timepicker({
     timeFormat : "HH",
    defaultValue: hour    
});
$('#chart_hour').val(hour); 

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

function getOrdinal(n) {
    var s=["th","st","nd","rd"],
    v=n%100;
    return n+(s[(v-20)%10]||s[v]||s[0]);
 }