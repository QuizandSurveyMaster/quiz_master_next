<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class will manage all data related to QSM Themes
 * 
 * @since 7.1.15
 */
class QSM_Theme_Settings{

    /**
     * ID of the quiz
     */
    private $quiz_id;

    private $themes_table;

    private $settings_table;

    public function __construct(){
        $this->themes_table = 'mlw_themes';
        $this->settings_table = 'mlw_quiz_theme_settings';
    }

    /**
     * updates theme status active/inactive when theme plugin is activated/deactivated
     * 
     * 
     */
    public function update_theme_status($status = TRUE, $path, $name = '', $default_settings = ''){
        global $wpdb;
        $theme_path = isset($path)? $path:'';
        $theme_name = isset($name)? $name:'';
        // if(isset($default_settings) && is_array($default_settings) && sizeOf($default_settings) > 0){
        //     $default_settings = array_map('esc_attr', $default_settings);
            $theme_default_settings = $default_settings;        
        // }

        if($theme_path){
            $theme_id = $this->get_theme_id($theme_path);
            if($theme_id){
                if($status){
                    return $result = $this->update_theme($theme_id, $status, $theme_name, $theme_default_settings);
                } else {
                    return $reuslt = $this->update_theme($theme_id, $status);
                }
            } else {
                if($status){
                    return $result = $this->add_theme_active($theme_path , $theme_name, $theme_default_settings);
                }
            }
        }

    }

    /**
     * 
     * Get theme id from theme_dir_basename
     * 
     * @param string theme_dir_path
     * @return int/null
     */
    private function get_theme_id($theme_dir){
        global $wpdb;
        $theme_dir = isset($theme_dir)?esc_attr( $theme_dir ):'';
        $theme_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}$this->themes_table WHERE theme = %s", $theme_dir
            )
        );
        return $theme_id;
    }

    /**
     * updates and activate existing theme
     * 
     * 
     */
    public function update_theme($theme_id, $status, $theme_name = '', $default_settings = ''){
        global $wpdb;
        $theme_id = (int) $theme_id;

        $data = [
            'theme_active'      => $status
        ];
        $identifier = ['%d'];
        if($theme_name){
            $data['theme_name'] = $theme_name;
            $identifier[] = '%s';
        }
        if($default_settings){
            $data['default_settings'] = $default_settings;
            $identifier[] = '%s';
        }
        return $result = $wpdb->update(
            $wpdb->prefix.$this->themes_table ,
            $data,
            array('id' => $theme_id),
            $identifier,
            array('%d')
        );
    }

    /**
     * 
     * Adds Record of new QSM themes
     * 
     * 
     */
    public function add_theme_active($theme_path , $theme_name, $theme_default_settings){
        global $wpdb;

        $data = [
            'theme_active'      => true,
            'theme'             => $theme_path,
            'theme_name'        => $theme_name,
            'default_settings'  => $theme_default_settings,
        ];

        return $wpdb->insert(
            $wpdb->prefix.$this->themes_table,
            $data,
            array('%d', '%s', '%s', '%s')
        );

    }

    /**
     * 
     * Get active themes from database
     */
    public function get_active_themes($parameter = 'theme'){
        global $wpdb;
        if(is_array($parameter)){
            $parameter = sanitize_text_field(implode(',', $parameter));
        } else if(is_string($parameter)){
            $parameter = sanitize_text_field($parameter);
        } else {
            $parameter = 'theme';
        }

        $query = $wpdb->prepare("SELECT id,%1s FROM {$wpdb->prefix}$this->themes_table WHERE theme_active = 1", $parameter);
        return $results = $wpdb->get_results($query, ARRAY_A);

    }

}