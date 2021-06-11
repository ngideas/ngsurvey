<?php
/**
 * Common utilities used across the plugin
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/init
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Defines utility methods common to all functions
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes
 * @author     NgIdeas <support@ngideas.com>
 */
class NgSurvey_Utils {

    /**
     * Generate a random character string
     *
     * @param int $length length of the string to be generated
     * @param string $chars characters to be considered, default alphanumeric characters.
     *
     * @return string randomly generated string
     */
    public static function get_survey_key ( $length = 16, $chars = 'abcdefghijklmnopqrstuvwxyz1234567890' ){
        // Length of character list
        $chars_length = ( strlen( $chars ) - 1 );
        
        // Start our string
        $string = $chars[ rand( 0, $chars_length ) ];
        
        // Generate random string
        for ( $i = 1; $i < $length; $i = strlen( $string ) ) {
            // Grab a random character from our list
            $r = $chars[ rand( 0, $chars_length ) ];
            
            // Make sure the same two characters don't appear next to each other
            if ( $r != $string[ $i - 1 ] ) $string .=  $r;
        }
        
        // Return the string
        return $string;
    }
    
    /**
     * Gets the ip address of the user from request
     *
     * @return string ip address
     */
    public static function get_user_ip_address() {
        $ip = '';
        
        if( !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && strlen( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) > 6 ) {
            
            $ip = strip_tags( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] );
        } elseif( !empty( $_SERVER['HTTP_CLIENT_IP'] ) && strlen( $_SERVER[ 'HTTP_CLIENT_IP' ] ) > 6 ) {
            
            $ip = strip_tags( $_SERVER[ 'HTTP_CLIENT_IP' ] );
        } elseif( !empty( $_SERVER[ 'REMOTE_ADDR' ] ) && strlen( $_SERVER[ 'REMOTE_ADDR' ] ) > 6 ) {
            
            $ip = strip_tags($_SERVER['REMOTE_ADDR']);
        }
        
        $ip = explode(',', $ip);
        $ip = $ip[0];
        
        return trim($ip);
    }
    
    /**
     * Gets the formatted number in the format 10, 100, 1000, 10k, 20.1k etc
     *
     * @param integer $num number to format
     * @return string formatted number
     */
    public static function format_number ( $num ) {
        $num = (int) $num;
        if ( $num < 1000 ) {
            return $num;
        } else if ( $num < 10000 ) {
            return round( $num/1000, 2 ).'k';
        } else if( $num < 1000000 ) {
            return round( $num/1000, 1 ).'k';
        } else {
            return round( $num / 1000000, 2 ).'m';
        }
    }
}
