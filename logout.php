<?php
// logout.php

session_start();

// αδειάζουμε τις μεταβλητές του session
session_unset();

// καταστρέφουμε το session
session_destroy();

// επιστροφή στην αρχική σελίδα
header("Location: index.php");
exit();
?>