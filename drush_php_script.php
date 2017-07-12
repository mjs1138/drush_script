<?php
// Select local machine or
$local = $argv[1];
if((strcmp($local, "local") && (strcmp($local, "remote")))){
    echo("You must choose 'local' or 'remote'\n");
    exit;
}

// ///////////////// SET PARMS /////////////////////////////////////////////////
$target_path = null;
$domain = null;
$subdomain = null; // starts as null
$target_sites = 'drush_target_sites.php'; // some version of the 'sites.php' file
$root = null;

// /////////////////// SET UP & TEST ENVIRONMENT //////////////////////////////////////
if(!strcmp($local, "local")){
    $target_path = '/Applications/MAMP/drush_script/';
    $root = '/Applications/MAMP/htdocs_756_multisite'; // Location of Drupal index file
    $domain = 'local.vermont.gov'; // The domain
} else {
    $domain = 'vcms.vt.dev.cdc.nicusa.com'; // The domain
    $root = '/portal/vermont/solaris10x86/www/drupal-7.56'; // Location of Drupal index file
    $target_path = '/portal/vermont/solaris10x86/www/d7data/sites';
}

$filePathName = "$target_path/$target_sites";
if (file_exists($filePathName)) {
    echo "The file $filePathName exists\n";
    $file = fopen($filePathName,"r");
} else {
    echo "The target-sites file: $filePathName does not exist\n";
    exit;
}

// /////////////////////////////////////////////////////////////////////////////
while(! feof($file))
{
    $fileLine = trim(fgets($file)); // read line from file

    if($fileLine == ''){ //skip lines that are blank
        continue;
    }

    $siteLine = substr_compare($fileLine, '$sites[\'', 0, 8); // Test if line starts for beginning with $sites[

    if(!$siteLine) {
        $pieces = explode('=', $fileLine); // split the line @ '='
        $tgtChars = array("'", ";");

        $subdomain = trim(str_replace($tgtChars, "", $pieces[1])); // delete single quotes and semicolons
        echo $subdomain;
        echo "\r\n";

        $operation = " pm-enable features ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $operation = " pm-enable vic_filtered_html_table_text_format ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $operation = " role-add-perm 'Site Administrator' 'use text format filtered_html_tables' ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $operation = " role-add-perm 'Enterprise Administrator' 'use text format filtered_html_tables' ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $operation = " role-remove-perm 'Site Administrator' 'use text format full_html' ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $operation = " role-remove-perm 'Enterprise Administrator' 'use text format full_html' ";
        $command = "drush --root=$root --uri=$subdomain.$domain/ $operation -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

        $command = "drush --root=$root --uri=$subdomain.$domain/ cc all -y";
        echo "COMMAND: " . $command . "\r\n";
        $output = shell_exec($command);

    }
}

fclose($file);
?>


