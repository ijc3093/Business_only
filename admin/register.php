<?php
    include('./controller.php');

    // ✅ FIX: correct path from root register.php to admin session helper
    include('../admin/includes/session_helper.php');

    $userModel = new Controller();

    class Users {
        private $userModel;

        public function __construct() {
            $this->userModel = new Controller();
        }

        public function register() {
            // Sanitize input
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $username    = trim($_POST['username'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $password    = trim($_POST['password'] ?? '');
            $gender      = trim($_POST['gender'] ?? '');
            $mobile      = trim($_POST['mobile'] ?? '');
            $designation = trim($_POST['designation'] ?? '');
            $roleName    = trim($_POST['role'] ?? '');

            // Basic validation
            if ($username === '' || $email === '' || $password === '' || $gender === '' || $mobile === '' || $designation === '' || $roleName === '') {
                echo "<script>alert('Please fill all required fields.');</script>";
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Invalid email address.');</script>";
                return;
            }

            // ✅ ADD: prevent duplicate email/username (uses method added to controller.php)
            if ($this->userModel->findAdminByEmailOrUsername($email, $username)) {
                echo "<script>alert('Email or Username already exists.');</script>";
                return;
            }

            // Role mapping (matches your radio values)
            $roleMap = [
                'Admin'   => 1,
                'Manager' => 2,
                'Staff'   => 4,
            ];
            $role = $roleMap[$roleName] ?? 4;

            // Image upload (safe default)
            $image = 'default.jpg';

            if (!empty($_FILES['image']['name']) && isset($_FILES['image']['tmp_name'])) {
                $allowedExt = ['jpg', 'jpeg', 'png'];
                $original = $_FILES['image']['name'];
                $tmp = $_FILES['image']['tmp_name'];

                $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    echo "<script>alert('Image must be JPG, JPEG, or PNG');</script>";
                    return;
                }

                // Safer filename
                $base = preg_replace('/[^a-zA-Z0-9-_]/', '-', pathinfo($original, PATHINFO_FILENAME));
                $finalName = strtolower($base . '-' . time() . '.' . $ext);

                // IMPORTANT: make sure this folder exists and matches your real folder name: images/ vs Images/
                $folder = __DIR__ . '/images/';
                if (!is_dir($folder)) {
                    mkdir($folder, 0755, true);
                }

                if (move_uploaded_file($tmp, $folder . $finalName)) {
                    $image = $finalName;
                } else {
                    echo "<script>alert('Image upload failed.');</script>";
                    return;
                }
            }

            $data = [
                'username'    => $username,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT), // secure hashing
                'gender'      => $gender,
                'mobile'      => $mobile,
                'designation' => $designation,
                'role'        => $role,
                'image'       => $image,
                'status'      => 1
            ];

            if ($this->userModel->register($data)) {
                $_SESSION['username'] = $username;
                $_SESSION['image'] = $image;

                echo "<script>alert('Registration Successful!');</script>";
                echo "<script>window.location.href='index.php';</script>";
                exit;
            }

            echo "<script>alert('Registration failed. Please try again.');</script>";
        }
    }

    $init = new Users();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['type'] ?? '') === 'register') {
        $init->register();
    }
?>

<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">

    <script type="text/javascript">
        // ✅ MINIMAL FIX: allow submit if no image selected (since default.jpg is allowed)
        function validate(){
            var image_file = document.regform.image.value;
            if (!image_file) return true;

            var extensions = new Array("jpg","jpeg","png");
            var pos = image_file.lastIndexOf('.') + 1;
            var ext = image_file.substring(pos).toLowerCase();

            for (var i = 0; i < extensions.length; i++){
                if(extensions[i] == ext){
                    return true;
                }
            }
            alert("Image Extension Not Valid (Use Jpg, jpeg, png)");
            return false;
        }
    </script>
</head>

<body>
    <div class="login-page bk-img">
        <div class="form-content">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1 class="text-center text-bold mt-2x">Register</h1>
                        <div class="hr-dashed"></div>
                        <div class="well row pt-2x pb-3x bk-light text-center">

                        <?php
                        echo '
                         <form method="post" class="form-horizontal" enctype="multipart/form-data" id="createAccountFor" name="regform" onSubmit="return validate();">
                            <div class="form-group">
                            <label class="col-sm-1 control-label">Name<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            <input type="hidden" name="type" value="register">
                            <input type="text" name="username" class="form-control" required>
                            </div>

                            <label class="col-sm-1 control-label">Email<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            <input type="text" name="email" class="form-control" required>
                            </div>
                            </div>

                            <div class="form-group">
                            <label class="col-sm-1 control-label">Password<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            <input type="password" name="password" class="form-control" id="password" required >
                            </div>

                            <label class="col-sm-1 control-label">Designation<span style="color:red">*</span></label>
                            <div class="col-sm-5">
                            <input type="text" name="designation" class="form-control" required>
                            </div>
                            </div>

                             <div class="form-group">
                                <label class="col-sm-1 control-label">Gender<span style="color:red">*</span></label>
                                        <div class="col-sm-5">
                                            <select name="gender" class="form-control" required>
                                                <option value="">Select</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </div>

                                    <label class="col-sm-1 control-label">Phone<span style="color:red">*</span></label>
                                    <div class="col-sm-5">
                                        <input type="number" name="mobile" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-1 control-label">Avtar<span style="color:red">*</span></label>
                                    <div class="col-sm-5">
                                    <div><input type="file" name="image" class="form-control"></div>
                                    </div>

                                    <label class="col-sm-1 control-label">Role<span style="color:red">*</span></label>
                                    <div>
                                        <div class="col-sm-5">
                                            <input type="radio" name="role" id="exampleRadios1" value="Admin">
                                            <label class="form-check-label" for="exampleRadios1">Admin</label>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="col-sm-5">
                                            <input type="radio" name="role" id="exampleRadios2" value="Manager">
                                            <label class="form-check-label" for="exampleRadios2">Manager</label>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="col-sm-5">
                                            <input type="radio" name="role" id="exampleRadios3" value="Staff">
                                            <label class="form-check-label" for="exampleRadios3">Staff</label>
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <button class="btn btn-primary" name="submit" type="submit">Register</button>
                                </form>
                                <br><br>
                                <p>Already Have Account? <a href="index.php" >Signin</a></p>
                            </div>
                        </div>';
                        ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>

</body>
</html>
