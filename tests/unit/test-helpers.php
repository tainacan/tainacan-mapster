<?php
/**
 * Unit tests for helper functions.
 *
 * @package Tainacan_Mapster
 */

/**
 * @coversNothing
 */
class Helpers_Test extends PHPUnit\Framework\TestCase {

	protected function setUp(): void {
		parent::setUp();
		tainacan_mapster_test_reset_posts();
	}

	public function test_feature_post_types_list() {
		$types = tainacan_mapster_get_feature_post_types();

		$this->assertSame(
			[
				'mapster-wp-location',
				'mapster-wp-line',
				'mapster-wp-polygon',
			],
			$types
		);
	}

	/**
	 * @dataProvider geometry_field_provider
	 *
	 * @param string $post_type Post type.
	 * @param string $expected  Expected ACF field.
	 */
	public function test_element_geometry_field( $post_type, $expected ) {
		$this->assertSame( $expected, tainacan_mapster_get_element_geometry_field( $post_type ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string}>
	 */
	public function geometry_field_provider() {
		return [
			'location' => [ 'mapster-wp-location', 'location' ],
			'line'     => [ 'mapster-wp-line', 'line' ],
			'polygon'  => [ 'mapster-wp-polygon', 'polygon' ],
			'unknown'  => [ 'post', '' ],
			'empty'    => [ '', '' ],
		];
	}

	/**
	 * @dataProvider css_dimension_provider
	 *
	 * @param mixed  $value    Value.
	 * @param string $unit     Unit.
	 * @param string $default  Default.
	 * @param string $expected Expected CSS length.
	 */
	public function test_sanitize_css_dimension( $value, $unit, $default, $expected ) {
		$this->assertSame(
			$expected,
			tainacan_mapster_sanitize_css_dimension( $value, $unit, $default )
		);
	}

	/**
	 * @return array<string, array{0: mixed, 1: string, 2: string, 3: string}>
	 */
	public function css_dimension_provider() {
		return [
			'integer px'     => [ 320, 'px', '100px', '320px' ],
			'float percent'  => [ 50.5, '%', '100px', '50.5%' ],
			'invalid unit'   => [ 10, 'pt', '100px', '10px' ],
			'non numeric'    => [ 'abc', 'px', '100px', '100px' ],
			'negative'       => [ -5, 'px', '100px', '100px' ],
			'vh'             => [ 80, 'VH', '100px', '80vh' ],
		];
	}

	public function test_user_can_view_published_post() {
		$GLOBALS['tainacan_mapster_test_posts'][5] = [
			'type'   => 'mapster-wp-location',
			'status' => 'publish',
		];

		$this->assertTrue( tainacan_mapster_user_can_view_post( 5 ) );
		$this->assertFalse( tainacan_mapster_user_can_view_post( 0 ) );
	}

	public function test_user_can_view_draft_requires_capability() {
		$GLOBALS['tainacan_mapster_test_posts'][6] = [
			'type'   => 'mapster-wp-location',
			'status' => 'draft',
		];

		$GLOBALS['tainacan_mapster_test_can_read'] = false;
		$this->assertFalse( tainacan_mapster_user_can_view_post( 6 ) );

		$GLOBALS['tainacan_mapster_test_can_read'] = true;
		$this->assertTrue( tainacan_mapster_user_can_view_post( 6 ) );
	}

	public function test_sanitize_feature_ids_filters_type_and_visibility() {
		$GLOBALS['tainacan_mapster_test_posts'] = [
			10 => [ 'type' => 'mapster-wp-location', 'status' => 'publish' ],
			11 => [ 'type' => 'post', 'status' => 'publish' ],
			12 => [ 'type' => 'mapster-wp-line', 'status' => 'draft' ],
			13 => [ 'type' => 'mapster-wp-polygon', 'status' => 'publish' ],
		];

		$GLOBALS['tainacan_mapster_test_can_read'] = false;

		$result = tainacan_mapster_sanitize_feature_ids( [ '10', 11, 12, 13, 0, 'x', 10 ] );

		$this->assertSame( [ 10, 13 ], $result );
	}

	public function test_sanitize_feature_ids_respects_allow_list() {
		$GLOBALS['tainacan_mapster_test_posts'] = [
			10 => [ 'type' => 'mapster-wp-location', 'status' => 'publish' ],
			13 => [ 'type' => 'mapster-wp-polygon', 'status' => 'publish' ],
		];

		$result = tainacan_mapster_sanitize_feature_ids(
			[ 10, 13 ],
			[ 'mapster-wp-location' ]
		);

		$this->assertSame( [ 10 ], $result );
	}
}
