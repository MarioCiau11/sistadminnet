jQuery(document).ready(function(){
    
//     "use strict";

//     function showTooltip(x, y, contents) {
// 	jQuery('<div id="tooltip" class="tooltipflot">' + contents + '</div>').css( {
// 	    position: 'absolute',
// 	    display: 'none',
// 	    top: y + 5,
// 	    left: x + 5
// 	}).appendTo("body").fadeIn(200);
//     }
    
//     /*****SIMPLE CHART*****/
    
//     var newCust = [[0, 0], [1, 10], [2,5], [3, 12], [4, 5], [5, 8], [6, 0]];
//     var retCust = [[0, 0], [1, 8], [2,3], [3, 10], [4, 3], [5, 6], [6,0]];
	
//     // var plot = jQuery.plot(jQuery("#basicflot"),
// 	// [{
// 	//     data: newCust,
// 	//     label: "New Customer",
// 	//     color: "#03c3c4"
// 	// },
//     //     {
// 	//     data: retCust,
// 	//     label: "Returning Customer",
// 	//     color: "#905dd1"
//     //     }
// 	// ],
// 	// {
// 	//     series: {
// 	// 	lines: {
// 	// 	    show: false
// 	// 	},
// 	// 	splines: {
// 	// 	    show: true,
// 	// 	    tension: 0.4,
// 	// 	    lineWidth: 1,
// 	// 	    fill: 0.4
// 	// 	},
// 	// 	shadowSize: 0
// 	//     },
// 	//     points: {
// 	// 	show: true,
// 	//     },
// 	//     legend: {
// 	// 	container: '#basicFlotLegend',
//     //             noColumns: 0
// 	//     },
// 	//     grid: {
// 	// 	hoverable: true,
// 	// 	clickable: true,
// 	// 	borderColor: '#ddd',
// 	// 	borderWidth: 0,
// 	// 	labelMargin: 5,
// 	// 	backgroundColor: '#fff'
// 	//     },
// 	//     yaxis: {
// 	// 	min: 0,
// 	// 	max: 15,
// 	// 	color: '#eee'
// 	//     },
// 	//     xaxis: {
// 	// 	color: '#eee'
// 	//     }
// 	// });
		
// 	// var previousPoint = null;
// 	// jQuery("#basicflot").bind("plothover", function (event, pos, item) {
// 	// jQuery("#x").text(pos.x.toFixed(2));
// 	// jQuery("#y").text(pos.y.toFixed(2));
			
// 	// if(item) {
// 	//     if (previousPoint != item.dataIndex) {
// 	// 	previousPoint = item.dataIndex;
						
// 	// 	jQuery("#tooltip").remove();
// 	// 	var x = item.datapoint[0].toFixed(2),
// 	// 	y = item.datapoint[1].toFixed(2);
	 			
// 	// 	showTooltip(item.pageX, item.pageY,
// 	// 	item.series.label + " of " + x + " = " + y);
// 	//     }
			
// 	// } else {
// 	//     jQuery("#tooltip").remove();
// 	//     previousPoint = null;            
// 	// }
		
//     // });
		
//     // jQuery("#basicflot").bind("plotclick", function (event, pos, item) {
// 	// if (item) {
// 	//     plot.highlight(item.series, item.datapoint);
// 	// }
//     // });
    
    
//     /*****CHART 2 *****/
    
//     var visitors = [[0, 0], [1, 3], [2,2], [3, 5], [4, 4], [5, 5], [6, 0]];
//     var unique = [[0, 0], [1, 2], [2,1], [3, 3], [4, 3], [5, 4], [6,0]];
	
//     var plot = jQuery.plot(jQuery("#basicflot2"),
// 	[{
// 	    data: visitors,
// 	    label: "Visits",
// 	    color: "#428bca"
// 	},
//         {
// 	    data: unique,
// 	    label: "Unique Visits",
// 	    color: "#b830b3"
//         }
// 	],
// 	{
// 	    series: {
// 		lines: {
// 		    show: false
// 		},
// 		splines: {
// 		    show: true,
// 		    tension: 0.4,
// 		    lineWidth: 1,
// 		    fill: 0.5
// 		},
// 		shadowSize: 0
// 	    },
// 	    points: {
// 		show: true
// 	    },
// 	    legend: {
// 		container: '#basicFlotLegend2',
//                 noColumns: 0
// 	    },
// 	    grid: {
// 		hoverable: true,
// 		clickable: true,
// 		borderColor: '#ddd',
// 		borderWidth: 0,
// 		labelMargin: 5,
// 		backgroundColor: '#fff'
// 	    },
// 	    yaxis: {
// 		min: 0,
// 		max: 15,
// 		color: '#eee'
// 	    },
// 	    xaxis: {
// 		color: '#eee'
// 	    }
// 	});
		
// 	var previousPoint = null;
// 	jQuery("#basicflot2").bind("plothover", function (event, pos, item) {
// 	jQuery("#x").text(pos.x.toFixed(2));
// 	jQuery("#y").text(pos.y.toFixed(2));
			
// 	if(item) {
// 	    if (previousPoint != item.dataIndex) {
// 		previousPoint = item.dataIndex;
						
// 		jQuery("#tooltip").remove();
// 		var x = item.datapoint[0].toFixed(2),
// 		y = item.datapoint[1].toFixed(2);
	 			
// 		showTooltip(item.pageX, item.pageY,
// 		item.series.label + " of " + x + " = " + y);
// 	    }
			
// 	} else {
// 	    jQuery("#tooltip").remove();
// 	    previousPoint = null;            
// 	}
		
//     });
		
//     jQuery("#basicflot2").bind("plotclick", function (event, pos, item) {
// 	if (item) {
// 	    plot.highlight(item.series, item.datapoint);
// 	}
//     });
    
    
//     /*****CHART 3 *****/
    
//     var impressions =       [[0, 0], [1, 5], [2,2], [3, 7], [4, 4], [5, 5], [6, 0]];
//     var uniqueimpressions = [[0, 0], [1, 2], [2,1], [3, 6], [4, 3], [5, 4], [6,0]];
	
//     var plot = jQuery.plot(jQuery("#basicflot3"),
// 	[{
// 	    data: impressions,
// 	    label: "Impressions",
// 	    color: "#905dd1"
// 	},
//         {
// 	    data: uniqueimpressions,
// 	    label: "Unique Impressions",
// 	    color: "#428bca"
//         }
// 	],
// 	{
// 	    series: {
// 		lines: {
// 		    show: false
// 		},
// 		splines: {
// 		    show: true,
// 		    tension: 0.4,
// 		    lineWidth: 1,
// 		    fill: 0.4
// 		},
// 		shadowSize: 0
// 	    },
// 	    points: {
// 		show: true
// 	    },
// 	    legend: {
// 		container: '#basicFlotLegend3',
//                 noColumns: 0
// 	    },
// 	    grid: {
// 		hoverable: true,
// 		clickable: true,
// 		borderColor: '#ddd',
// 		borderWidth: 0,
// 		labelMargin: 5,
// 		backgroundColor: '#fff'
// 	    },
// 	    yaxis: {
// 		min: 0,
// 		max: 15,
// 		color: '#eee'
// 	    },
// 	    xaxis: {
// 		color: '#eee'
// 	    }
// 	});
		
// 	var previousPoint = null;
// 	jQuery("#basicflot3").bind("plothover", function (event, pos, item) {
// 	jQuery("#x").text(pos.x.toFixed(2));
// 	jQuery("#y").text(pos.y.toFixed(2));
			
// 	if(item) {
// 	    if (previousPoint != item.dataIndex) {
// 		previousPoint = item.dataIndex;
						
// 		jQuery("#tooltip").remove();
// 		var x = item.datapoint[0].toFixed(2),
// 		y = item.datapoint[1].toFixed(2);
	 			
// 		showTooltip(item.pageX, item.pageY,
// 		item.series.label + " of " + x + " = " + y);
// 	    }
			
// 	} else {
// 	    jQuery("#tooltip").remove();
// 	    previousPoint = null;            
// 	}
		
//     });
		
//     jQuery("#basicflot3").bind("plotclick", function (event, pos, item) {
// 	if (item) {
// 	    plot.highlight(item.series, item.datapoint);
// 	}
//     });
    
    
//     jQuery('#sparkline').sparkline([4,3,3,1,4,3,2,2,3,10,9,6], {
// 	type: 'bar', 
//         height:'30px',
//         barColor: '#428BCA'
//     });
    
//     jQuery('#sparkline2').sparkline([9,8,8,6,9,10,6,5,6,3,4,2], {
// 	type: 'bar', 
// 	height:'30px',
//         barColor: '#999'
//     });
    
//     jQuery('#sparkline3').sparkline([4,3,3,1,4,3,2,2,3,10,9,6], {
// 	type: 'bar', 
//         height:'30px',
//         barColor: '#428BCA'
//     });
    
//     jQuery('#sparkline4').sparkline([9,8,8,6,9,10,6,5,6,3,4,2], {
// 	type: 'bar', 
// 	height:'30px',
//         barColor: '#999'
//     });
    
//     jQuery('#sparkline5').sparkline([4,3,3,1,4,3,2,2,3,10,9,6], {
// 	type: 'bar', 
//         height:'30px',
//         barColor: '#428BCA'
//     });
    
//     jQuery('#sparkline6').sparkline([9,8,8,6,9,10,6,5,6,3,4,2], {
// 	type: 'bar', 
// 	height:'30px',
//         barColor: '#999'
//     });
    
    
//     /***** BAR CHART *****/
    
    var m3 = new Morris.Bar({
        // ID of the element in which to draw the chart.
        element: 'bar-chart',
        // Chart data records -- each entry in this array corresponds to a point on
        // the chart.
        data: [
            { y: 'Enero', a: 50, b: 20 },
            { y: 'Febrero', a: 75,  b: 65 },
            { y: 'Marzo', a: 50,  b: 40 },
            { y: 'Abril', a: 75,  b: 65 }
        ],
        xkey: 'y',
        ykeys: ['a', 'b'],
        labels: ['Series A', 'Series B'],
        lineWidth: '1px',
        fillOpacity: 0.8,
        smooth: false,
        hideHover: true,
        resize: true
    });
    
    var delay = (function() {
	var timer = 0;
	return function(callback, ms) {
	    clearTimeout(timer);
	    timer = setTimeout(callback, ms);
	};
    })();

    jQuery(window).resize(function() {
	delay(function() {
	    m3.redraw();
	}, 200);
    }).trigger('resize');

                

});