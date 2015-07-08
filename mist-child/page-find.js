function grow(tree, data, value) {
  // Exhibit.Database._indexVisit
  if (typeof value == 'object') {
    for (value in value) {
      grow(tree, data, value);
    }
  } else {
    var parent = data.spo[value] && data.spo[value].parent;
    if (tree[parent]) {
      tree[parent][value] = true;

      return;
    }
    (tree[parent] = {})[value] = true;
    while (parent) {
      var value = parent;
      var parent = data.spo[value] && data.spo[value].parent;
      if (tree[parent]) {
        if (!tree[parent][value]) {
          tree[parent][value] = false;
        }

        return;
      }
      (tree[parent] = {})[value] = false;
    }
  }
}

// new Intl.Collator().compare
function compare(a, b) {
  return String.prototype.localeCompare.call(a, b);
}

function group_branches(tree, branches) {
  var groups = [];
  for (var value in branches) {
    var values = [];

    if (tree[value]) {
      var branch_groups = group_branches(tree, tree[value]);
      for (var i = 0; i < branch_groups.length; i++) {
        if (branch_groups[i].length <= 4) {
          values = values.concat(branch_groups[i]);
        }
      }

      if (branches[value] || values.length > 3) {
        for (var i = branch_groups.length - 1; i >= 0; i--) {
          if (branch_groups[i].length <= 4) {
            branch_groups.splice(i, 1);
          }
        }
      } else {
        var values = [];
      }
    } else {
      var branch_groups = [];
    }

    if (branches[value] || values.length) {
      values.unshift(value);
      branch_groups.unshift(values);
    }

    groups.push(branch_groups);
  }

  groups.sort(compare);
  return Array.prototype.concat.apply([], groups);
}

var months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];

function is_in_season(value) {
  var values = value.split(' to ', 2);
  for (var i = 0; i < 2; i++) {
    values[i] = values[i].split(' ', 2);
    values[i] = [ months.indexOf(values[i][0]), parseInt(values[i][1]) ];
  }

  var now = new Date();
  var now = [ now.getMonth(), now.getDate() ];

  return values[1] >= values[0] ? now >= values[0] && now <= values[1] : now >= values[0] || now <= values[1];
}

function format_products(data) {
  var tree = {};
  var value = data.spo[data.itemID].products;
  grow(tree, data, value);

  var groups = group_branches(tree, tree[undefined]);
  var result = '';
  for (var i = 0; i < groups.length; i++) {
    for (var j = 0; j < (groups[i].length > 3 || (typeof value == 'object' ? value[groups[i][0]] : groups[i][0] == value) || groups[i].length); j++) {
      var in_season = data.spo[groups[i][j]] && data.spo[groups[i][j]]['in-season'];
      if (in_season) {
        var tag_name = is_in_season(in_season) ? 'strong' : 'span';
        result += '<' + tag_name + ' title="' + page_find['in-season'].replace('%s', _.escape(in_season)) + '">' + _.escape(groups[i][j]) + '</' + tag_name + '>, ';
      } else {
        result += _.escape(groups[i][j]) + ', ';
      }
    }
  }

  return result.slice(0, -2);
}

var list_template = _.template(' \
  <tr> \
    <td colspan=2> \
      <header class=entry-header> \
        <h2 class=entry-title><a rel=bookmark href="{{{ data.spo[data.itemID].permalink }}}">{{ data.spo[data.itemID].title || data.spo[data.itemID].label }}</a></h2> \
      </header> \
    </td> \
  </tr> \
  <tr> \
    <td> \
      {{{ address_template(data.spo[data.itemID]) }}} \
    </td> \
    <td> \
\
      <# if (data.spo[data.itemID]["thumbnail-src"]) { #> \
        <a href="{{{ data.spo[data.itemID].permalink }}}"> \
          <img class=archivePostThumb src="{{{ data.spo[data.itemID]["thumbnail-src"] }}}" width="{{{ data.spo[data.itemID]["thumbnail-width"] }}}" height="{{{ data.spo[data.itemID]["thumbnail-height"] }}}"> \
        </a> \
      <# } #> \
\
      <dl> \
\
        <# if (data.spo[data.itemID].products) { #> \
          <dt>{{{ data.products }}}</dt> \
          <dd>{{{ format_products(data) }}}</dd> \
        <# } #> \
\
        <# if (data.ops[data.itemID]["available-at"]) { #> \
          <dt>{{{ data["products-from"] }}}</dt> \
          <dd>{{{ format_terms(data.ops[data.itemID]["available-at"]) }}}</dd> \
        <# } #> \
\
        <# if (data.spo[data.itemID]["farm-practices"]) { #> \
          </dl> \
\
          <div class=farm-practices><i class="fa fa-certificate"></i> <strong>{{{ format_terms(data.spo[data.itemID]["farm-practices"]) }}}</strong></div> \
\
          <dl> \
        <# } #> \
\
        <# if (data.spo[data.itemID]["business-type"]) { #> \
          <dt>{{{ script["business-type"] }}}</dt> \
          <dd>{{{ format_terms(data.spo[data.itemID]["business-type"]) }}}</dd> \
        <# } #> \
\
      </dl> \
\
    </td> \
  </tr> \
');

function map_constructor(map_div) {
  var opts = {
    scrollwheel: false
  };

  return new google.maps.Map(map_div, opts);
}

jQuery(function ($) {
  function handler() {
    Exhibit.TileView.prototype._reconstruct = function () {
      var view = this;
      var state = {
        div: this._dom.bodyDiv,
        contents: null,
        groupDoms: [],
        groupCounts: []
      };

      function closeGroups(groupLevel) {
        for (var i = groupLevel; i < state.groupDoms.length; i++) {
          Exhibit.jQuery(state.groupDoms[i].countSpan).html(state.groupCounts[i]);
        }
        state.groupDoms = state.groupDoms.slice(0, groupLevel);
        state.groupCounts = state.groupCounts.slice(0, groupLevel);

        if (groupLevel > 0 && groupLevel <= state.groupDoms.length) {
          state.div = state.groupDoms[groupLevel - 1].contentDiv;
        } else {
          state.div = view._dom.bodyDiv;
        }
        state.contents = null;
      };

      this._orderedViewFrame.onNewGroup = function (groupSortKey, keyType, groupLevel) {
        closeGroups(groupLevel);

        var groupDom = Exhibit.TileView.constructGroup(groupLevel, groupSortKey);

        Exhibit.jQuery(state.div).append(groupDom.elmt);
        state.div = groupDom.contentDiv;

        state.groupDoms.push(groupDom);
        state.groupCounts.push(0);
      };

      this._orderedViewFrame.onNewItem = function(itemID, index) {
        if (typeof state.contents === 'undefined' || state.contents === null) {
          state.contents = Exhibit.TileView.constructList();
          Exhibit.jQuery(state.div).append(state.contents);
        }

        for (var i = 0; i < state.groupCounts.length; i++) {
          state.groupCounts[i]++;
        }

        var database = view.getUIContext().getDatabase();

        var data = {
          spo: database._spo,
          ops: database._ops,
          itemID: itemID
        };

        var value = database._spo[itemID]['business-type'];
        data.products = page_find[typeof value == 'object' ? value.Restaurant ? 'local-ingredients' : value.Retailer ? 'local-products' : 'products' : {
          Restaurant: 'local-ingredients',
          Retailer: 'local-products'
        }[value] || 'products'];
        data['products-from'] = page_find[(typeof value == 'object' ? value.Restaurant : value == 'Restaurant') ? 'we-serve' : 'we-sell'];

        state.contents.append(list_template(data));
      };

      Exhibit.jQuery(this.getContainer()).hide();

      Exhibit.jQuery(this._dom.bodyDiv).empty();
      this._orderedViewFrame.reconstruct();
      closeGroups(0);

      Exhibit.jQuery(this.getContainer()).show();
    };

    Exhibit.TileView.constructList = function () {
      return Exhibit.jQuery('<table class=exhibit-tileView-body>');
    };

    Exhibit.ViewUtilities.fillBubbleWithItems = function (bubbleElmt, arrayOfItemIDs, labelExpression, uiContext) {
      return map_template(uiContext.getDatabase()._spo[arrayOfItemIDs[0]]);
    };

    Exhibit.MapView.prototype._rePlotItems = function (unplottableItems) {
      var bounds;

      var view = this;
      var collection = this.getUIContext().getCollection();
      var database = this.getUIContext().getDatabase();
      var settings = this._settings;

      var currentSet = collection.getRestrictedItems();
      var locationToData = {};

      var hasPoints = this._getLatlng !== null;

      currentSet.visit(function (itemID) {
        var latlngs = [];

        if (hasPoints) {
          view._getLatlng(itemID, database, function (v) {
            if (v !== null && typeof v.lat !== 'undefined' && v.lat !== null && typeof v.lng !== 'undefined' && v.lng !== null) {
              latlngs.push(v);
            }
          });
        }

        if (latlngs.length > 0) {
          for (var i = 0; i < latlngs.length; i++) {
            var latlng = latlngs[i];
            var latlngKey = latlng.lat + ',' + latlng.lng;
            if (typeof locationToData[latlngKey] !== 'undefined') {
              var locationData = locationToData[latlngKey];
              locationData.items.push(itemID);
            } else {
              var locationData = {
                latlng: latlng,
                items: [ itemID ]
              };
              locationToData[latlngKey] = locationData;
            }
          }
        } else {
          unplottableItems.push(itemID);
        }
      });

      var addMarkerAtLocation = function (locationData) {
        if (typeof bounds === 'undefined' || bounds === null) {
          bounds = new google.maps.LatLngBounds();
        }

        var point = new google.maps.LatLng(locationData.latlng.lat, locationData.latlng.lng);
        bounds.extend(point);

        var opts = database._spo[locationData.items[0]];
        opts.position = point;
        opts.map = view._map;
        opts.title = opts.label;

        var marker = new google.maps.Marker(opts);

        google.maps.event.addListener(marker, 'click', function () {
          view._showInfoWindow(locationData.items, null, marker);
          if (view._selectListener !== null) {
            view._selectListener.fire({ itemIDs: locationData.items });
          }
        });
        view._overlays.push(marker);

        for (var i = 0; i < locationData.items.length; i++) {
          view._itemIDToMarker[locationData.items[i]] = marker;
        }
      };

      try {
        for (var latlngKey in locationToData) {
          if (locationToData.hasOwnProperty(latlngKey)) {
            addMarkerAtLocation(locationToData[latlngKey]);
          }
        }
      } catch (e) {
        Exhibit.Debug.exception(e);
      }

      if (typeof bounds !== 'undefined' && bounds !== null && settings.autoposition && !this._shown) {
        google.maps.event.addListenerOnce(view._map, 'bounds_changed', function () {
          if (view._map.getZoom() > settings.maxAutoZoom) {
            view._map.setZoom(settings.maxAutoZoom);
          }
        });
        view._map.fitBounds(bounds);
      }

      this._shown = true;
    };
  }
  $(document).on('scriptsLoaded.exhibit', handler);
});
