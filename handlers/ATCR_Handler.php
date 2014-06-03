<?php
/*******************************************************************************
** ATCR_Handler
** Abstract for handlers
** @since 1.0
*******************************************************************************/
abstract class ATCR_Handler {

    abstract protected function recordView($productID);
    abstract protected function recordAddToCart($productID);
    abstract protected function getViews($productID);
    abstract protected function getAddToCarts($productID);

}
?>
