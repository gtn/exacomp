$(document).ready(function () {
  $(this).find('.exa-collapsible-open').each(function () {
    $(this).removeClass('exa-collapsible-open');
  });

  $('.exacomp_tabbed').responsiveTabs({
    // navigationContainer: 'ul',
    // collapsible: false,
    // startCollapsed: false,
    startCollapsed: 'accordion'
  });
});

/*$(document).on('click', 'a.print', function(e) {
	$(document).find('.exa-collapsible').each(function () {
		 $(this).addClass('exa-collapsible-open');
	})

	window.print();
});*/

(function ($) {

  $.fn.donut = function (options) {
    var settings = $.extend({
      colors: [],
    }, options);

    return this.each(function () {
      var ctx = this.getContext('2d');
      var segments = [];
      var labels = [];
      settings.colors = [];

      var canvasWidth = $(this).width();
      var canvasHeight = $(this).height();
      var xCenter = Math.floor(canvasWidth / 2);
      var yCenter = Math.floor(canvasHeight / 2);
      var radius = Math.ceil(0.8 * Math.min(xCenter, yCenter));
      var innerRadius = Math.ceil(radius / 2);

      var valMax = $(this).data('valuemax');
      var value = $(this).data('value');

      for (i = 0; i < valMax; i++) {
        segments.push(100 / valMax);
        labels.push(i);
      }

      if (value < 0) {
        for (i = 0; i < valMax; i++) {
          settings.colors.push('#ffffff');
        }
      } else if (value == 0) {
        for (i = 0; i < valMax; i++) {
          settings.colors.push('#dd001a');
        }

      } else if (value > 0) {
        for (i = 0; i < value; i++) {
          settings.colors.push('#0add1a');
        }

        for (i = value; i < valMax; i++) {
          settings.colors.push('#ffffff');
        }
      }

      //Reset the canvas
      ctx.clearRect(0, 0, canvasWidth, canvasHeight);
      ctx.restore();
      ctx.save();

      var chartTitle = 'M';

      function addText(text, x, y) {
        ctx.lineWidth = 1;
        ctx.fillStyle = "#000000";
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.textTransform = 'uppercase';
        ctx.fillText(chartTitle, xCenter, yCenter);
      }

      if ($(this).data('title')) {
        chartTitle = $(this).data('title');
        addText(chartTitle, xCenter, yCenter);
      }

      var i,
        total = 0;

      for (i = 0; i < segments.length; i++) {
        total = total + parseFloat(segments[i]);
      }

      var percentByDegree = 360 / total,
        degToRad = Math.PI / 180,
        currentAngle = 0,
        startAngle = 0,
        endAngle,
        innerStart,
        innerEnd;

      ctx.translate(xCenter, yCenter);
      //Turn the chart around so the segments start from 12 o'clock
      ctx.rotate(270 * degToRad);

      for (i = 0; i < segments.length; i++) {
        startAngle = currentAngle * degToRad;
        endAngle = (currentAngle + (segments[i] * percentByDegree)) * degToRad;

        //Draw the segments
        ctx.fillStyle = settings.colors[i % settings.colors.length];

        ctx.beginPath();
        ctx.arc(0, 0, radius, startAngle, endAngle, false);
        ctx.arc(0, 0, innerRadius, endAngle, startAngle, true);
        ctx.strokeStyle = "gray";

        ctx.stroke();
        ctx.closePath();

        ctx.fill();

        currentAngle = currentAngle + (segments[i] * percentByDegree);
      }
    });
  };

}(jQueryExacomp));
