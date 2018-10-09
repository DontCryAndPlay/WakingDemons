<?php
Session::$instance->deleteUserSession();
header("Location: /");
?>