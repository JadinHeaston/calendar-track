<?php
if (isset($_POST['calendar_edit_password']) === false)
	exit('No password given.');

require_once(__DIR__ . '/../includes/config.php');

echo password_hash($_POST['calendar_edit_password'], PASSWORD_ARGON2ID, CALENDAR_MANAGEMENT_PASSWORD_HASH_PARAMETERS);
