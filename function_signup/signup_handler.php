<?php
//  validate dữ liệu
$username = "";
$password = "";
$cfpassword = "";
$captcha = "";

$errors = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $cfpassword = $_POST["cfpassword"];
    $captcha = $_POST["captcha"];
    if (empty($username)) {
        $errors['username']['required'] = "Username không được để trống !";
    } else {
        if (strlen($username) < 6) {
            $errors["username"]["min_length"] = "Username ít nhất 6 ký tự !";
        }
    }

    if (empty($password)) {
        $errors["password"]["required"] = "Password không được để trống !";
    } else {
        if (strlen($password) < 6) {
            $errors["password"]["min_length"] = "Password ít nhất 6 ký tự !";
        }
    }

    if (empty($cfpassword)) {
        $errors["cfpassword"]["required"] = "Bạn phải xác nhận mật khẩu !";
    } else {
        if ($cfpassword != $password) {
            $errors["cfpassword"]["matched"] = "Mật khẩu không trùng khớp !";
        }
    }

    if (empty($captcha)) {
        $errors["captcha"]["required"] = "Captcha không được để trống !";
    } else if ($captcha != $_SESSION["captchaSs"]) {
        $errors["captcha"]["matched"] = "Captcha không đúng !";
    }

}

// insert du lieu
if (empty($errors) && !empty($username)) {
    $servername = "localhost";
    $usernameDB = "root";
    $passwordDB = "";
    $dbname = "project_php";

    require_once "../connectDB.php";

    // Kiểm tra xem người dùng đã tồn tại chưa
    $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors["username"]["duplicate"] = "Người dùng đã tồn tại";
    } else {
        // Chèn người dùng mới vào cơ sở dữ liệu
        $sql_insert = "INSERT INTO user (username, password) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_insert->bind_param("ss", $username, $password);
        $stmt_insert->execute();
        $stmt_insert->close();

        //  set username vao session 
        $_SESSION["username"] = $username;
        if (empty($_SESSION["username"])) {
            // neu mat session dieu huong ve trang login
            //     header("Location: ../function_login/login_view.php");
        }

        // dieu huong den trang todo
        header("Location: todo_view.php");
    }

    $stmt->close();
    $conn->close();
}
?>