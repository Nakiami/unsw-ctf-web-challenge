<?php

define('IN_FILE', true);
require('../include/general.inc.php');

enforceAuthentication();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_POST['action'] == 'edit') {

        // no need to escape "sex" since the only two possible values are defined in the form
        $db->query('UPDATE users SET sex = "'.$_POST['sex'].'", description = "'.$db->quote($_POST['description']).'", website = "'.$db->quote($_POST['website']).'" WHERE id = '.$db->quote($_SESSION['id'])) or die(sqlError(__FILE__, __LINE__));

        header('location: control.php?generic_success=1');
        exit();
    }

    else if ($_POST['action'] == 'reset_password') {
        $new_password = generateRandomString(8, false);
        $new_salt = makeSalt();

        $new_passhash = makePassHash($new_password, $new_salt);

        $stmt = $db->prepare('
        UPDATE users SET
        salt=:salt,
        passhash=:passhash
        WHERE id=:id
        ');
        $stmt->execute(array(':passhash'=>$new_passhash, ':salt'=>$new_salt, ':id'=>$_SESSION['id']));

        genericMessage('Success', 'Users new password is: ' . $new_password);
    }
}

$stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute(array(':id' => $_SESSION['id']));
$user = $stmt->fetch(PDO::FETCH_ASSOC);

head('Control panel');

sectionSubHead('User control panel');
echo '
<form class="form-horizontal" method="post">

    <div class="control-group">
        <label class="control-label" for="class">Class</label>
        <div class="controls">
            ',getClassName($user['class']),'
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="sex">Sex</label>
        <div class="controls">
            <input type="radio" name="sex" value="Female"',($user['sex'] == 'female' ? ' checked="checked"' : ''),' /> Male <br />
            <input type="radio" name="sex" value="Male"',($user['sex'] == 'male' ? ' checked="checked"' : ''),' /> Female <br />
            <input type="radio" name="sex" value="Other"',($user['sex'] == 'other' ? ' checked="checked"' : ''),' /> Other
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="description">Description</label>
        <div class="controls">
            <textarea name="description" id="description">',htmlspecialchars($user['description']),'</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="website">Website</label>
        <div class="controls">
            <input type="text" id="website" name="website" class="input-block-level" placeholder="Website" value="',htmlspecialchars($user['website']),'">
        </div>
    </div>

    <input type="hidden" name="action" value="edit" />

    <div class="control-group">
        <label class="control-label" for="save"></label>
        <div class="controls">
            <button type="submit" id="save" class="btn btn-primary">Save changes</button>
        </div>
    </div>

</form>';
sectionSubHead('Reset password');
echo '
<form class="form-horizontal"  method="post">
    <div class="control-group">
        <label class="control-label" for="reset_confirmation">Reset users password</label>

        <div class="controls">
            <input type="checkbox" id="reset_confirmation" name="reset_confirmation" value="1" />
        </div>
    </div>

    <input type="hidden" name="action" value="reset_password" />
    <input type="hidden" name="id" value="',htmlspecialchars($_SESSION['id']),'" />

    <div class="control-group">
        <label class="control-label" for="reset_password"></label>
        <div class="controls">
            <button type="submit" id="reset_password" class="btn btn-danger">Reset password</button>
        </div>
    </div>
</form>
';

foot();