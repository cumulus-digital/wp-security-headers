<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\General;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Actions\ActionsRegister;

require_once __DIR__ . '/hsts.php';
ActionsRegister::registerActor( 'general/hsts', new HSTS() );

require_once __DIR__ . '/upgrade-insecure.php';
ActionsRegister::registerActor( 'general/upgrade_insecure', new UpgradeInsecure() );

require_once __DIR__ . '/content.php';
ActionsRegister::registerActor( 'general/content', new Content() );

require_once __DIR__ . '/mask-headers.php';
ActionsRegister::registerActor( 'general/mask_headers', new MaskHeaders() );
