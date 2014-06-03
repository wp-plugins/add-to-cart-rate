<?php
// Get the abstract
require_once('ATCR_Handler.php');

/*******************************************************************************
** ATCR_EDD_Handler
** Handler for Easy Digital Downloads
** @since 1.0
*******************************************************************************/
class ATCR_EDD_Handler extends ATCR_Handler {

    /*******************************************************************************
    ** __construct
    ** Construct and initialise the handler
    ** @since 1.0
    *******************************************************************************/
    public function __construct() {
        // Add the column to the products list view
        add_action('admin_init', array($this, 'addColumnToProductList'));

        // Hook in the function that detects a view
        add_action('template_redirect', array($this, 'detectProductView'));

        // Hook in the function that detects an add to cart
        add_action('edd_post_add_to_cart', array($this, 'detectAddToCart'), 10, 2);
    }

    /*******************************************************************************
    ** recordView
    ** Records the view when a single product page is viewed
    ** @param $productID the ID of the product
    ** @since 1.0
    *******************************************************************************/
    protected function recordView($productID) {
        $currentViews = get_post_meta($productID, 'atcr_single_page_views', true);

        // Sensible default
        if (!isset($currentViews) || empty($currentViews))
            $currentViews = 0;

        // Add view to current count and record for posterity
        $currentViews += 1;

        update_post_meta($productID, 'atcr_single_page_views', $currentViews);

        // Save sort meta
        $this->saveSortMeta($productID);
    }

    /*******************************************************************************
    ** recordAddToCart
    ** Records an add to cart when a product is added from a single product page
    ** @param $productID the ID of the product
    ** @since 1.0
    *******************************************************************************/
    protected function recordAddToCart($productID) {
        $currentAddToCarts = get_post_meta($productID, 'atcr_single_page_add_to_carts', true);

        // Sensible default
        if (!isset($currentAddToCarts) || empty($currentAddToCarts))
            $currentAddToCarts = 0;

        // Add view to current count and record for posterity
        $currentAddToCarts += 1;

        update_post_meta($productID, 'atcr_single_page_add_to_carts', $currentAddToCarts);

        // Save sort meta
        $this->saveSortMeta($productID);
    }

    /*******************************************************************************
    ** getViews
    ** Returns the view count for a single product page
    ** @param $productID the ID of the product
    ** @return current views for given product
    ** @since 1.0
    *******************************************************************************/
    protected function getViews($productID) {
        $currentViews = get_post_meta($productID, 'atcr_single_page_views', true);

        // Sensible default
        if (!isset($currentViews) || empty($currentViews))
            $currentViews = 0;

        return $currentViews;
    }

    /*******************************************************************************
    ** getAddToCarts
    ** Returns the add to cart count for a single product page
    ** @param $productID the ID of the product
    ** @return current add to carts for given product
    ** @since 1.0
    *******************************************************************************/
    protected function getAddToCarts($productID) {
        $currentAddToCarts = get_post_meta($productID, 'atcr_single_page_add_to_carts', true);

        // Sensible default
        if (!isset($currentAddToCarts) || empty($currentAddToCarts))
            $currentAddToCarts = 0;

        return $currentAddToCarts;
    }

    /*******************************************************************************
    ** detectProductView
    ** Detects a view on a single product page and records it
    ** @since 1.0
    *******************************************************************************/
    function detectProductView() {
        global $post;

        // Validate if this is a product page
        if (get_post_type($post) != 'download')
            return;

        $productID = $post->ID;

        // Record the view
        $this->recordView($productID);

    }

    /*******************************************************************************
    ** detectAddToCart
    ** Detects an add to cart on a product
    ** @param $download_id the product id that was added to cart
    ** @param $options cart options
    ** @since 1.0
    *******************************************************************************/
    function detectAddToCart($download_id, $options) {
        $requestURI = trailingslashit(atcrFilterData($_SERVER['HTTP_REFERER']));
        $productURL = trailingslashit(get_permalink($download_id));

        // Only record the add to cart if it came from the single product page
        if (strcasecmp($requestURI, $productURL) === 0) {
            // Record the add to cart
            $this->recordAddToCart($download_id);
        }
    }

    /*******************************************************************************
    ** addColumnToProductList
    ** Adds the actions and filters required to add the ATCR column to the
    ** products list view
    ** @since 1.0
    *******************************************************************************/
    function addColumnToProductList() {
        add_filter('manage_download_columns', array($this, 'filterProductColumns'), 10, 1);
        add_filter('manage_edit-download_columns', array($this, 'filterProductColumns'), 10, 1);
    	add_action('manage_posts_custom_column', array($this, 'showColumnInList'), 10, 1);
        add_filter('manage_edit-download_sortable_columns', array($this, 'sortColumnInList'));
		add_filter('request', array($this, 'columnOrderBy'));
    }

    /*******************************************************************************
    ** filterProductColumns
    ** Add the Add To Cart Rate column
    ** @param $columns array of columns
    ** @return adjusted array of columns
    ** @since 1.0
    *******************************************************************************/
    function filterProductColumns($columns) {
        if (!isset($columns['date'])) {
            $newColumns = $columns;
        } else {
            $newColumns = array();
            $index = 0;
            foreach($columns as $key => $column) {
                if ($key=='date')
                    $newColumns['add-to-cart-rate'] = null;
                $newColumns[$key] = $column;
            }
        }

        $newColumns['add-to-cart-rate'] = 'Add To Cart Rate';
        echo 'new columns: ' . print_r($newColumns, true);
        return $newColumns;
    }

    /*******************************************************************************
    ** showColumnInList
    ** Show the value of the ATCR
    ** @param $column the current column being shown
    ** @since 1.0
    *******************************************************************************/
    function showColumnInList($column) {
        global $typenow;
    	global $post;

    	if ($typenow != 'download')
            return;

		if ($column == 'add-to-cart-rate') {
            $views = $this->getViews($post->ID);
            $addToCarts = $this->getAddToCarts($post->ID);

            if ($views == 0) {
                $rate = 0.00;
            } else {
                $rate = ($addToCarts / $views) * 100;
            }

			echo number_format($rate, 2) . '%';
		}

    }

    /*******************************************************************************
    ** sortColumnInList
    ** Add the column to the sortable columns list
    ** @param $columns columns that are sortable
    ** @return adjusted columns that are sortable
    ** @since 1.0
    *******************************************************************************/
    function sortColumnInList($columns) {
		$custom = array(
            'add-to-cart-rate' => 'add-to-cart-rate'
		);

		return wp_parse_args($custom, $columns);
	}

    /*******************************************************************************
    ** columnOrderBy
    ** Tell the orderby to use the special sort meta value when sorting the
    ** Add To Cart Rate column
    ** @param $vars array of options for the query
    ** @return adjusted query options array
    ** @since 1.0
    *******************************************************************************/
    function columnOrderBy($vars) {
		if (!isset($vars['orderby']))
            return $vars;

        if ($vars['orderby'] == 'add-to-cart-rate') {
			$vars = array_merge($vars, array(
				'meta_key' => 'atcr_sort',
				'orderby' => 'meta_value_num'
			));
		}

		return $vars;
	}

    /*******************************************************************************
    ** saveSortMeta
    ** Save the sort meta so that the product page can be sorted properly with the
    ** latest values
    ** @param $productID the ID of the product
    ** @since 1.0
    *******************************************************************************/
    function saveSortMeta($productID) {
        $views = $this->getViews($productID);
        $addToCarts = $this->getAddToCarts($productID);

        if ($views == 0) {
            $rate = 0.00;
        } else {
            $rate = ($addToCarts / $views) * 100;
        }

        $atcr = number_format($rate, 2);
        update_post_meta($productID, 'atcr_sort', $atcr);
    }
}
?>
