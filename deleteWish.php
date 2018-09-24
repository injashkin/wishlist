<?php

/*
 * 
 * 
 * and open the template in the editor.
 */
require_once("Includes/db.php");
WishDB::getInstance()->delete_wish($_POST["wishID"]);
header('Location: editWishList.php');
