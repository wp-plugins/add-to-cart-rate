<?php
/*******************************************************************************
** atcrFilterData
** Filter all the data for nasty surprises in the input forms
** @since 1.0
*******************************************************************************/
function atcrFilterData($data) {
	if (is_array($data)) {
		foreach ($data as $key => $elem) {
			$data[$key] = atcrFilterData($elem);
		}
	} else {
		if (empty($data))
			return $data;

		$data = nl2br(trim(htmlspecialchars(wp_kses_post($data), ENT_COMPAT)));
		$breaks = array("\r\n", "\n", "\r");
		$data = str_replace($breaks, "", $data);

		if (get_magic_quotes_gpc())
			$data = stripslashes($data);
		$data = esc_sql($data);
	}
    return $data;
}
?>
