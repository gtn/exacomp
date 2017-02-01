$(function () {
	var data = new vis.DataSet();
	var graph = null;

	var student_value_id = 'student_value';

	var evalniveau_titles = {};
	var evalniveau_titles_by_index = [];

	var index = 1;
	$.each(exacomp_data.evalniveau_titles, function (id, title) {
		if (id <= 0) {
			return;
		}
		evalniveau_titles_by_index[index] = evalniveau_titles[id] = {
			id: id,
			index: index,
			titleShort: title,
			title: title,
		};
		index++;
	});

	evalniveau_titles_by_index[index] = evalniveau_titles[student_value_id] = {
		id: student_value_id,
		index: index,
		titleShort: M.util.get_string('selfevaluation_short', 'block_exacomp'),
		title: M.util.get_string('selfevaluation', 'block_exacomp'),
	}

	// Create and populate a data table.
	var index = 1;
	$.each(exacomp_data.evaluation, function (key, evaluation) {
		if (evaluation.evalniveau > 0 && evaluation.teachervalue >= 0 && evalniveau_titles[evaluation.evalniveau]) {
			data.add({
				x: index,
				y: evalniveau_titles[evaluation.evalniveau].index,
				z: parseInt(evaluation.teachervalue)
			});
		}
		if (evaluation.studentvalue >= 0) {
			data.add({
				x: index,
				y: evalniveau_titles[student_value_id].index,
				z: parseInt(evaluation.studentvalue)
			});
		}
		index++;
	});

	vis.Graph3d.prototype._getColorsRegular = function (point) {
		if (point.point.y == evalniveau_titles[student_value_id].index) {
			color = 'RGB(' + parseInt(255) + ',' + parseInt(197) + ',' + parseInt(57) + ')';
		} else {
			color = 'RGB(' + parseInt(220) + ',' + parseInt(220) + ',' + parseInt(222) + ')';
		}

		return {
			fill: color,
			border: 'RGB(' + parseInt(0) + ',' + parseInt(0) + ',' + parseInt(0) + ')'
		};
	};

	// specify options
	var options = {
		width: '600px',
		height: '600px',
		style: 'bar',
		showPerspective: true,
		showGrid: true,
		showShadow: false,

		xMin: 0,
		xMax: Object.keys(exacomp_data.evaluation).length + 1,
		xStep: 1,
		yMin: 0,
		yMax: Object.keys(evalniveau_titles).length + 1,
		yStep: 1,
		zMin: 0,
		zMax: Object.keys(exacomp_data.value_titles).length - 1,
		zStep: 1,
		xLabel: "LFS",
		yLabel: "",
		zLabel: "",
		xBarWidth: 1,
		yBarWidth: 1,

		tooltip: function (point) {
			var title = evalniveau_titles_by_index[point.y] ? evalniveau_titles_by_index[point.y].title : '' || '';
			var value
			if (evalniveau_titles_by_index[point.y].id == student_value_id) {
				value = exacomp_data.value_titles_self_assessment[point.z];
			} else {
				value = exacomp_data.value_titles_long[Object.keys(exacomp_data.value_titles)[point.z]];
			}
			return title + ' <b>' + value + '</b>';
		},

		xValueLabel: function (value) {
			if (value == 0 || value == Object.keys(exacomp_data.evaluation).length + 1)
				return "";

			return Object.keys(exacomp_data.evaluation)[value - 1].replace("LFS", "");
		},
		yValueLabel: function (value) {
			return evalniveau_titles_by_index[value] ? evalniveau_titles_by_index[value].titleShort : '' || '';
		},

		zValueLabel: function (value) {
			return '';
			// return exacomp_data.value_titles[value];
		},

		keepAspectRatio: true,
		verticalRatio: 1.0
	};

	// create our graph
	var container = document.getElementById('mygraph');
	if (data.length > 0) {
		graph = new vis.Graph3d(container, data, options);

		graph.setCameraPosition({
			horizontal: -0.1,
			vertical: 0.23,
			distance: 1.9
		});
	} else {
		container.innerHTML = M.util.get_string('topic_3dchart_empty', 'block_exacomp');
	}
});
