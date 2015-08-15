<?php
/**
 * This file exposes configuration data to the frontend (Backbone)
 * application via the ConfigModel.
 */

require_once('../shared/Config.php');
require_once('../../../config/config.php');

print '{"welcomeMsg":"' . Config::get('welcomeMsg', 'Connected!') . '"}';