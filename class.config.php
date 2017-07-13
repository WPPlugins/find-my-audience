<?php
namespace FindMyAudience;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FMA_Config {

	protected $configFilePath;

        public function __construct($configFile="core/config.php") {
		$this->configFilePath = \FindMyAudience::$AppDir.$configFile;
                $this->readConfig();
        }

        private function readConfig() {

		@include($this->configFilePath);

                if(isset($CONFIG) && is_array($CONFIG)) {
                        $this->Config = $CONFIG;
			return $this->Config;
                }

		return false;
        }

        public function getValue($key, $default=null) {
                if (isset($this->Config[$key])) {
                        if( (empty($this->Config[$key])) AND ($this->Config[$key] != '0') ) {
                                //echo "$key is empty<br>";
                                return $default;
                        }
                        return $this->Config[$key];
                }
                return $default;
        }

        public function getKeys() {
                return $this->Config;
        }

}

