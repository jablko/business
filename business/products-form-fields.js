jQuery(function ($) {
  var months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];

  function format(value) {
    if (value >= 59) {
      value += 2;
    }
    value += Math.floor((value >= 244 ? (value - 31) : value) / 61);

    return months[Math.floor(value / 31)] + ' ' + (value % 31 + 1);
  }

  function parse(value) {
    value = value.split(' ', 2);
    value = 31 * months.indexOf(value[0]) + parseInt(value[1]) - 1;
    value -= Math.floor((value >= 248 ? (value - 31) : value) / 62);
    if (value >= 61) {
      value -= 2;
    }

    return value;
  }

  var in_season = document.getElementById('in-season');

  function slide_handler(event, ui) {
    in_season.value = format(ui.values[0]) + ' to ' + format(ui.values[1]);
    in_season.classList.add('autofill');
  }

  var opts = {
    max: 364,
    range: true,
    slide: slide_handler
  };

  var slider = $(document.getElementById('slider')).slider(opts);

  function input_handler() {
    in_season.classList.remove('autofill');
    var values = in_season.value.split(' to ', 2);
    for (var i = 0; i < values.length; i++) {
      var value = parse(values[i]);
      if (value) {
        slider.slider('values', i, value);
      }
    }
  }
  input_handler();

  $(in_season).on('input', input_handler);
});
