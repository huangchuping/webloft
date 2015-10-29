<?php
/**
 * 错误处理类，自己处理所有的PHP错误
 */
error_reporting ( 0 );

function errorAlert() {
    if (is_null ( $e = error_get_last () ) === false) {
        errorHandler($e['type'], $e['message'], $e['file'], $e['line']);
    }
}
register_shutdown_function ( 'errorAlert' );

function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = '') {
    $errors = "Unknown";
    switch ($errno) {
        case E_NOTICE :
        case E_USER_NOTICE :
            $errors = "Notice";
            break;
        case E_WARNING :
        case E_USER_WARNING :
            $errors = "Warning";
            break;
        case E_ERROR :
        case E_USER_ERROR :
            $errors = "Fatal Error";
            break;
        default :
            return;
    }

    // 输出JSON格式错误
    $result = array(
        'code' => 1,
        'message' => sprintf( "%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline )
    );

    Helper::setLogs('error|'.var_export($result,true),'errorlog_');

    header("Content-Type:	application/json");
    echo json_encode($result);
//	if (ini_get ( "display_errors" ))
//		printf ( "<br />\n<b>%s(%d)</b>: %s in <b>%s</b> on line <b>%d</b><br /><br />\n", $errors, $errno, $errstr, $errfile, $errline );
    if (ini_get ( 'log_errors' ))
        error_log ( sprintf ( "PHP %s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline ) );
    return true;
}
set_error_handler ( 'errorHandler' );

?>