<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

require_once __DIR__ . '/ActionsRegister.php';
require_once __DIR__ . '/AbstractActor.php';

require_once __DIR__ . '/general/index.php';
require_once __DIR__ . '/csp/index.php';
