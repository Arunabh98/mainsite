@extends('layout.default')
@section('script')
<script src="{{ URL::asset('assets/js/d3.v3.min.js')}}"></script>
<script src="{{ URL::asset('assets/js/d3pie.min.js')}}"></script>
<script type="text/javascript" src="{{ URL::asset('assets/js/tabletop.js')}}"></script>
<script src="{{URL::asset('assets/js/highcharts.js')}}"></script>
<script src="{{URL::asset('assets/js/exporting.js')}}"></script>
<script>

	var colors = ["#2484c1","#0c6197","#4daa4b","#90c469","#daca61","#e4a14b","#e98125","#cb2121","#830909","#ae83d5","#bf273e","#ce2aeb","#bca44a","#618d1b"];

	function chart(id,title,subtitle,content){
	  	return new d3pie(id, {
		"header": {
			"title": {
				"text": title,
				"fontSize": 24,
				"font": "open sans"
			},
			"subtitle": {
				"text": subtitle,
				"color": "#999999",
				"fontSize": 12,
				"font": "open sans"
			},
			"titleSubtitlePadding": 9
		},
		"footer": {
			"color": "#999999",
			"fontSize": 10,
			"font": "open sans",
			"location": "bottom-left"
		},
		"size": {
			"canvasWidth": 550,
			"pieInnerRadius": "30%",
			"pieOuterRadius": "90%"
		},
		"data": {
			"sortOrder": "value-desc",
			"content": content
		},
		"labels": {
			"outer": {
				"pieDistance": 32
			},
			"inner": {
				"hideWhenLessThanPercentage": 3
			},
			"mainLabel": {
				"fontSize": 11
			},
			"percentage": {
				"color": "#ffffff",
				"decimalPlaces": 0
			},
			"value": {
				"color": "#adadad",
				"fontSize": 11
			},
			"lines": {
				"enabled": true
			},
			"truncation": {
				"enabled": true
			}
		},
		"effects": {
			"pullOutSegmentOnClick": {
				"effect": "linear",
				"speed": 400,
				"size": 8
			}
		},
		"misc": {
			"gradient": {
				"enabled": true,
				"percentage": 100
			}
		}
		});
	}
	function _x(STR_XPATH) {
	    var xresult = document.evaluate(STR_XPATH, document, null, XPathResult.ANY_TYPE, null);
	    var xnodes = [];
	    var xres;
	    while (xres = xresult.iterateNext()) {
	        xnodes.push(xres);
	    }

	    return xnodes;
	}
	function getContent(gc){
		var content = [];
		for(var i=0;i<14;i++){
			var cnt={
				"label":"Hostel "+(i+1).toString(),
				"value":gc.scores[i],
				"color":colors[i]
			};
			content.push(cnt);
		}
		return content;
	}

      var a;
      window.onload = function() { init() };

      var public_spreadsheet_url_1 = 'https://docs.google.com/spreadsheets/d/1kTQ6zwYY0hiKmSi_6ZpkWm7fMBvN40-lWaQ3rAcCFZs/pubhtml?gid=0&single=true';

      function init() {
        a = Tabletop({
            key: public_spreadsheet_url_1,
            callback: showInfo,
            simpleSheet: true
        });
      }

      var count = 0;
      var GC = {};
      GC.physXGC = {};
      GC.logicGC = {};
      GC.physXGC.scores = [];
      GC.logicGC.scores = [];
      function showInfo(data, tabletop) {
        for(i = 1; i < data.length; i++) {
        	GC.physXGC.scores.push(parseInt(data[0]["PhysX GC"])*parseFloat(data[i]["PhysX GC"]));
        	GC.logicGC.scores.push(parseInt(data[0]["Logic GC"])*parseFloat(data[i]["Logic GC"]))
        }

		GC.physXGC.content = getContent(GC.physXGC);
		GC.physXGC.pie = chart("physXGC","PhysX GC","Performance of Hostels",GC.physXGC.content);
		GC.logicGC.content = getContent(GC.logicGC);
		GC.logicGC.pie = chart("logicGC","Logic GC","Performance of Hostels",GC.logicGC.content);
		$('#allgraphs').trigger('click');

		var hostels = ['H1','H2','H3','H4','H5','H6','H7','H8','H9','H10','H11','H12','H13','H14'];
		var series = [{
		            name: 'PhysX GC',
		            data: GC.physXGC.scores
		        }, {
		            name: 'Logic GC',
		            data: GC.logicGC.scores
		        }];
	    $('#bar-graph').highcharts({
	        chart: {
	            type: 'bar'
	        },
	        title: {
	            text: 'Stacked bar chart'
	        },
	        xAxis: {
	            categories: hostels
	        },
	        yAxis: {
	            min: 0,
	            title: {
	                text: 'Hostel GC Tally'
	            }
	        },
	        legend: {
	            reversed: true
	        },
	        plotOptions: {
	            series: {
	                stacking: 'normal'
	            }
	        },
	        series: [{
	            name: 'PhysX GC',
	            data: GC.physXGC.scores
	        }, {
	            name: 'Logic GC',
	            data: GC.logicGC.scores
	        }]
	    });

		$(".highcharts-button").remove();

	}


</script>

@endsection
@section('content')
<main>
			
	<section  class="light-bg">
		<div class="container inner">
					
					<div class="row">
						<div class="col-md-8 col-sm-9 center-block text-center">
							<header>
								<h1>Tech GC Points</h1>
								<p>Year : 2015 - 2016</p>
								<p> Click <a href="https://docs.google.com/spreadsheets/d/1kTQ6zwYY0hiKmSi_6ZpkWm7fMBvN40-lWaQ3rAcCFZs/edit?usp=sharing">here</a> to see the overall tally</p> 
								<br>
							</header>
						</div><!-- /.col -->
						<div class="col-md-12 center-block text-center">
							<div id="bar-graph" style="width: 310px; width: 100%; height: 100%; margin: 0 auto"></div>
							</div>
						</div>	
					</div><!-- /.row -->
					<br>


				<div class="container inner-bottom">
					<div class="row">
						<div class="col-sm-12 portfolio">
							
							<ul class="filter text-center">
								<li><a id="allgraphs" href="#" data-filter="*" class="active">All</a></li>
								<li><a href="#" data-filter=".mnp">Maths and Physics Club</a></li>
							</ul><!-- /.filter -->
							
							<ul class="items col-2 gap">
								
								<li class="item mnp">
									<div id="physXGC"></div>
								</li><!-- /.item -->
								<li class="item mnp">
									<div id="logicGC"></div>
								</li><!-- /.item -->
							</ul><!-- /.items -->
							
						</div><!-- /.col -->
					</div><!-- /.row -->
				</div><!-- /.container -->								
					<br>

		</div>
	</section>
			
</main>
@endsection