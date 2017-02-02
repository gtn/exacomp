$(function () {
	var data = new vis.DataSet();

	vis.Graph3d.prototype._getColorsRegular = function (data) {
		var color = {
			fill: 'RGB(220,220,222)',
			border: 'RGB(0,0,0)'
		};

		if (exacomp_graph.options.yColors && exacomp_graph.options.yColors[data.point.y]) {
			$.extend(color, exacomp_graph.options.yColors[data.point.y]);
		}

		return color;
	};

	$.each(exacomp_graph.data, function (key, entry) {
		data.add({
			x: entry.x,
			y: entry.y,
			z: entry.z,
		});
	});

	// specify options
	var options = $.extend({
		width: '600px',
		height: '600px',
		style: 'bar',
		showPerspective: true,
		showGrid: true,
		showShadow: false,

		xMin: 0,
		xMax: exacomp_graph.options.xLabels.length,
		xStep: 1,
		yMin: 0,
		yMax: exacomp_graph.options.yLabels.length,
		yStep: 1,
		zMin: 0,
		zMax: exacomp_graph.options.zLabels.length - 1,
		zStep: 1,
		xLabel: "",
		yLabel: "",
		zLabel: "",
		xBarWidth: 1,
		yBarWidth: 1,

		tooltip: function (point) {
			if (exacomp_graph.data[point.x+'-'+point.y+'-'+point.z]) {
				return exacomp_graph.data[point.x+'-'+point.y+'-'+point.z].label || '';
			} else {
				return '';
			}
		},

		xValueLabel: function (value) {
			return exacomp_graph.options.xLabels[value] || '';
		},
		yValueLabel: function (value) {
			return exacomp_graph.options.yLabels[value] || '';
		},

		zValueLabel: function (value) {
			return exacomp_graph.options.zLabels[value] || '';
		},

		keepAspectRatio: true,
		verticalRatio: 1.0
	}, exacomp_graph.options);

	// create our graph
	var container = document.getElementById('mygraph');
	var graph = new vis.Graph3d(container, data, options);

	graph.setCameraPosition({
		horizontal: -0.1,
		vertical: 0.23,
		distance: 1.9
	});
});
