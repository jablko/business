<?php

function my_html_entity_decode($string) {
  return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
}

function my_parse_url($url) {
  if (substr($url, 0, 2) == '//') {
    $parts = parse_url("foo:$url");
    unset($parts['scheme']);

    return $parts;
  }

  return parse_url($url);
}

function my_array_sum($array) {
  list (, $result) = each($array);
  while (list (, $value) = each($array)) {
    $result += $value;
  }

  return $result;
}

function format_address($value) {
  if (substr($value, -8) == ', Canada') {
    $value = substr($value, 0, -8);
  }

  return preg_replace('/^(.*?), /', '<div><strong>$1</strong></div>', esc_html($value));
}

function telephone_subscriber($value) {
  $value = preg_replace('/\D/', '', $value);
  if (strlen($value) == 10) {
    return substr($value, 0, 3) . '-' . substr($value, 3, 3) . '-' . substr($value, 6);
  }

  return $value;
}

function format_website($value) {
  return preg_replace('/^https?:\/\/(?:www\.)?/i', '', $value);
}

function short_title($value) {
  switch ($value) {
    case 'Creston Hotel and Jimmy\'s Pub':
      return 'Jimmy\'s Pub';

    case 'Kootenay Natural Meats and Goat River Farms':
      return 'Kootenay Natural Meats';
  }

  return preg_replace('/ \(.*/', '', $value);
}

function format_terms($terms) {
  foreach ($terms as $i => $term) {
    $terms[$i] = '<a class="tag-' . $term->slug . '" rel=tag href="' . get_term_link($term) . '">' . wptexturize(short_title($term->name)) . '</a>';
  }

  return implode(', ', $terms);
}

function group_branches($tree, $branches, $opts) {
  $groups = array();
  foreach ($branches as $term_id => $name) {
    $terms = array();

    if ($tree[$term_id]) {
      $branch_groups = group_branches($tree, $tree[$term_id], $opts);
      if ($opts['short']) {
        foreach ($branch_groups as $group_term_id => $group_terms) {
          if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
            $terms += $group_terms;
          }
        }

        if ($name || count($terms) > 3) {
          foreach ($branch_groups as $group_term_id => $group_terms) {
            if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
              unset($branch_groups[$group_term_id]);
            }
          }
        } else {
          $terms = array();
        }
      } else {
        foreach ($branch_groups as $group_term_id => $group_terms) {
          if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
            $terms[current($group_terms)] = $group_terms;
          }
        }

        if ($name || count($terms) > 1) {
          ksort($terms);
          $terms = my_array_sum($terms);

          foreach ($branch_groups as $group_term_id => $group_terms) {
            if (count($group_terms) <= ($group_terms[$group_term_id] ? 4 : 3)) {
              unset($branch_groups[$group_term_id]);
            }
          }
        } else {
          $terms = current($terms);
          if (count($terms) == 1 && $branch_groups[key($terms)]) {
            unset($branch_groups[key($terms)]);
          } else {
            $terms = array();
          }
        }
      }
    } else {
      $branch_groups = array();
    }

    switch (true) {
      case $name:
        $terms = array($term_id => $name) + $terms;

      case $terms:
        $branch_groups = array($term_id => $terms) + $branch_groups;

        break;

      default:
        reset($branch_groups);
    }

    $groups[get_term(key($branch_groups), 'products')->name] = $branch_groups;
  }

  ksort($groups);
  return my_array_sum($groups);
}

function group_terms($terms, $opts = null) {
  $tree = array();
  foreach ($terms as $i => $term) {
    $tree[$term->parent][$term->term_id] = $term->name;
    while ($term->parent) {
      $term = get_term($term->parent, $term->taxonomy);
      if (isset($tree[$term->parent][$term->term_id])) {
        break;
      }
      $tree[$term->parent][$term->term_id] = false;
    }
  }

  return group_branches($tree, $tree[0], $opts);
}

function is_in_season($value) {
  list ($from, $to) = explode(' to ', $value, 2);

  $from = date_parse($from);
  $from = array($from['month'], $from['day']);

  $to = date_parse($to);
  $to = array($to['month'], $to['day']);

  $now = getdate();
  $now = array($now['mon'], $now['mday']);

  return $to >= $from ? $now >= $from && $now <= $to : $now >= $from || $now <= $to;
}

function format_in_season($value) {
  $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

  $values = explode(' to ', $value, 2);
  foreach ($values as $i => $value) {
    $value = date_parse($value);
    if ($value['month'] && $value['day']) {
      $values[$i] = $months[$value['month'] - 1] . ' ' . $value['day'];
      switch ($value['day']) {
        case 11:
        case 12:
        case 13:
          $values[$i] .= 'th';

          break;

        default:
          switch ($value['day'] % 10) {
            case 1:
              $values[$i] .= 'st';

              break;

            case 2:
              $values[$i] .= 'nd';

              break;

            case 3:
              $values[$i] .= 'rd';

              break;

            default:
              $values[$i] .= 'th';
          }
      }
    }
  }

  return implode(' to ', $values);
}

function format_products($terms) {
  static $all_in_season;
  if (!$all_in_season) {
    $all_in_season = get_option('in_season');
  }

  foreach ($terms as $term_id => $name) {
    $in_season = $all_in_season[$term_id];
    if ($in_season) {
      $terms[$term_id] = '<a rel=tag title="' . esc_attr(sprintf(__('In Season %s'), format_in_season($in_season))) . '" href="' . get_term_link($term_id, 'products') . '">' . $name . '</a>';
      if (is_in_season($in_season)) {
        $terms[$term_id] = "<strong>$terms[$term_id]</strong>";
      }
    } else {
      $terms[$term_id] = '<a rel=tag href="' . get_term_link($term_id, 'products') . '">' . $name . '</a>';
    }
  }

  return implode(', ', $terms);
}

function format_posts($posts) {
  for ($i = 0; $i < count($posts); $i++) {
    $posts[$i] = '<a href="' . get_permalink($posts[$i]) . '">' . short_title(get_the_title($posts[$i])) . '</a>';
  }

  return implode(', ', $posts);
}

function meta_box($post) {
  require plugin_dir_path(__FILE__) . 'business/meta-box.php';
}

function register_meta_box_cb() {
  add_meta_box('business', __('Business'), 'meta_box');
}

function init() {
  $labels = array(
    'name' => __('Businesses'),
    'singular_name' => __('Business'),
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'supports' => array('title'),
    'register_meta_box_cb' => 'register_meta_box_cb',
  );

  register_post_type('business', $args);

  $labels = array(
    'name' => __('Products'),
    'singular_name' => __('Product'),
  );

  $args = array(
    'labels' => $labels,
    'meta_box_cb' => false,
    'hierarchical' => true,
  );

  register_taxonomy('products', 'business', $args);

  $labels = array(
    'name' => __('Farm Practices'),
    'singular_name' => __('Farm Practices'),
  );

  $args = array(
    'labels' => $labels,
    'meta_box_cb' => false,
  );

  register_taxonomy('farm-practices', 'business', $args);

  $labels = array(
    'name' => __('Business Types'),
    'singular_name' => __('Business Type'),
  );

  $args = array(
    'labels' => $labels,
    'meta_box_cb' => false,
  );

  register_taxonomy('business-type', 'business', $args);

  $labels = array(
    'name' => __('Available at These Locations'),
    'singular_name' => __('Available at This Location'),
  );

  $args = array(
    'labels' => $labels,
    'meta_box_cb' => false,
  );

  register_taxonomy('available-at', 'business', $args);
}
add_action('init', 'init');

function term_search_min_chars($term_search_min_chars, $tax) {
  switch ($tax->name) {
    case 'products':
    case 'farm-practices':
    case 'business-type':
    case 'available-at':
      return -1;
  }

  return $term_search_min_chars;
}
add_filter('term_search_min_chars', 'term_search_min_chars', 10, 2);

function wp_ajax_scrape() {
  if (isset($_GET['website'])) {
    $website = esc_url_raw($_GET['website']);
    if ($website) {
      $body = wp_remote_retrieve_body(wp_safe_remote_get($website));
      preg_match('/[-.\d_a-z~]+@[-.\d_a-z~]+\.[a-z]{2,}/', $body, $matches);
      if ($matches) {
        echo $matches[0];
      }
    }
  }

  wp_die();
}
add_action('wp_ajax_scrape', 'wp_ajax_scrape');

function post_type_link($post_link, $post) {
  global $wp_rewrite;

  if ($post->post_type == 'business' && !in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) {
    $struct = $wp_rewrite->get_page_permastruct();
    if ($struct) {
      $post_link = str_replace('%pagename%', $post->post_name, $struct);
      $post_link = user_trailingslashit($post_link);
      $post_link = home_url($post_link);
    }
  }

  return $post_link;
}
add_filter('post_type_link', 'post_type_link', 10, 2);

function term_link($termlink, $term, $taxonomy) {
  global $wp_rewrite;

  if ($taxonomy == 'available-at') {
    $struct = $wp_rewrite->get_page_permastruct();
    if ($struct) {
      $termlink = str_replace('%pagename%', $term->slug, $struct);
      $termlink = user_trailingslashit($termlink);
      $termlink = home_url($termlink);
    } else {
      $struct = $wp_rewrite->get_extra_permastruct('business');
      if ($struct) {
        $termlink = str_replace('%business%', $term->slug, $struct);
        $termlink = user_trailingslashit($termlink);
        $termlink = home_url($termlink);
      } else {
        $termlink = add_query_arg('business', $term->slug, home_url());
      }
    }
  }

  return $termlink;
}
add_filter('term_link', 'term_link', 10, 3);

function pre_get_posts($query) {
  if (!$query->queried_object && $query->get('pagename') && !$query->get('post_type')) {
    $query->queried_object = get_page_by_path($query->get('pagename'), OBJECT, array('attachment', 'business', 'page'));
    if ($query->queried_object) {
      $query->queried_object_id = $query->queried_object->ID;
      $query->set('post_type', $query->queried_object->post_type);
      if ($query->queried_object->post_type != 'page') {
        $query->is_single = true;
      }
    } else {
      $query->set('available-at', $query->get('pagename'));
      $query->set('pagename', '');
      $query->is_singular = false;
      $query->is_tax = true;
      $query->is_page = false;
      $query->is_archive = true;
    }
  }
}
add_action('pre_get_posts', 'pre_get_posts');

// http://norvig.com/spell-correct.html
function edits($word) {
  $edits = array();
  for ($i = 0; $i < strlen($word); $i++) {
    $edits[] = substr($word, 0, $i) . substr($word, $i + 1);
  }

  for ($i = 0; $i < strlen($word) - 1; $i++) {
    $edits[] = substr($word, 0, $i) . $word[$i + 1] . $word[$i] . substr($word, $i + 2);
  }

  for ($i = 0; $i < strlen($word); $i++) {
    foreach (range('a', 'z') as $letter) {
      $edits[] = substr($word, 0, $i) . $letter . substr($word, $i + 1);
    }
  }

  for ($i = 0; $i < strlen($word) + 1; $i++) {
    foreach (range('a', 'z') as $letter) {
      $edits[] = substr($word, 0, $i) . $letter . substr($word, $i);
    }
  }

  return $edits;
}

function fix_title($value) {
  static $dbh;
  if (!$dbh) {
    $dbh = new PDO('sqlite:' . plugin_dir_path(__FILE__) . 'business/wordlist.db');
  }

  $wordlist = array(
    'charcuterie-style' => 'Charcuterie-Style',
    'grass-finished' => 'Grass-Finished',
    'greenhouse' => 'Greenhouse',
    'hazelnuts' => 'Hazelnuts',
    'tomatoes' => 'Tomatoes',
    'zucchini' => 'Zucchini',
  );

  $value = stripslashes($value);
  $value = remove_accents($value);
  $value = strtolower($value);

  $value = " $value ";

  $search = array(
    ' inc. ',
    ' inc ',
    ' ltd. ',
    ' ltd ',
  );

  $value = str_replace($search, ' ', $value);

  $value = substr($value, 1, -1);

  switch ($value) {
    case 'apple':
      return 'Apples';

    case 'bean':
      return 'Beans';

    case 'beet':
      return 'Beets';

    case 'bokchoi':
      return 'Bok Choy';

    case 'carrot':
      return 'Carrots';

    case 'cherry':
      return 'Cherries';

    case 'cucumber':
      return 'Cucumbers';

    case 'fruits':
      return 'Fruit';

    case 'gailan':
      return 'Kai-Lan';

    case 'grains':
      return 'Grain';

    case 'leek':
      return 'Leeks';

    case 'meats':
      return 'Meat';

    case 'melon':
      return 'Melons';

    case 'onion':
      return 'Onions';

    case 'parsnip':
      return 'Parsnips';

    case 'pepper':
      return 'Peppers';

    case 'plum':
      return 'Plums';

    case 'potato':
      return 'Potatoes';

    case 'radish':
      return 'Radishes';

    case 'rose wine':
      return 'Rosé Wine';

    case 'scallion':
      return 'Scallions';

    case 'strawberry':
      return 'Strawberries';

    case 'teas':
      return 'Tea';

    case 'turnip':
      return 'Turnips';
  }

  $parts = preg_split('/(co-op|[a-z](?:\'?[a-z])*)/', $value, -1, PREG_SPLIT_DELIM_CAPTURE);
  for ($i = 1; $i < count($parts); $i += 2) {
    if ($parts[$i + 1] == ' ' && $i < count($parts) - 2) {
      foreach (array($parts[$i] . $parts[$i + 2], $parts[$i] . '-' . $parts[$i + 2]) as $compound) {
        if (isset($wordlist[$compound])) {
          array_splice($parts, $i, 3, $wordlist[$compound]);

          continue 2;
        }
      }
    }

    if ($parts[$i] != 'formerly' || $parts[$i - 1] != ' (') {
      // http://daringfireball.net/2008/05/title_case
      switch ($parts[$i]) {
        case 'a':
        case 'an':
        case 'and':
        case 'as':
        case 'at':
        case 'but':
        case 'by':
        case 'en':
        case 'for':
        case 'if':
        case 'in':
        case 'of':
        case 'on':
        case 'or':
        case 'the':
        case 'to':
        case 'v':
        case 'via':
        case 'vs':
          switch ($i) {
            case 1:
            case count($parts) - 2:
              $parts[$i] = ucfirst($parts[$i]);
          }

          break;

        default:
          $sth = $dbh->prepare('SELECT v FROM wordlist WHERE k = ?');
          $sth->execute(array($parts[$i]));
          $result = $sth->fetchColumn();
          switch (true) {
            case $result:
              $parts[$i] = $result;

              break;

            case strlen($parts[$i]) < 10:
              $edits = edits($parts[$i]);
              foreach ($edits as $edit1) {
                if (isset($wordlist[$edit1])) {
                  $parts[$i] = $wordlist[$edit1];

                  break 2;
                }
              }
              foreach ($edits as $edit1) {
                foreach (edits($edit1) as $edit2) {
                  if (isset($wordlist[$edit2])) {
                    $parts[$i] = $wordlist[$edit2];

                    break 3;
                  }
                }
              }

            default:
              $parts[$i] = ucfirst($parts[$i]);
          }
      }
    }
  }

  return implode($parts);
}

function pre_insert_term($value, $taxonomy) {
  global $wpdb;

  switch ($taxonomy) {
    case 'products':
    case 'farm-practices':
    case 'business-type':
    case 'available-at':
      $value = fix_title($value);
      $query = "
        SELECT t.term_id
        FROM $wpdb->terms AS t
          INNER JOIN $wpdb->term_taxonomy AS tt
            ON tt.term_id = t.term_id
        WHERE t.name = %s
          AND tt.taxonomy = %s
        LIMIT 1
      ";
      $query = $wpdb->prepare($query, $value, $taxonomy);
      if ($wpdb->get_var($query)) {
        return new WP_Error('term_exists', __('That name already exists in this taxonomy.'));
      }
  }

  return $value;
}
add_filter('pre_insert_term', 'pre_insert_term', 10, 2);

function pre_term_term_id($value) {
  global $this_id;

  $this_id = $value;

  return $value;
}
add_filter('pre_term_term_id', 'pre_term_term_id');

function pre_term_name($value, $taxonomy) {
  global $wpdb, $this_id;

  switch ($taxonomy) {
    case 'products':
    case 'farm-practices':
    case 'business-type':
    case 'available-at':
      $value = fix_title($value);
      $query = "
        SELECT t.term_id
        FROM $wpdb->terms AS t
          INNER JOIN $wpdb->term_taxonomy AS tt
            ON tt.term_id = t.term_id
        WHERE t.name = %s
          AND tt.taxonomy = %s
        LIMIT 1
      ";
      $query = $wpdb->prepare($query, $value, $taxonomy);
      $that_id = $wpdb->get_var($query);
      if ($that_id && $that_id != $this_id) {
        return;
      }
  }

  return $value;
}
add_filter('pre_term_name', 'pre_term_name', 10, 2);

function pre_post_title($value) {
  if (!get_post_type() || get_post_type() == 'business') {
    return fix_title($value);
  }

  return $value;
}
add_filter('pre_post_title', 'pre_post_title');

function my_sanitize_title($title, $raw_title, $context) {
  if ($context == 'save' && (!get_post_type() || get_post_type() == 'business')) {
    return short_title($title);
  }

  return $title;
}
add_filter('sanitize_title', 'my_sanitize_title', 9, 3);

function sanitize_post_meta_phone($meta_value) {
  if (!get_post_type() || get_post_type() == 'business') {
    $value = preg_replace('/\D/', '', $meta_value);
    switch (true) {
      case strlen($value) == 11 && $value[0] == '1':
        $value = substr($value, 1);

      case strlen($value) == 10:
        return '(' . substr($value, 0, 3) . ') ' . substr($value, 3, 3) . '-' . substr($value, 6);
    }
  }

  return $meta_value;
}
add_filter('sanitize_post_meta_phone', 'sanitize_post_meta_phone');

function sanitize_post_meta_email($meta_value) {
  return strtolower($meta_value);
}
add_filter('sanitize_post_meta_email', 'sanitize_post_meta_email');

function deleted_post_meta($meta_ids, $object_id, $meta_key) {
  if ($meta_key == 'website') {
    delete_post_meta($object_id, 'website_up');
  }
}
add_action('deleted_post_meta', 'deleted_post_meta', 10, 3);

function set_object_terms($object_id, $terms, $tt_ids, $taxonomy) {
  if ($taxonomy == 'business-type') {
    $value = array();
    if (has_term('Farm Gate Sales', 'business-type', $object_id)) {
      $value[] = 'On Farm';
    }

    if (has_term('Farmers\' Market Vendor', 'business-type', $object_id)) {
      $value[] = 'Farmers\' Market';
    }

    if ($value) {
      wp_add_object_terms($object_id, $value, 'available-at');
    }
  }
}
add_action('set_object_terms', 'set_object_terms', 10, 4);

function update_data() {
  remove_filter('pre_post_title', 'pre_post_title');

  $upload_dir = wp_upload_dir();
  $filename = $upload_dir['basedir'] . '/data.json';

  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $items = array();

  $args = array(
    'numberposts' => -1,
    'post_type' => 'business',
  );

  foreach (get_posts($args) as $post) {
    $custom_fields = get_post_custom($post->ID);
    list ($address) = $custom_fields['address'];
    list ($latlng) = $custom_fields['latlng'];
    list ($phone) = $custom_fields['phone'];

    $item = array(
      'label' => my_html_entity_decode(wptexturize($post->post_title)),
      'permalink' => get_permalink($post),
    );

    $item += array_filter(compact('address', 'latlng', 'phone'));

    $website = esc_url_raw($custom_fields['website'][0]);
    if ($website) {
      $website = strtolower($website);
      $website = preg_replace('/^https?:\/\/(?:www\.)?([-.\d_a-z~]+(?:\/[-.\/\d_a-z~]+)?)\/*/', 'http://$1', $website);

      $args = array(
        'timeout' => 300,
        'redirection' => 0,
      );

      $response = wp_safe_remote_get($website, $args);

      $args['redirection'] = 1;
      $args['reject_unsafe_urls'] = true;

      for ($i = 0; $i < 5; $i++) {
        if (is_wp_error($response)) {
          delete_post_meta($post->ID, 'website_up');

          break;
        }

        $redirect_response = WP_Http::handle_redirects($website, $args, $response);
        if (!$redirect_response) {
          $website = preg_replace('/^(https?:\/\/[-.\d_a-z~]+)\/+$/i', '$1', $website);
          //update_post_meta($post->ID, 'website', $website);
          update_post_meta($post->ID, 'website_up', $website);

          $body = wp_remote_retrieve_body($response);
          $doc = new DOMDocument();
          @$doc->loadHTML($body);

          $url_parts = my_parse_url($website);
          $urls = array();
          foreach ($doc->getElementsByTagName('img') as $node) {
            $relative_url_parts = my_parse_url($node->getAttribute('src'));
            $relative_url_parts['path'] = str_replace(' ', '%20', $relative_url_parts['path']);
            $relative_url_parts['query'] = str_replace(' ', '+', $relative_url_parts['query']);

            if (!$relative_url_parts['scheme']) {
              $relative_url_parts['scheme'] = $url_parts['scheme'];
              if (!$relative_url_parts['host']) {
                $relative_url_parts['host'] = $url_parts['host'];
                $relative_url_parts['port'] = $url_parts['port'];
                if (!$relative_url_parts['path']) {
                  $relative_url_parts['path'] = $url_parts['path'];
                  if (!$relative_url_parts['query']) {
                    $relative_url_parts['query'] = $url_parts['query'];
                  }
                } else if ($relative_url_parts['path'][0] != '/') {
                  $relative_url_parts['path'] = "/$relative_url_parts[path]";
                  if ($url_parts['path']) {
                    $relative_url_parts['path'] = substr($url_parts['path'], 0, strrpos($url_parts['path'], '/')) . $relative_url_parts['path'];
                  }
                }
              }
            }

            do {
              $relative_url_parts['path'] = preg_replace('/[^\/]+\/\.\.(?:\/|$)/', '', $relative_url_parts['path'], 1, $count);
            } while ($count);

            $url = "$relative_url_parts[scheme]://$relative_url_parts[host]";
            if ($relative_url_parts['port']) {
              $url .= ":$relative_url_parts[port]";
            }
            $url .= $relative_url_parts['path'];
            if ($relative_url_parts['query']) {
              $url .= "?$relative_url_parts[query]";
            }

            $urls[$url] = $url;
          }

          if ($urls) {
            $args = array(
              'post_parent' => $post->ID,
              'post_type' => 'attachment',
            );

            foreach (get_children($args) as $attachment) {
              wp_delete_attachment($attachment->ID);
            }

            $found = false;
            foreach ($urls as $url) {
              $tmp_name = download_url($url);
              list ($width, $height) = getimagesize($tmp_name);
              if ($width > 40 && $height > 40 && $width + $height > 180) {
                preg_match('/[^\/]+?(?=\/?(?:$|\?))/', $url, $matches);
                $file_array = array(
                  'name' => $matches[0],
                  'tmp_name' => $tmp_name,
                );

                $thumbnail_id = media_handle_sideload($file_array, $post->ID);
                if (!$found) {
                  set_post_thumbnail($post, $thumbnail_id);
                  $found = true;
                }
              }
            }
          }

          break;
        }

        $website = WP_Http::make_absolute_url(wp_remote_retrieve_header($response, 'location'), $website);
        $response = $redirect_response;
      }
    }

    $value = wp_get_object_terms($post->ID, 'products', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $item['products'] = count($value) == 1 ? $value[0] : $value;
    }

    $value = wp_get_object_terms($post->ID, 'farm-practices', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $item['farm-practices'] = count($value) == 1 ? $value[0] : $value;
    }

    $value = wp_get_object_terms($post->ID, 'business-type', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $item['business-type'] = count($value) == 1 ? $value[0] : $value;
    }

    $value = wp_get_object_terms($post->ID, 'available-at', array('fields' => 'names'));
    if ($value) {
      $value = array_map('my_html_entity_decode', array_map('wptexturize', $value));
      $item['available-at'] = count($value) == 1 ? $value[0] : $value;
    }

    $thumbnail_id = get_post_thumbnail_id($post->ID);
    if ($thumbnail_id) {
      list ($item['thumbnail-src'], $item['thumbnail-width'], $item['thumbnail-height']) = wp_get_attachment_image_src($thumbnail_id);
    }

    switch (true) {
      case has_term('Restaurant', 'business-type', $post):
        $item['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-1f77b4.png';

        break;

      case has_term('Retailer', 'business-type', $post):
        $item['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-2ca02c.png';

        break;

      default:
        $item['icon'] = get_stylesheet_directory_uri() . '/maki/marker-36-d62728.png';
    }

    $items[] = $item;
  }

  $all_products = array();
  foreach (get_terms('products') as $term) {
    $all_products[$term->term_id] = $term;
  }

  $all_in_season = get_option('in_season');
  foreach ($all_products as $term) {
    $in_season = $all_in_season[$term->term_id];
    if ($term->parent || $in_season) {
      $item = array(
        'label' => my_html_entity_decode(wptexturize($term->name)),
        'type' => 'product',
      );

      if ($term->parent) {
        $item['parent'] = my_html_entity_decode(wptexturize($all_products[$term->parent]->name));
      }

      if ($in_season) {
        $item['in-season'] = format_in_season($in_season);
      }

      $items[] = $item;
    }
  }

  $data = array(
    'items' => $items,
  );

  file_put_contents($filename, wp_json_encode($data));
}
add_action('update_data', 'update_data');

function save_post_business($post_id) {
  if (isset($_POST['address'])) {
    $value = sanitize_text_field($_POST['address']);
    if ($value) {
      $query_data = array(
        'address' => $value,
        'bounds' => '49,-117.2002313109224|49.54520019716734,-115.82678448907757',
      );

      $body = json_decode(wp_remote_retrieve_body(wp_safe_remote_get('http://maps.googleapis.com/maps/api/geocode/json?' . http_build_query($query_data))));
      if ($body->status == 'OK') {
        list ($result) = $body->results;
        //$value = $result->formatted_address;

        $location = $result->geometry->location;
        update_post_meta($post_id, 'latlng', "$location->lat,$location->lng");
      } else {
        delete_post_meta($post_id, 'latlng');
      }

      update_post_meta($post_id, 'address', $value);
    } else {
      delete_post_meta($post_id, 'address');
      delete_post_meta($post_id, 'latlng');
    }
  }

  if (isset($_POST['phone'])) {
    $value = sanitize_text_field($_POST['phone']);
    if ($value) {
      update_post_meta($post_id, 'phone', $value);
    } else {
      delete_post_meta($post_id, 'phone');
    }
  }

  if (isset($_POST['email'])) {
    $value = sanitize_email($_POST['email']);
    if ($value) {
      update_post_meta($post_id, 'email', $value);
    } else {
      delete_post_meta($post_id, 'email');
    }
  }

  if (isset($_POST['website'])) {
    $value = sanitize_text_field($_POST['website']);
    if ($value) {
      update_post_meta($post_id, 'website', $value);
    } else {
      delete_post_meta($post_id, 'website');
    }
  }

  if (isset($_POST['products'])) {
    $value = array_map('fix_title', array_filter(array_map('sanitize_text_field', explode("\n", $_POST['products']))));
    wp_set_object_terms($post_id, $value, 'products');
  }

  if (isset($_POST['farm-practices'])) {
    $value = array_map('fix_title', array_filter(array_map('sanitize_text_field', explode("\n", $_POST['farm-practices']))));
    wp_set_object_terms($post_id, $value, 'farm-practices');
  }

  if (isset($_POST['business-type'])) {
    $value = array_map('fix_title', array_filter(array_map('sanitize_text_field', explode("\n", $_POST['business-type']))));
    wp_set_object_terms($post_id, $value, 'business-type');
  }

  if (isset($_POST['available-at'])) {
    $value = array_map('fix_title', array_filter(array_map('sanitize_text_field', explode("\n", $_POST['available-at']))));
    wp_set_object_terms($post_id, $value, 'available-at');
  }

  if (get_post_status($post_id) == 'publish') {
    wp_clear_scheduled_hook('update_data');
    wp_schedule_event(time(), 'daily', 'update_data');
  }
}
add_action('save_post_business', 'save_post_business');

function before_delete_post($postid) {
  if (get_post_type($postid) == 'business') {
    $args = array(
      'post_parent' => $postid,
      'post_type' => 'attachment',
    );

    foreach (get_children($args) as $attachment) {
      wp_delete_attachment($attachment->ID);
    }
  }
}
add_action('before_delete_post', 'before_delete_post');

function products_add_form_fields($term) {
  require plugin_dir_path(__FILE__) . 'business/products-add-form-fields.php';
}
add_action('products_add_form_fields', 'products_add_form_fields');

function products_edit_form_fields($term) {
  require plugin_dir_path(__FILE__) . 'business/products-edit-form-fields.php';
}
add_action('products_edit_form_fields', 'products_edit_form_fields');

function sanitize_option_in_season($all_in_season) {
  $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

  foreach ($all_in_season as $term_id => $in_season) {
    $values = explode(' to ', $in_season, 2);
    foreach ($values as $i => $value) {
      $value = date_parse($value);
      if ($value['month'] && $value['day']) {
        $values[$i] = $months[$value['month'] - 1] . ' ' . $value['day'];
      }
    }
    $all_in_season[$term_id] = implode(' to ', $values);
  }

  return $all_in_season;
}
add_filter('sanitize_option_in_season', 'sanitize_option_in_season');

function save_products($term_id) {
  if (isset($_POST['in-season'])) {
    $all_in_season = get_option('in_season');
    $value = sanitize_text_field($_POST['in-season']);
    if ($value) {
      $all_in_season[$term_id] = $value;
    } else {
      unset($all_in_season[$term_id]);
    }
    update_option('in_season', $all_in_season);
  }
}
add_action('create_products', 'save_products');
add_action('edited_products', 'save_products');

function xmlrpc_blog_options($blog_options) {
  $blog_options['in_season'] = array(
    'option' => 'in_season',
  );

  return $blog_options;
}
add_filter('xmlrpc_blog_options', 'xmlrpc_blog_options');

// WP::register_globals() WTF?!
function my_wp() {
  unset($GLOBALS['posts']);
}
add_action('wp', 'my_wp');
