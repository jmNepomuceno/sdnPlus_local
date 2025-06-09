<?php
    session_start();
    include("../database/connection2.php");

    $action = $_POST['action'];

    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $permissions = '{"setting": true, "history_log": true, "admin_function": true, "bucas_referral": false, "incoming_referral": true, "outgoing_referral": true, "patient_registration": true}';

    if($action === 'add'){
        $middleName = $_POST['middlename'];

        $sql = "INSERT INTO sdn_users (hospital_code, user_lastname, user_firstname, user_middlename, username, password, user_created, role, permission) VALUES (?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([1111, $lastName, $firstName, $middleName, $username, $password, date('Y-m-d H:i:s'), $role, $permissions]);
    }
    else if($action === 'edit'){
        $firstName_old = $_POST['firstname_old'];
        $lastName_old = $_POST['lastname_old'];
        $username_old = $_POST['username_old'];
        $password_old = $_POST['password_old'];
        $permission = json_encode(json_decode($_POST['permissions'], true)); // Decode and re-encode permissions

        $sql = "UPDATE sdn_users SET role=?, username=?, password=?, user_lastname=?, user_firstname=? , permission=? WHERE user_firstname=? AND user_lastname=? AND username=? AND password=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$role,$username,$password, $lastName, $firstName, $permission, $firstName_old, $lastName_old, $username_old, $password_old]);
    }
    else if($action === 'delete'){
        $sql = "DELETE FROM sdn_users WHERE user_firstname=? AND user_lastname=? AND username=? AND password=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$firstName, $lastName, $username, $password]);
    }


    $sql = "SELECT * FROM sdn_users WHERE role='doctor_admin'  ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $data_user_access = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode permissions once
    foreach ($data_user_access as &$user) {
        if (isset($user['permission']) && !empty($user['permission'])) {
            $user['permission'] = json_decode($user['permission'], true);
        }
    }

    $style = "";
    for ($i = 0; $i < count($data_user_access); $i++) {
        $style = ($i % 2 == 0) ? "background: #e2e7e9" : "background: none";
        
        echo '
        <tr style="' . $style . '">
            <td class="access-details" id="access-firstname-td"> 
                <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['user_firstname']) . '" />
            </td>
            <td class="access-details" id="access-lastname-td">
                <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['user_lastname']) . '" />
            </td>
            <td class="access-details" id="access-username-td">
                <input type="text" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['username']) . '" />
            </td>
            <td class="access-details password-mask" id="access-password-td">
                <input type="password" class="access-details-inputs form-control" value="' . htmlspecialchars($data_user_access[$i]['password']) . '" />
            </td>

            <td class="access-details access-role-td" id="access-role-div">
                <label>
                    <input type="checkbox" id="admin" name="role" value="admin" class="role-checkbox-add" ' . 
                    ($data_user_access[$i]['role'] === 'admin' ? 'checked' : '') . '>
                    Admin
                </label>
                <label>
                    <input type="checkbox" id="doctor-admin" name="role" value="doctor_admin" class="role-checkbox-add" ' . 
                    ($data_user_access[$i]['role'] === 'doctor_admin' ? 'checked' : '') . '>
                    Doctor Admin
                    
                </label>
            </td>

            <td class="access-details access-access-td">
                <div class="access-permision-div">
                    <div id="left-div">';
                    
                    // Use pre-decoded permissions
                    $permissions = $data_user_access[$i]['permission'];
                    $count = 0;
                    if ($permissions) {
                        foreach ($permissions as $key => $value) {
                            if ($count < 3) { // First 3 permissions for left div
                                $label = ucwords(str_replace('_', ' ', $key));
                                $checked = $value ? 'checked' : '';
                                echo '
                                <label>
                                    <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                    ' . $label . '
                                </label>';
                            }
                            $count++;
                        }
                    }

                    echo '
                    </div>

                    <div id="middle-div">';
                    
                    // Next 2 permissions for the middle div
                    $count = 0;
                    if ($permissions) {
                        foreach ($permissions as $key => $value) {
                            if ($count >= 3 && $count < 5) { 
                                $label = ucwords(str_replace('_', ' ', $key));
                                $checked = $value ? 'checked' : '';
                                echo '
                                <label>
                                    <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                    ' . $label . '
                                </label>';
                            }
                            $count++;
                        }
                    }

                    echo '
                    </div>

                    <div id="right-div">';
                    
                    // Remaining permissions for right div
                    $count = 0;
                    if ($permissions) {
                        foreach ($permissions as $key => $value) {
                            if ($count >= 5) {
                                $label = ucwords(str_replace('_', ' ', $key));
                                $checked = $value ? 'checked' : '';
                                echo '
                                <label>
                                    <input type="checkbox" id="' . $key . '-permission" name="permission" value="' . $key . '" class="permission-checkbox" ' . $checked . '>
                                    ' . $label . '
                                </label>';
                            }
                            $count++;
                        }
                    }

                    echo '
                    </div>
                    
                </div>
            </td>

            <td class="access-action-td">
                <button class="access-action-btn">EDIT</button>
                <i class="fa-solid fa-trash" id="delete-user-btn"></i>
            </td>
        </tr>';
    }

?>