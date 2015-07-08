jQuery(function ($) {
  function source(data) {
    return data.thumbnail;
  }

  var opts = {
    helpers: {
      thumbs: {
        source: source
      }
    }
  };

  function handler() {
    for (var parentNode = this.parentNode; parentNode; parentNode = parentNode.parentNode) {
      if (parentNode.classList.contains('gallery-item')) {
        opts.index = Array.prototype.indexOf.call(parentNode.parentNode.children, parentNode);

        break;
      }
    }
    $.fancybox.open(<?php echo wp_json_encode($all_data) ?>, opts);

    return false;
  }
  $(document.getElementById('gallery-1')).on('click', 'a', handler);
});
