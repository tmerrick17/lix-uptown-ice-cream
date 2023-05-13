<?php
/**
 * Reviews Sources Management
 *
 * @since 1.0
 */

namespace SmashBalloon\Reviews\Common\Builder;

use SmashBalloon\Reviews\Common\Customizer\DB;

class SBR_Sources{

    /**
     * Add a new source as a row in the sbi_sources table
     *
     * @param array $source_data
     *
     * @return false|int
     *
     * @since 1.0
     */
    public static function insert($source_data){
        $db = new DB();
        if (isset($source_data['id'])) {
            $source_data['account_id'] = $source_data['id'];
            unset($source_data['id']);
        }
        $data = $source_data;

        return $db->source_insert($data);
    }

    /**
     * Whether or not the source exists in the database
     *
     * @param array $args
     *
     * @return bool
     *
     * @since 6.0
     */
    public static function exists_in_database($args){
        $db = new DB();
        $results = $db->get_single_source($args);
        return isset($results[0]);
    }
    /**
	 * Used to update or insert connected accounts (sources)
	 *
	 * @param array $source_data
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public static function update_or_insert( $source_data ) {
		if ( ! isset( $source_data['id'] ) ) {
			return false;
		}

		if ( isset( $source_data ) ) {
			// data from an API request related to the source is saved as a JSON string
			if ( is_object( $source_data ) || is_array( $source_data ) ) {
				$source_data['info'] = sbr_json_encode( $source_data );
			}
		}

		if ( self::exists_in_database( $source_data ) ) {
			$source_data['last_updated'] = date( 'Y-m-d H:i:s' );
			self::update( $source_data, false );
		} else {
			self::insert( $source_data );
		}

		return true;
	}

    /**
	 * Update info in rows that match the source data
	 *
	 * @param array $source_data
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function update( $source_data) {
		$where = array(
            'id' => $source_data['id'],
            'provider' => $source_data['provider']
         );

		$data = $source_data;
        $db = new DB();
        return $db->source_update( $data, $where );
	}


    /**
	 * Update info in rows that match the source data
	 *
	 * @param array $source_data
	 *
	 * @return false|int
	 *
	 * @since 6.0
	 */
	public static function delete_source( $source_id) {
        $db = new DB();
        return $db->delete_source( $source_id );
	}

    /**
     * Get Sources
     *
     * @return array
     *
     * @since 1.0
     */
    public static function get_sources_list( $args = [] ){
        $db = new DB();
        return $db->source_query( $args );
    }

}