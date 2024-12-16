<?php
session_start();
ini_set('display_errors', 1);

class Action {
    private $db;

    public function __construct() {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct() {
        $this->db->close();
        ob_end_flush();
    }

    // Improved login with prepared statement to avoid SQL injection
    function login() {
        extract($_POST);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password); // Bind parameters
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            foreach ($user as $key => $value) {
                if ($key != 'password' && !is_numeric($key))
                    $_SESSION['login_' . $key] = $value;
            }
            return 1;
        } else {
            return 3;
        }
    }

    // Improved login2 with prepared statement
    function login2() {
        extract($_POST);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $hashed_password = md5($password);
        $stmt->bind_param("ss", $email, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            foreach ($user as $key => $value) {
                if ($key != 'password' && !is_numeric($key))
                    $_SESSION['login_' . $key] = $value;
            }
            return 1;
        } else {
            return 3;
        }
    }

    // Improved logout method
    function logout() {
        session_destroy();
        $_SESSION = [];
        header("location: login.php");
    }

    // Improved logout2 method
    function logout2() {
        session_destroy();
        $_SESSION = [];
        header("location: ../index.php");
    }

    // Save or update user with prepared statements
    function save_user() {
        extract($_POST);
        $stmt = $this->db->prepare("INSERT INTO users (name, username, password, type) VALUES (?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE name = ?, username = ?, password = ?, type = ?");
        $stmt->bind_param("ssssssss", $name, $username, $password, $type, $name, $username, $password, $type);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Improved user signup
    function signup() {
        extract($_POST);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return 2; // Email already exists
        }

        $hashed_password = md5($password);
        $stmt = $this->db->prepare("INSERT INTO users (name, contact, address, username, password, type) 
                                   VALUES (?, ?, ?, ?, ?, 3)");
        $stmt->bind_param("sssss", $name, $contact, $address, $email, $hashed_password);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['login_' . $stmt->insert_id] = $stmt->insert_id;
            return 1;
        }
        return 0;
    }

    // Save system settings (with proper sanitization)
    function save_settings() {
        extract($_POST);
        $data = "name = '" . str_replace("'", "&#x2019;", $name) . "'";
        $data .= ", email = '$email'";
        $data .= ", contact = '$contact'";
        $data .= ", about_content = '" . htmlentities(str_replace("'", "&#x2019;", $about)) . "'";

        if ($_FILES['img']['tmp_name'] != '') {
            $fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
            if ($move) {
                $data .= ", cover_img = '$fname'";
            }
        }

        // Check if settings exist and insert/update accordingly
        $stmt = $this->db->prepare("SELECT * FROM system_settings");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt = $this->db->prepare("UPDATE system_settings SET $data");
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("INSERT INTO system_settings SET $data");
            $stmt->execute();
        }

        if ($stmt->affected_rows > 0) {
            $query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch_array();
            foreach ($query as $key => $value) {
                if (!is_numeric($key))
                    $_SESSION['setting_' . $key] = $value;
            }
            return 1;
        }
        return 0;
    }

    // Save airline
    function save_airlines() {
        extract($_POST);
        $data = "airlines = '$airlines'";

        if (!empty($_FILES['img']['tmp_name'])) {
            $fname = strtotime(date("Y-m-d H:i")) . "_" . $_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
            if ($move) {
                $data .= ", logo_path = '$fname'";
            }
        }

        $stmt = $this->db->prepare("INSERT INTO airlines_list SET $data ON DUPLICATE KEY UPDATE $data");
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Delete airline
    function delete_airlines() {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM airlines_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Delete user (new method added)
    function delete_user() {
        if (isset($_POST['id'])) {
            $id = $_POST['id']; // Get the ID of the user to delete
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id); // Bind the ID parameter
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return 1; // User successfully deleted
            }
        }
        return 0; // Deletion failed or no ID provided
    }

    // Save airport
    function save_airports() {
        extract($_POST);
        $data = "airport = '$airport', location = '$location'";

        $stmt = $this->db->prepare("INSERT INTO airport_list SET $data ON DUPLICATE KEY UPDATE $data");
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Delete airport
    function delete_airports() {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM airport_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Save flight
    function save_flight() {
        extract($_POST);
        $data = "airline_id = '$airline', plane_no = '$plane_no', departure_airport_id = '$departure_airport_id',
                arrival_airport_id = '$arrival_airport_id', departure_datetime = '$departure_datetime',
                arrival_datetime = '$arrival_datetime', seats = '$seats', price = '$price'";

        $stmt = $this->db->prepare("INSERT INTO flight_list SET $data ON DUPLICATE KEY UPDATE $data");
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Delete flight
    function delete_flight() {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM flight_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Book flight
    function book_flight() {
        extract($_POST);
        foreach ($name as $k => $value) {
            $data = "flight_id = $flight_id, name = ?, address = ?, contact = ?";
            $stmt = $this->db->prepare("INSERT INTO booked_flight SET $data");
            $stmt->bind_param("sss", $name[$k], $address[$k], $contact[$k]);
            $stmt->execute();
        }

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    // Update booked flight
    function update_booked() {
        extract($_POST);
        $data = "name = ?, address = ?, contact = ?";
        $stmt = $this->db->prepare("UPDATE booked_flight SET $data WHERE id = ?");
        $stmt->bind_param("sssi", $name, $address, $contact, $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }
}
?>
