<?php
/**
 * This script converts the user table from the info-arena 1 database to
 * the new info-arena 2 database.
 * This script should be run when there will be no more database changes.
 *
 * If someone changes the databse, they should review this script to
 * ensure it's still working. If they can't review it at least make a ticket!
 *
 * It is recommended to empty the ia_user table before running this script
**/

    // include infoa-arena 2 defines and connect to database (dbLink)
    require_once("../config.php");
    require_once("../common/db.php");

    // defines for info-arena 1 database
    define("DB1_HOST", 'localhost');    // change me!
    define("DB1_USER", 'root');         // change me!
    define("DB1_PASS", '');             // change me!
    define("DB1_NAME", 'infoarena1_dev');

    // if we make more utils, this should be in a utils-common config file
    define("IA_URL_UTILS_PREFIX", '/infoarena2/utilities/');

    $verbose = isset($_POST['verbose']);

    if (!isset($_POST['ok'])) {
        // first display a form to confirm database copy
        
        echo '<form action='. IA_URL_UTILS_PREFIX . 'db_conv.php' . ' method="post">'."\n";
        echo '<p>This utility will adapt the info-arena 1 user table to info-arena 2</p>'."\n";
        echo '<p>Are you sure you want to do this?</p>'."\n";
        echo '<input type="submit" value="DO IT" name="ok"/><br>'."\n";
        echo 'Verbose: <input type="checkbox" checked="checked" name="verbose"/>'."\n";
        echo '</form>'."\n";
    }
    else {
        // now the party starts!

        // connect to info-arena 1 db
        assert(!isset($dbLink1));    // repetitive-include guard
        $dbLink1 = mysql_connect(DB1_HOST, DB1_USER, DB1_PASS, TRUE) // new link
            or die('Cannot connect to database from info-arena1.');
        mysql_select_db(DB1_NAME, $dbLink1) or die ('Cannot select database.');

        // dbLink1  -> info-arena 1
        // dbLink   -> info-arena 2

        $ok = true;
        $count = 0;
        
        $select_query = "SELECT * FROM devnet_users";
        
        // use db_query instead of db_fetch_all to speed things up a bit
        $result = mysql_query($select_query, $dbLink1);
        if ($result) {
            while ($row = mysql_fetch_assoc($result)) {
                // fill the user data
                $data = array();
                $data['username'] = $row['id'];
                $data['password'] = $row['password'];
                $data['email'] = $row['email'];
                $data['full_name'] = $row['name'];
                $data['newsletter'] = $row['receiveNewsletter'];
                if ($row['admin']) {
                    $data['security_level'] = "admin";
                }
                else {
                    $data['security_level'] = "normal";
                }
                
                // run query
                $res = user_create($data);

                if (!$res) {
                    $ok = false;
                }
                else {
                    ++$count;
                }

                if ($verbose) {
                    echo "<pre>";
                    print_r($data);
                    echo "</pre>\n";
                    echo "<strong>" . (($res)?"OK":"FAILED") . "</strong>";
                    echo "<br><br>\n";
                }
                else {
                    if (!$res) {
                        echo "Problems at user " . $row['name'] . "<br>\n";
                    }
                }

                unset($data); // make sure nothing remains to the next user
            }
        }
        else {
            echo "Could not get the users from info-arena 1 database";
            $ok = false;
        }

        if ($ok) {
            echo "Everything went ok! Added $count entries to the database";
        }
        else {
            echo "Some problems encountered.
                  Added $count entries to the database";
        }

        // cleanup
        mysql_close($dbLink1);
    }

?>