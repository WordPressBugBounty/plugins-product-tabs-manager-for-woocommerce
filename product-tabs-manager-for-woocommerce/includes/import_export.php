<?php
/** Import/export of complete Location tab maps, using framework post references. */
class BeRocket_tab_manager_import_export {
    const FORMAT_KEY = '__br_tabs_map_v1';

    public static function export_map($value, $context) {
        $converted = BeRocket_import_export::type_check_export(
            array('map' => $value),
            array('map' => self::reference_type($context)),
            $context['settings']
        );
        // The envelope distinguishes a complete (possibly empty) map from legacy deltas.
        return array(self::FORMAT_KEY => $converted['map']);
    }

    public static function import_map($value, $context) {
        if( ! is_array($value) ) {
            return array();
        }
        $complete = array_key_exists(self::FORMAT_KEY, $value);
        $map = $complete ? $value[self::FORMAT_KEY] : $value;
        if( ! is_array($map) ) {
            return array();
        }
        $type = self::reference_type($context);
        $entries_key = BeRocket_import_export::MAP_ENTRIES_KEY;
        $legacy_defaults = array();
        if( array_key_exists($entries_key, $map) ) {
            $entries = is_array($map[$entries_key]) ? $map[$entries_key] : array();
            if( ! $complete ) {
                // Old exports omitted unchanged fields; the framework restores these defaults.
                $legacy_defaults = $map;
                unset($legacy_defaults[$entries_key]);
            }
        } else {
            $entries = array();
            foreach($map as $key => $item) {
                // Keep stable tab keys scalar so the framework's passthrough check recognizes them.
                $reference = in_array($key, $type['passthrough'], true)
                    ? $key
                    : array('source_id' => (string) $key, 'pt' => $type['post_type']);
                $entries[] = array('key' => $reference, 'value' => $item);
            }
        }
        // The standard array-key converter treats an empty entry list as a legacy raw map.
        if( empty($entries) ) {
            return $legacy_defaults;
        }
        $converted = BeRocket_import_export::type_check_import(
            array('map' => array($entries_key => $entries)),
            array('map' => $type),
            $context['settings']
        );
        return array_replace($legacy_defaults, $converted['map']);
    }

    protected static function reference_type($context) {
        $type = $context['declaration'];
        $type['export_type'] = 'post';
        return $type;
    }
}

add_filter('brfr_export_type_tab_manager_map', array('BeRocket_tab_manager_import_export', 'export_map'), 10, 2);
add_filter('brfr_import_type_tab_manager_map', array('BeRocket_tab_manager_import_export', 'import_map'), 10, 2);
