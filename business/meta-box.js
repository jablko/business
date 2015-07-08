jQuery(function ($) {
  var title = document.getElementById('title');
  var address = document.getElementById('address');
  var phone = document.getElementById('phone');
  var email = document.getElementById('email');
  var website = document.getElementById('website');
  var business_type = document.getElementById('business-type');

  var post_body = document.getElementById('post-body');

  var bounds = new google.maps.LatLngBounds(
    new google.maps.LatLng(49, -117.2002313109224),
    new google.maps.LatLng(49.54520019716734, -115.82678448907757)
  );

  // https://www.canadapost.ca/tools/pg/manual/PGaddress-e.asp?ecid=murl10006450#1423617
  var street_types = {
    Aut: 'Autoroute',
    Ave: 'Avenue',
    Av: 'Avenue',
    Blvd: 'Boulevard',
    Boul: 'Boulevard',
    Car: 'Carré',
    Carref: 'Carrefour',
    Ctr: 'Centre',
    C: 'Centre',
    Ch: 'Chemin',
    Cir: 'Circle',
    Circt: 'Circuit',
    Conc: 'Concession',
    Crnrs: 'Corners',
    Crt: 'Court',
    Cres: 'Crescent',
    Crois: 'Croissant',
    Cross: 'Crossing',
    Cds: 'Cul-de-sac',
    Divers: 'Diversion',
    Dr: 'Drive',
    Éch: 'Échangeur',
    Espl: 'Esplanade',
    Estate: 'Estates',
    Expy: 'Expressway',
    Exten: 'Extension',
    Fwy: 'Freeway',
    Gdns: 'Gardens',
    Grnds: 'Grounds',
    Harbr: 'Harbour',
    Hts: 'Heights',
    Hghlds: 'Highlands',
    Hwy: 'Highway',
    Imp: 'Impasse',
    Landng: 'Landing',
    Lmts: 'Limits',
    Lkout: 'Lookout',
    Mtn: 'Mountain',
    Orch: 'Orchard',
    Pk: 'Park',
    Pky: 'Parkway',
    Pass: 'Passage',
    Ptway: 'Pathway',
    Pl: 'Place',
    Plat: 'Plateau',
    Pt: 'Point',
    Pvt: 'Private',
    Prom: 'Promenade',
    Rg: 'Range',
    Rd: 'Road',
    Rdpt: 'Rond-point',
    Rte: 'Route',
    Rle: 'Ruelle',
    Sent: 'Sentier',
    Sq: 'Square',
    St: 'Street',
    Subdiv: 'Subdivision',
    Terr: 'Terrace',
    Tsse: 'Terrasse',
    Thick: 'Thicket',
    Tline: 'Townline',
    Trnabt: 'Turnabout',
    Villge: 'Village'
  };

  // https://www.canadapost.ca/tools/pg/manual/PGaddress-e.asp?ecid=murl10006450#1403220
  var street_directions = {
    ' E': ' East',
    ' N': ' North',
    ' NE': ' Northeast',
    ' NW': ' Northwest',
    ' S': ' South',
    ' SE': ' Southeast',
    ' SW': ' Southwest',
    ' W': ' West'
  };

  var pattern = '( \\d+)? (';
  for (var k in street_types) {
    pattern += k + '|' + street_types[k] + '|';
  }
  pattern = pattern.slice(0, -1) + ')(';
  for (var k in street_directions) {
    pattern += k + '|' + street_directions[k] + '|';
  }
  pattern = new RegExp(pattern.slice(0, -1) + ')?$');

  function replace($0, $1, $2, $3) {
    if ($1) {
      switch ($1.slice(-2)) {
        case '11':
        case '12':
        case '13':
          $1 += 'th';

          break;

        default:
          $1 += [ 'st', 'nd', 'rd' ][$1.slice(-1) - 1] || 'th';
      }
    } else {
      var $1 = '';
    }

    return $1 + ' ' + (street_types[$2] || $2) + (street_directions[$3] || $3 || '');
  }

  function fix_street_address(value) {
    value = ' ' + value;
    value = value.replace(pattern, replace);
    switch (true) {
      case value.slice(-19) == ' British Columbia 3':
        value = value.slice(0, -19) + ' Highway 3';

        break;

      case value.slice(-24) == ' Creston-Rykerts Highway':
        value = value.slice(0, -24) + ' Highway 3';

        break;

      case value.slice(-5) == ' Swan':
        value = value.slice(0, -5) + ' Swan Road';

        break;
    }

    return value.slice(1);
  }

  var localities = {
    'Arrow Creek': {
      lat: 49.133657,
      lng: -116.446142
    },
    Canyon: {
      lat: 49.0836154,
      lng: -116.4477734
    },
    Creston: {
      lat: 49.0955401,
      lng: -116.5135079
    },
    Erickson: {
      lat: 49.0912298,
      lng: -116.4661136
    },
    Lister: {
      lat: 49.050396,
      lng: -116.469498
    },
    'West Creston': {
      lat: 49.0814436,
      lng: -116.6088511
    }
  };

  function fix_locality(locality, lat, lng) {
    if (localities[locality]) {
      var shortest = Infinity;
      for (var k in localities) {
        var distance = Math.pow(lat - localities[k].lat, 2) + Math.pow(lng - localities[k].lng, 2);
        if (distance < shortest) {
          var shortest = distance;
          var locality = k;
        }
      }
    }

    return locality;
  }

  title.placeholder = '';

  var opts = {
    bounds: bounds,
    types: [ 'establishment' ]
  };

  var autocomplete = new google.maps.places.Autocomplete(title, opts);

  function title_handler() {
    for (var elements = post_body.getElementsByClassName('autofill'); elements.length; elements[0].classList.remove('autofill'));

    var place = this.getPlace();

    title.value = ' ' + place.name + ' ';
    title.value = title.value.replace(' Inc. ', ' ');
    title.value = title.value.replace(' Inc ', ' ');
    title.value = title.value.replace(' Ltd. ', ' ');
    title.value = title.value.replace(' Ltd ', ' ');
    title.value = title.value.slice(1, -1);
    title.classList.add('autofill');

    var index1 = place.formatted_address.indexOf(', ');
    var street_address = fix_street_address(place.formatted_address.slice(0, index1));

    var index2 = place.formatted_address.indexOf(', ', index1 + 2);
    var location = place.geometry.location;
    var locality = fix_locality(place.formatted_address.slice(index1 + 2, index2), location.lat(), location.lng());

    address.value = street_address + ', ' + locality + place.formatted_address.slice(index2);
    address.classList.add('autofill');

    if (place.formatted_phone_number) {
      phone.value = place.formatted_phone_number;
      phone.classList.add('autofill');
    }

    if (place.website) {
      website.value = place.website;
      website_handler();
      website.classList.add('autofill');
    }

    var value = [];

    if (place.types.indexOf('cafe') != -1 || place.types.indexOf('restaurant') != -1) {
      value.push('Restaurant');
    }

    if (place.types.indexOf('grocery_or_supermarket') != -1 || place.types.indexOf('store') != -1) {
      value.push('Retailer');
    }

    if (value.length) {
      business_type.value = value.join('\n');
      business_type.classList.add('autofill');
    }
  }
  google.maps.event.addListener(autocomplete, 'place_changed', title_handler);

  address.placeholder = '';

  var opts = {
    bounds: bounds,
    types: [ 'address' ]
  };

  var autocomplete = new google.maps.places.Autocomplete(address, opts);

  function address_handler() {
    for (var elements = post_body.getElementsByClassName('autofill'); elements.length; elements[0].classList.remove('autofill'));

    var place = this.getPlace();

    var value = address.value.slice(0, address.value.indexOf(', ')).split(' ');

    var index1 = place.formatted_address.indexOf(', ');
    var street_address = fix_street_address(place.formatted_address.slice(0, index1));

    var formatted_address = street_address.split(' ');
    if (value.length > formatted_address.length) {
      street_address = value.slice(0, -formatted_address.length).join(' ') + ' ' + street_address;
    }

    var index2 = place.formatted_address.indexOf(', ', index1 + 2);
    var location = place.geometry.location;
    var locality = fix_locality(place.formatted_address.slice(index1 + 2, index2), location.lat(), location.lng());

    address.value = street_address + ', ' + locality + place.formatted_address.slice(index2);
    address.classList.add('autofill');
  }
  google.maps.event.addListener(autocomplete, 'place_changed', address_handler);

  function website_handler(event) {
    function success_handler(data) {
      if (data) {
        if (event) {
          var elements = post_body.getElementsByClassName('autofill');
          while (elements.length) {
            elements[0].classList.remove('autofill');
          }
        }

        email.value = data;
        email.classList.add('autofill');
      }
    }

    if (website.value) {
      var data = {
        action: 'scrape',
        website: website.value };

      $.get(ajaxurl, data, success_handler);
    }
  }
  $(website).on('change', website_handler);

  function input_handler(event) {
    event.target.classList.remove('autofill');
  }
  $(post_body).on('input', input_handler);

  var opts = {
    minchars: -1,
    multiple: true,
    multipleSep: '\n'
  };

  $(document.getElementById('products')).suggest(ajaxurl + '?action=ajax-tag-search&tax=products', opts);

  var opts = {
    minchars: -1,
    multiple: true,
    multipleSep: '\n'
  };

  $(document.getElementById('farm-practices')).suggest(ajaxurl + '?action=ajax-tag-search&tax=farm-practices', opts);

  var opts = {
    minchars: -1,
    multiple: true,
    multipleSep: '\n'
  };

  $(document.getElementById('business-type')).suggest(ajaxurl + '?action=ajax-tag-search&tax=business-type', opts);

  var opts = {
    minchars: -1,
    multiple: true,
    multipleSep: '\n'
  };

  $(document.getElementById('available-at')).suggest(ajaxurl + '?action=ajax-tag-search&tax=available-at', opts);
});
