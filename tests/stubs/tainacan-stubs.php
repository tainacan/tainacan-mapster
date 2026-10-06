<?php
/**
 * Minimal Tainacan Exposer base stub for unit tests.
 *
 * @package Tainacan_Mapster
 */

namespace Tainacan\Exposers {

	if ( ! class_exists( __NAMESPACE__ . '\\Exposer', false ) ) {
		/**
		 * Stub of \Tainacan\Exposers\Exposer.
		 */
		abstract class Exposer {
			/**
			 * @var string
			 */
			public $slug = '';

			/**
			 * @var bool
			 */
			public $accept_no_mapper = true;

			/**
			 * @var string
			 */
			private $name = '';

			/**
			 * @var string
			 */
			private $description = '';

			/**
			 * @param string $name Name.
			 */
			protected function set_name( $name ) {
				$this->name = $name;
			}

			/**
			 * @param string $description Description.
			 */
			protected function set_description( $description ) {
				$this->description = $description;
			}

			/**
			 * @return string
			 */
			public function get_name() {
				return $this->name;
			}

			/**
			 * @return string
			 */
			public function get_description() {
				return $this->description;
			}

			/**
			 * @param mixed $response Response.
			 * @param mixed $handler  Handler.
			 * @param mixed $request  Request.
			 * @return mixed
			 */
			abstract public function rest_request_after_callbacks( $response, $handler, $request );
		}
	}
}

namespace {

	if ( ! class_exists( 'WP_REST_Request', false ) ) {
		/**
		 * Minimal WP_REST_Request for exposer option parsing tests.
		 */
		class WP_REST_Request {
			/**
			 * @var array<string, mixed>
			 */
			private $params = [];

			/**
			 * @param array<string, mixed> $params Params.
			 */
			public function __construct( array $params = [] ) {
				$this->params = $params;
			}

			/**
			 * @param string $key Param key.
			 * @return mixed|null
			 */
			public function get_param( $key ) {
				return array_key_exists( $key, $this->params ) ? $this->params[ $key ] : null;
			}
		}
	}
}
