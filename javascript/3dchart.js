/**
 * 
 */

(function($) {

	$(document).ready(
			function() {
				var data = new vis.DataSet();;
				var graph = null;

				var exacomp_data = null;
				var start, end = 0;
				
				if(sessionStorage.getItem('date1') != null && sessionStorage.getItem('date2') != null) {
					start = (new Date(sessionStorage.getItem('date1')).getTime() / 1000);
			    	end = (new Date(sessionStorage.getItem('date2')).getTime() / 1000);
				}
				
				block_exacomp.call_ajax({
					topicid : block_exacomp.get_param('topicid'),
					userid : block_exacomp.get_param('userid'),
					start : start,
					end : end,
					action : 'get_3dchart_data'
				}).done(function(msg) {
					exacomp_data = $.parseJSON(msg); 
				
				// Create and populate a data table.
				
				var index;
				for (index = 0; index < Object.keys(exacomp_data.evaluation).length; index++) {
					var evaluation = exacomp_data.evaluation[Object.keys(exacomp_data.evaluation)[index]];
					if(evaluation.evalniveau > 0 && evaluation.teachervalue >= 0 ) {
						data.add({
							x: index + 1,
							y: parseInt(evaluation.evalniveau),
							z : parseInt(evaluation.teachervalue)
						});
					}
					if(evaluation.studentvalue >= 0) {
						data.add({
							x: index + 1,
							y: 4,
							z : parseInt(evaluation.studentvalue)
						});
					}
				}
				
				// specify options
				var options = {
					width : '600px',
					height : '600px',
					style : 'bar',
					showPerspective : true,
					showGrid : true,
					showShadow : false,

					xMin : 0,
					xMax : Object.keys(exacomp_data.evaluation).length + 1,
					xStep : 1,
					yMin : 0,
					yMax : 5,
					yStep : 1,
					zMin : 0,
					zMax : Object.keys(exacomp_data.value_titles).length - 2, // subtract values -1 and 0
					zStep : 1,
					xLabel : "LFS",
					yLabel : "",
					zLabel : "",
					xBarWidth : 1,
					yBarWidth : 1,

					// Option tooltip can be true, false, or a function
					// returning a string with HTML contents
					// tooltip: true,
					tooltip : function(point) {
						// parameter point contains properties x, y, z
						return 'value: <b>' + point.z + '</b>';
					},

					xValueLabel : function(value) {
						if (value == 0 || value == Object.keys(exacomp_data.evaluation).length + 1)
							return "";

						return Object.keys(exacomp_data.evaluation)[value - 1].replace("LFS","");
					},
					yValueLabel : function(value) {

						switch (value) {
						case 0:
							return "";
							break;
						case 1:
							return exacomp_data.evalniveau_titles[1];
							break;
						case 2:
							return exacomp_data.evalniveau_titles[2];
							break;
						case 3:
							return exacomp_data.evalniveau_titles[3];
							break;
						case 4:
							return M.util.get_string('selfevaluation', 'block_exacomp');
							break;
						default:
							return ""
						}
					},

					zValueLabel : function(value) {
						return exacomp_data.value_titles[value];
						/*
						switch (value) {
						case 0:
							return "nE";
							break;
						case 1:
							return "tE";
							break;
						case 2:
							return "ueE";
							break;
						case 3:
							return "vE";
							break;
						default:
							return ""
						}*/
					},

					keepAspectRatio : true,
					verticalRatio : 1.0
				};

				// create our graph
				var container = document.getElementById('mygraph');
					if(data.length > 0) {
						graph = new vis.Graph3d(container, data, options);
		
						graph.setCameraPosition({
							horizontal : -0.1,
							vertical : 0.23,
							distance : 1.9
						});
					} else {
						container.innerHTML = M.util.get_string('topic_3dchart_empty', 'block_exacomp');
					}
				
				});
			});
})(jQueryExacomp);
