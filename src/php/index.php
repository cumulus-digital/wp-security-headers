<?php

namespace CUMULUS\Wordpress\SecurityHeaders;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

require __DIR__ . '/install.php';
require __DIR__ . '/FilterableScripts.php';
require __DIR__ . '/FilterableStyles.php';
require __DIR__ . '/settings/index.php';
require __DIR__ . '/actions/index.php';
require __DIR__ . '/uninstall.php';
