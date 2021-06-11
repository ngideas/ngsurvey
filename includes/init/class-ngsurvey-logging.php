<?php
/**
 * Register logging class
 *
 * @link       https://ngideas.com
 * @since      1.0.0
 *
 * @package    NgSurvey
 * @subpackage NgSurvey/includes/init
 */
if (! defined('ABSPATH')) {
    exit();
}

/**
 * Register logger for using across the plugin.
 *
 * The logger class is responsible for logging information for debugging purpose.
 * It follows the singleton pattern to define the single instance across the request.
 *
 * @package NgSurvey
 * @subpackage NgSurvey/includes
 * @author NgIdeas <support@ngideas.com>
 */
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class NGLOG
{

    protected static $instance;
    
    private function __construct() {
        // create object not allowed
    }

    /**
     * Method to return the Monolog instance
     *
     * @return \Monolog\Logger
     */
    static public function getLogger()
    {
        if ( ! self::$instance ) {
            self::configureInstance();
        }

        return self::$instance;
    }

    /**
     * Configure Monolog to use a rotating files system.
     *
     * @return Logger
     */
    protected static function configureInstance()
    {
        $uploads_dir = wp_upload_dir();
        $dir = trailingslashit( $uploads_dir[ 'basedir' ] ) . 'ngsurvey/logs';
        
        if ( ! file_exists( $dir ) ) {
            mkdir($dir, FS_CHMOD_FILE, true);
        }
        
        $formatter = new LineFormatter( LineFormatter::SIMPLE_FORMAT, LineFormatter::SIMPLE_DATE );
        $formatter->includeStacktraces( true );
        
        $stream = new RotatingFileHandler( trailingslashit( $dir ) . 'messages.log', 10 );
        $stream->setFormatter( $formatter );

        $logger = new Logger( 'NgSurvey' );
        $logger->pushHandler( $stream );

        self::$instance = $logger;
    }

    public static function debug( $message, array $context = [] )
    {
        self::getLogger()->debug( $message, $context );
    }

    public static function info( $message, array $context = [] )
    {
        self::getLogger()->info( $message, $context );
    }

    public static function notice( $message, array $context = [] )
    {
        self::getLogger()->notice( $message, $context );
    }

    public static function warning( $message, array $context = [] )
    {
        self::getLogger()->warning( $message, $context );
    }

    public static function error( $message, array $context = [] )
    {
        self::getLogger()->error( $message, $context );
    }

    public static function critical( $message, array $context = [] )
    {
        self::getLogger()->critical( $message, $context );
    }

    public static function alert( $message, array $context = [] )
    {
        self::getLogger()->alert( $message, $context );
    }

    public static function emergency( $message, array $context = [] )
    {
        self::getLogger()->emergency( $message, $context );
    }
}