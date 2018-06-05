var host= $(location).attr('host');
var path= $(location).attr('pathname');
path = path.substr(0,path.indexOf('spi_trend')); 

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
var counter="";
var chart_hour="";
var chart_date=cur_date;
var out_width = $( '#wrapper' ).width();
var div_width=0;
var chart_width=0;
var tile_class="tile";
var module='';
function displayOptions(id,flag,date,mod){
  module=mod;
  switch(id){
    case 0:
    if ( !flag )
      showVals(0,flag,date);
    break;      
  }
}


function showVals(id,flag,date)
{
  switch(id){
    case 0:
    if (flag)
      $('#dashBoard').slideUp();
    cust="";
    $("#navsel0 option:selected").each(function() {
       cust+=$(this).val()+",";
    }); 
    cust=cust.substr(0,cust.length-1);
    chk1=drawColumnChart(0,cust,date);   
    $('#dashBoard').slideDown();
    if(flag)
      $('#trendTbl').slideUp();       
    break;   
  }
}

function showTcTiles(cust, chart_hour)
{
	params="func=0&cust="+cust+"&chart_hour="+chart_hour+"&chart_date="+chart_date;
	ajaxCall(params,"post","spi_trend.php",null,0,false,1,0,function(res) {
		var obj = jQuery.parseJSON( res );
		$.each(obj,function(index,value){
			parts=index.split(';');
			index=parts[0];
			tc=parts[1];
			
			if (value=='N/A')
				$('#'+index).hide();               
			else{
				  $('#'+index).parents("a").attr('href','spi_trend_tc.php?module='+module+'&tc='+tc+'&hour='+chart_hour+'&date='+chart_date); 
				  plot_gauge(index,tc,value);
			}
		});
	});
}

function plot_gauge(id,tc,value){
  var v = eval('['+value+']');
   $('#'+id).highcharts({
        chart: {
            type: 'gauge',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            borderRadius: 0,
            borderWidth:0,    
            margin: 30,
            backgroundColor: 'transparent',
        },
        credits:{
          enabled: false
        },

        title: {
            text: tc
        },

        pane: {
            startAngle: -150,
            endAngle: 150,
            background: [{
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#FFF'],
                        [1, '#333']
                    ]
                },
                borderWidth: 0,
                outerRadius: '109%'
            }, {
                backgroundColor: {
                    linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                    stops: [
                        [0, '#333'],
                        [1, '#FFF']
                    ]
                },
                borderWidth: 1,
                outerRadius: '107%'
            }, {
            }, {
                backgroundColor: '#DDD',
                borderWidth: 0,
                outerRadius: '105%',
                innerRadius: '103%'
            }]
        },

        yAxis: {
            min: 0,
            max: 100,

            minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 30,
            tickWidth: 2,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto'
            },
            title: {
                text: 'SR %'
            },
            plotBands: [{
                from: 95,
                to: 100,
                color: '#55BF3B' // green
            }, {
                from: 90,
                to: 95,
                color: '#DDDF0D' // yellow
            }, {
                from: 0,
                to: 90,
                color: '#DF5353' // red
            }]
        },

        series: [{
            name: tc,
            data: [v],
            tooltip: {
                valueSuffix: ' %'
            }
        }],
        exporting:{
          enabled: false
        }

    });
}

function showTc(tc,chart_hour,date)
{
	if (tc=='') {
		var host= $(location).attr('host');
		var path= $(location).attr('pathname');
		path = path.substr(0,path.indexOf('spi_'));
		window.location.href='http://'+host+path+'spi_trend.php';
	}
	traf_case=tc;
	params="func=0&tc="+tc+"&chart_hour="+chart_hour+"&date="+date;
	ajaxCall(params,"post","spi_trend_tc.php",null,0,false,1,0,function(res){
		var obj = jQuery.parseJSON( res );    
		var min=getMin(obj);
		$.each(obj,function(index,value){
			parts=index.split('_');
			node_type=parts[0];
			if ( value == min && value != 100)
				tile_class=node_type+"_red";
			else if ( value == 100 )
				tile_class=node_type+"_green";
			else 
				tile_class=node_type+"_yellow";
			$('#'+index).closest('div').addClass(tile_class);
			value = value == "N/A" ? value : value+'%';
			$('#'+index).html(value);
			if (value=='N/A')
			{
				$('#'+index).closest("div[class*='span']").hide();
			}
		});
		chk2=drawTrend(0,tc,date,chart_hour);     
	});
}

function showNode(flag,tc,node,hour,date,mod)
{
    module=mod;
    if (tc=='')
    {
      var host= $(location).attr('host');
      var path= $(location).attr('pathname');
      path = path.substr(0,path.indexOf('spi_'));
      window.location.href='http://'+host+path+'spi_trend.php';
    }


if (flag)
  {
    counter="";       
    $("#navsel1 option:selected").each(function() {
       counter+=$(this).val()+"','";
    }); 
    counter=counter==''? '' : "'"+counter.substr(0,counter.length-2);
    if(counter==""){
      alert("Please select a counter!");
      return;
    }
    if($('#chart_hour').val()=='' || $('#chart_hour').val()==null || $('#chart_hour').val()==undefined){
      alert("Please select an hour value!");
      return; 
    }
    chart_hour=$('#chart_hour').val();
  }
  else
  {
    counter="";
    chart_hour=hour;
    $('#chart_hour').val(cur_hour);     
  }
  parts=node.split('_');
  node_type=parts[0].toLowerCase();
  $('#errorLogBtn').parents("a").attr('href','spi_'+node_type+'_analytics.php?module='+module+'&tc='+tc+'&node='+node+'&hour='+chart_hour+'&date='+date); 

    traf_case=tc;
    traf_node=node;
    params="func=0&tc="+tc+"&node="+node+"&counter="+counter+"&chart_hour="+chart_hour+"&date="+date;
	ajaxCall(params,"post","spi_trend_node.php",null,0,false,1,0,function(data){
		data=data.split('<%SEP%>');
		if(data.length!=3)
			return;
		chk = !(data[0]==']');   
		
		if (!chk && !flag)
		{
			$('#pieDiv').hide();
			$('#tabDiv').hide();
		} 
		else if(!chk && flag)
		{
			alert("No data exists for the current selection!");
			return;
		}
		else if (chk)
		{
			data[0]=eval("["+data[0]+"]");
			series=data[0];
			data[1]=eval("["+data[1]+"]");
			drilldown=data[1];
			
			div_width = $('.span12 .widget-body').css('width');
			if (div_width.match(".*px$")) {
				chart_width = div_width;
				chart_width = parseInt(chart_width.replace("px",''))-15;
			} else  {
				div_width = parseInt(div_width.replace("%",''));
				chart_width = out_width * div_width * 0.01 - 50;
			}
			plot_pie_graph(series,drilldown,chart_hour,chart_width,date,tc,node);
			series = data[2]; 
			plot_tab_graph(series,chart_hour); 
		}
		if (!flag)
		{
			params="func=1&tc="+tc;
			ajaxCall(params,"post","spi_trend_node.php",document.getElementById('navsel1'),0,false,1,0); 
		}
		
	});
}

function plot_tab_graph(series,chart_hour){
  $('#tabTitle').html("<a href='#tabTitle'>Tabular Data</a>");
  $('#tabDiv').html(series);
}

function plot_pie_graph(series,drilldown,chart_hour,width,date,tc,node){ 
  $('#pieTitle').html("Counter Info for "+date+" for "+getOrdinal(chart_hour)+" hour");
  $('#pieDiv').highcharts({
        chart: {
            type:'pie',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            
            width:width
        },
       
        credits:{
          enabled:false
        },
        title:{
          text:traf_case+' - '+traf_node
        },
        tooltip: {
          headerFormat: ' <b>{point.key}</b><br>',
          pointFormat: 'Count <b>{point.percentage:.2f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(2) +' %';
                    }
                },
                
          },
          series:{
             colorByPoint: true
          }
        },
        series:series,
        drilldown: {
          series:drilldown,
        },
         exporting: {
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },
  });
}

function drawColumnChart(flag,cust,date) {
  if (flag)
  {
    if($('#chart_hour').val()=='' || $('#chart_hour').val()==null || $('#chart_hour').val()==undefined){
      alert("Please select an hour value!");
      return; 
    }
    chart_hour=$('#chart_hour').val();
    if($('#chart_date').val()=='' || $('#chart_date').val()==null || $('#chart_date').val()==undefined){
      alert("Please select a date value!");
      return; 
    }
    chart_date=$('#chart_date').val();
  }
  else
  {
    chart_hour=cur_hour;
    $('#chart_hour').val(cur_hour);
    chart_date=date;
  }

  
  
    div_width = $('.span12').css('width');
    if (div_width.match(".*px$")) {
      chart_width = div_width;
      chart_width = parseInt(chart_width.replace("px",''))-15;
    } else  {
       div_width = parseInt(div_width.replace("%",''));
     chart_width = out_width * div_width * 0.01 - 50;
    }

  params="func=1&cust="+cust+"&chart_hour="+chart_hour+"&chart_date="+chart_date;
  
	ajaxCall(params,"post","spi_trend.php",null,0,false,1,0,function(series){
		chk = plot_chart(series,true, 'column_chart', 'column',chart_width,cust);
		if (!chk && !flag)
			$('#columnDiv').hide();
		else if(!chk && flag)
		{
			alert("No data exists for the current selection!");
			return;
		}
		else
		{
			$('#columnTitle').html("Traffic Case wise Attemp count for "+chart_date+" for "+getOrdinal(chart_hour)+" hour");
			$('#columnDiv').show();   
		}
	});  

}

function plot_chart(data,animation, cont, type,width,cust){
  metasendpos=data.indexOf('<%METASEND%>') + 12 ;
 
  datasendpos=data.indexOf('<%DATASEND%>');
  hourpos=data.indexOf('<%HOUR%>');
 

  meta = data.substr(metasendpos,datasendpos-12);
  
  meta=eval('['+meta+']');
  chart_hour=data.substr(hourpos+8);
  showTcTiles(cust,chart_hour);
  series=data.substr(datasendpos+12,hourpos-datasendpos-12);
  if (series.length == 0)
    return false;
  series=eval('['+series+']');
 container = $('#'+cont);
  container.highcharts({
        chart: {
            type: type,          
            height: 300,
            width:width
        },
        title: {
            text: 'Service Performance Analysis'
        },
        xAxis: {
            categories: meta,
            minorGridLineWidth:0          
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Number of attempts'
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
            x: -30,
            verticalAlign: 'top',
            y: 25,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
            borderColor: '#CCC',
            borderWidth: 1,
            shadow: false
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.x + '</b><br/>' +
                    this.series.name + ': ' + this.y + '<br/>' +
                    'Total: ' + this.point.stackTotal;
            }
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    style: {
                        textShadow: '0 0 3px black'
                    }
                }
            },
            series: {
                stacking: 'normal'
            }
        },
         exporting: {
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },
        colors: [
      '#a9e185',
      '#e75555',
      '#f4df62',
      '#ff988c',
        '#6cdcdf'
        ],
        series: series
    });
  return true;
}

function drawTrend(flag,tc,date,hour){
  if (flag)
  {
    node="";       
    $("#navsel1 option:selected").each(function() {
       node+=$(this).val()+"','";
    }); 
    node=node==''? '' : "'"+node.substr(0,node.length-2);
    if(node==""){
      alert("Please select a node!");
      return;
    }
     if($('#fromdate').val()=='' || $('#fromdate').val()==null || $('#fromdate').val()==undefined){
      var d = new Date();
      d.setDate(d.getDate());
      $( "#fromdate" ).datepicker({ dateFormat: "yy-mm-dd" }).datepicker('setDate',d); 
    }
    fromdate=$('#fromdate').val();
    todate=$('#todate').val();
    if(fromdate > todate){
      alert("From date cannot be greater than To date!");
      return;
    }
  }
  else
  {
    node="";
    fromdate="";
    todate="";
    params="func=2&tc="+tc;
    ajaxCall(params,"post","spi_trend_tc.php",document.getElementById('navsel1'),0,false,1,0);   
  }
  params="func=1&tc="+tc+"&node="+node+"&fromdate="+fromdate+"&todate="+todate+"&date="+date;
  
	ajaxCall(params,"post","spi_trend_tc.php",null,0,false,1,0,function(series){
		chk = plot_trend(series,true);
		
		if (!chk && !flag)
			$('#trendDiv').hide();  
		else if(!chk && flag)
		{
			alert("No data exists for the current selection!");
			return;
		}
		else if(!flag)
			$('#trendTitle').html(tc+": Node wise trend for Success Rate for "+date+" till "+getOrdinal(hour)+" hour");
		else
		{
			$('#trendDiv').show();
			$('#trendTitle').html(tc+": Node wise trend for Success Rate from "+fromdate+" to "+todate);
		}
	});
}

function plot_trend(series,animation){
  plotLines=null;
  series=eval('['+series+']');
  if (series.length == 0)
    return false;
  if(series.length==1)
    chart_type='areaspline';
  else
    chart_type='spline';
 
chart = $('#trend').highcharts({
    chart:{
      animation:animation,
      type: chart_type,
      height: 250,
      zoomType:"xy",     
    },
     title: {
            text: ''
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
      enabled:true,
      layout:'vertical',
      align: 'right',
      verticalAlign: 'middle',
      maxHeight:300,
      backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
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
      minorGridLineColor: '#F0F0F0',
      plotLines:plotLines,
      title: {
            enabled: true,
            text: 'Success Rate (%)'
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
    series : series,
     exporting: {
            url: 'http://'+host+path+'exporting_server/index.php',
            sourceWidth: 1024,
            sourceHeight: 768,
            scale:2
        },

  });
 return true;
}

function selectallfunc(id){
  switch(id){
    case 1:
      $('#navsel1 option').prop('selected',true);
      displayOptions(1);
    break;
    case 2:
      $('#navsel2 option').prop('selected',true);
      displayOptions(2);
    break;
    
  }
}

function closeFilter(id){
  $('#filterDiv'+id).slideUp();
}
function showFilter(id){
    $('#filterDiv'+id).slideToggle();
}

$('#trendOpt').click(function(){
    $('#trendTbl').slideToggle();
});

$( "#fromdate" ).datepicker({ dateFormat: "yy-mm-dd"}).datepicker("setDate", new Date());    
$( "#todate" ).datepicker({ dateFormat: "yy-mm-dd" }).datepicker("setDate", new Date());
$( "#chart_date" ).datepicker({ dateFormat: "yy-mm-dd" }).datepicker("setDate", new Date());


$("#chart_hour").timepicker({
     timeFormat : "HH",
    defaultValue: chart_hour    
});

function getOrdinal(n) {
    var s=["th","st","nd","rd"],
    v=n%100;
    return n+(s[(v-20)%10]||s[v]||s[0]);
 }
 
 function getMin(obj)
 {
  var min = Number.POSITIVE_INFINITY;
  var max = Number.NEGATIVE_INFINITY;
  var tmp;
  $.each(obj,function(index,value){
      tmp = value;
      if (tmp < min) min = tmp;
      if (tmp > max) max = tmp;
  });
  return min;
 }
