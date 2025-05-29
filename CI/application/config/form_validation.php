<?php
if (strpos(uri_string(), ADMIN_DIR_NAME) === 0) {
    require_once(APPPATH.'config/validation/form_validation_admin.php');
} else {
    require_once(APPPATH.'config/validation/form_validation_customer.php');
}

