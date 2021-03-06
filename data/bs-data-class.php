<?php

class bsDataClass {

    var $table = 'bsImageGallery';
    var $selectField = [];

    public function bsGalleryTable() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        /* Newsletter List Table */
        $table_name = $wpdb->prefix . $this->table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                id int(9) NOT NULL AUTO_INCREMENT,
                gallery_name tinytext NOT NULL,
                gallery_data longtext NOT NULL,
                thumbnail varchar(255) NOT NULL,
                status tinyint DEFAULT 1 NOT NULL,
                created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                modified datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
        }
    }

    protected function bsSaveGallery() {
        if (wp_verify_nonce(handlePostData('glryNonce'), 'save_ivm_galry')) {
            $action = 'added';
            global $wpdb;
            $table_name = $wpdb->prefix . $this->table;
            $result = $this->bsCreateGalleryData();
            $result['created'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $result);
        } else if (wp_verify_nonce(handlePostData('glryNonce'), 'update_ivm_galry')) {
            $action = 'updated';
            global $wpdb;
            $table_name = $wpdb->prefix . $this->table;

            $id = handlePostData('gkey');
            $result = $this->bsCreateGalleryData();
            $result['modified'] = current_time('mysql');

            $result = $wpdb->update($table_name, $result, array('id' => $id));
        } else {
            return false;
        }

        if (is_wp_error($result)) {
            echo $result->get_error_message();
            return false;
        }
        return "Gallery $action successfully!";
    }

    private function bsCreateGalleryData() {
        $data = [];
        $data['gallery_name'] = handlePostData('galleryName');
        $data['thumbnail'] = handlePostData('thumbnail');

        $galleryData['row'] = handlePostData('row_count');
        $galleryData['column'] = handlePostData('col_count');
        $galleryData['img_id'] = handlePostData('image');
        $galleryData['img_title'] = handlePostData('title');
        $galleryData['img_desc'] = handlePostData('descText');
        $galleryData['img_status'] = handlePostData('active');

        $data['gallery_data'] = serialize($galleryData);
        return $data;
    }

    /*
     * public function > bsFetchData
     * id = integer
     * field = array
     * row = single/multi
     */

    public function bsFetchData($id = null, $field = array(), $multi = false) {
        global $wpdb;
        $tableName = $wpdb->prefix . $this->table;

        if (!empty($field)) {
            if (is_array($field)) {
                $field = '`' . (implode('`,`', $field)) . '`';
            }
        } else {
            $field = '*';
        }

        $sqlQry = 'SELECT ' . $field . ' FROM ' . $tableName . ' WHERE 1 ';

        if (!empty($id)) {
            if ($multi) {
                $sqlQry .= 'AND `id` IN(' . $id . ')';
            } else {
                $sqlQry .= 'AND `id`=' . $id;
                return $wpdb->get_row($sqlQry, ARRAY_A);
            }
        }
        return $wpdb->get_results($sqlQry, ARRAY_A);
    }

    protected function bsGalleryAutoCount() {
        global $wpdb;
        $tableName = $wpdb->prefix . $this->table;

        $sqlQry = 'SELECT `AUTO_INCREMENT` FROM '
                . 'INFORMATION_SCHEMA.TABLES WHERE '
                . 'TABLE_SCHEMA = "' . DB_NAME . '" '
                . 'AND TABLE_NAME = "' . $tableName . '"';
        return $wpdb->get_var($sqlQry);
    }

}
