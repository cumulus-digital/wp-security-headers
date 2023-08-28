<?php

namespace CUMULUS\Wordpress\SecurityHeaders\Actions\CSP;

\defined( 'ABSPATH' ) || exit( 'No direct access allowed.' );

use CUMULUS\Wordpress\SecurityHeaders\Actions\ActionsRegister;

require_once __DIR__ . '/mode.php';
ActionsRegister::registerActor( 'csp/mode', new Mode() );

require_once __DIR__ . '/directives.php';
ActionsRegister::registerActor( 'csp/directives', new Directives() );

require_once __DIR__ . '/auto-nonce.php';
ActionsRegister::registerActor( 'csp/auto_nonce', new AutoNonce() );

require_once __DIR__ . '/reporting.php';
ActionsRegister::registerActor( 'csp/reporting', new Reporting() );
