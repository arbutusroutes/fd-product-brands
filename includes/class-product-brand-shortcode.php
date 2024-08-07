<?php

class WooCommerce_Product_Brand_Shortcode extends WooCommerce_Product_Brand
{

  public function __construct()
  {
    parent::__construct();
    add_shortcode('product_brand_list', array($this, 'product_brand_list_shortcode'));
  }

  public function product_brand_list_shortcode($atts)
  {
    $atts = shortcode_atts(array(
      'orderby' => 'product_brand_weight',
      'order' => 'asc',
      'scroll' => 'true',
      'limit' => '12',
      'width' => '200px',
      'image_class' => 'brand-image'
    ), $atts, 'product_brand_list');

    $brands = get_terms('product_brand', array(
      'number' => $atts['limit'],
      'orderby' => $atts['orderby'],
      'order' => $atts['order'],
      'hide_empty' => false,
      'meta_query' => array(
        array(
          'key' => 'product_brand_logo',
          'compare' => 'EXISTS'
        )
      )
    ));

    $output = '';

    if (!empty($brands)) {
      $output .= '<div class="fd-product-brand brand-gallery main-carousel ">';

      if ($atts['scroll'] === 'false') {
        $output .= '<div class="row align-items-center row-cols-2 row-cols-md-4 row-cols-lg-5">';
      } else {
        $output .= '<div class="flickity-enabled">';
      }

      foreach ($brands as $brand) {
        $brand_id = $brand->term_id;
        $brand_name = $brand->name;
        $brand_slug = $brand->slug;
        $brand_image_id = get_term_meta($brand_id, 'product_brand_logo', true);
        $brand_image_url = wp_get_attachment_url($brand_image_id);

        $output .= '<div class="brand-item carousel-cell col">';
        $output .= '<a href="' . esc_url(add_query_arg('product_brand', $brand_slug, get_permalink(wc_get_page_id('shop')))) . '">';
        $output .= '<img class="' . $atts['image_class'] . '" src="' . $brand_image_url . '" alt="' . $brand_name . '" style="max-width: ' . $atts['width'] . ';" />';
        $output .= '</a>';
        $output .= '</div>';
      }

      $output .= '</div>';

      if ($atts['scroll'] === 'true') {

        $flickityOptions = [
          'groupCells' => true,
          'contain' => true,
          'draggable' => true
        ];

        $output .= '<script src="' .  plugin_dir_url(__DIR__) . 'public/js/flickity.pkgd.min.js"></script>';
        $output .= '<link rel="stylesheet" href="' . plugin_dir_url(__DIR__) . 'public/css/flickity.css" />';
        $output .= '<link rel="stylesheet" href="' . plugin_dir_url(__DIR__) . 'public/css/product-brands.css" />';
        $output .= '<script>jQuery(".brand-gallery").flickity(' . json_encode($flickityOptions) . ');</script>';
      }
      $output .= '</div>';
    }

    return $output;
  }

  private function get_total_quantity_by_term($term_id)
  {
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => -1,
      'tax_query' => array(
        array(
          'taxonomy' => 'product_brand',
          'field' => 'term_id',
          'terms' => $term_id
        )
      )
    );

    $products = new WP_Query($args);
    $total_quantity = 0;

    if ($products->have_posts()) {
      while ($products->have_posts()) {
        $products->the_post();
        $product = wc_get_product(get_the_ID());
        $total_quantity += $product->get_stock_quantity();
      }
      wp_reset_postdata();
    }

    return $total_quantity;
  }

  private function get_total_retail_value_by_term($term_id)
  {
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => -1,
      'tax_query' => array(
        array(
          'taxonomy' => 'product_brand',
          'field' => 'term_id',
          'terms' => $term_id
        )
      )
    );

    $products = new WP_Query($args);
    $total_value = 0;
    if ($products->have_posts()) {
      while ($products->have_posts()) {
        $products->the_post();
        $product = wc_get_product(get_the_ID());
        $total_value += $product->get_price();
      }
      wp_reset_postdata();
    }

    return $total_value;
  }
}

// In this class, we first define the `__construct()` method, which calls the parent constructor and adds the `product_brand_list` shortcode with the `product_brand_list_shortcode()` method as its callback.
// The `product_brand_list_shortcode()` method gets the shortcode attributes and uses `get_terms()` to retrieve all terms in the `product_brand` taxonomy that have an associated image. It then loops through each term and displays the image, name, and total quantity and retail value of the associated products.
// We also define two private helper methods called `get_total_quantity_by_term()` and `get_total_retail_value_by_term()` that calculate the total quantity and retail value of the products associated with a given term.
// To use this shortcode, you can simply add `[product_brand_list]` to any post or page. You can also pass the `orderby` and `order` attributes to sort the list by weight, total quantity, or total retail value. For example, `[product_brand_list orderby="product_brand_weight" order="desc"]` would display the list of product brands sorted by weight in descending order.
