function format_address(value) {
  if (value.slice(-8) == ', Canada') {
    value = value.slice(0, -8);
  }

  return _.escape(value).replace(/^(.*?), /, '<div><strong>$1</strong></div>');
}

function telephone_subscriber(value) {
  value = value.replace(/\D/g, '');
  if (value.length == 10) {
    return value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
  }

  return value;
}

function short_title(value) {
  switch (value) {
    case 'Creston Hotel and Jimmy\'s Pub':
      return 'Jimmy\'s Pub';

    case 'Kootenay Natural Meats and Goat River Farms':
      return 'Kootenay Natural Meats';
  }

  return value.replace(/ \(.*/, '');
}

function format_terms(value) {
  if (Array.isArray(value)) {
    var result = '';
    for (var i = 0; i < value.length; i++) {
      result += format_terms(value[i]) + ', ';
    }

    return result.slice(0, -2);
  }

  // Exhibit.Database._indexVisit
  if (typeof value == 'object') {
    return format_terms(Object.keys(value));
  }

  return '<span class="tag-' + ('-' + value.toLowerCase() + '-').replace(/[^a-z]+/, '-').slice(1, -1) + '">' + _.escape(short_title(value)) + '</span>';
}

_.templateSettings = {
  evaluate: /<#([\s\S]+?)#>/g,
  interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
  escape: /\{\{([^\}]+?)\}\}(?!\})/g,
  variable: 'data'
};

var address_template = _.template(' \
  <address> \
\
    <# if (data.address) { #> \
      <div title="{{{ script.address }}}"><i class="fa fa-map-marker"></i> {{{ format_address(data.address) }}}</div> \
    <# } #> \
\
    <# if (data.phone) { #> \
      <div><a title="{{{ script.phone }}}" href="tel:{{{ telephone_subscriber(data.phone) }}}">{{ data.phone }}</a></div> \
    <# } #> \
\
  </address> \
');

var map_template = _.template(' \
  <article> \
    <table class=info-window> \
      <tr> \
\
        <# if (data["thumbnail-src"]) { #> \
          <td class=thumbnail rowspan=2> \
            <a href="{{{ data.permalink }}}"> \
              <img class=archivePostThumb src="{{{ data["thumbnail-src"] }}}" width="{{{ data["thumbnail-width"] }}}" height="{{{ data["thumbnail-height"] }}}"> \
            </a> \
          </td> \
        <# } #> \
\
        <td colspan=2> \
          <header class=entry-header> \
            <h2 class=entry-title><a rel=bookmark href="{{{ data.permalink }}}">{{ data.title || data.label }}</a></h2> \
          </header> \
        </td> \
\
      </tr> \
      <tr> \
        <td> \
          {{{ address_template(data) }}} \
        </td> \
        <td> \
\
          <# if (data["farm-practices"]) { #> \
            <div class=farm-practices><i class="fa fa-certificate"></i> <strong>{{{ format_terms(data["farm-practices"]) }}}</strong></div> \
          <# } #> \
\
          <# if (data["business-type"]) { #> \
            <dl> \
              <dt>{{{ script["business-type"] }}}</dt> \
              <dd>{{{ format_terms(data["business-type"]) }}}</dd> \
            </dl> \
          <# } #> \
\
        </td> \
      </tr> \
    </table> \
  </article> \
');
