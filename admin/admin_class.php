<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors', 1);
header('Content-Type: application/json');

class Action
{
    private $db;

    public function __construct()
    {
        ob_start();
        include 'db_connect.php';
        $this->db = $conn;
    }

    function __destruct()
    {
        $this->db->close();
        ob_end_flush();
    }

    function login()
    {
        // Extract POST variables
        extract($_POST);

        // Clear previous session data
        session_unset();

        // Prepare the SQL statement to prevent SQL injection
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Store user type and ID in session
            $_SESSION['type'] = $user['type']; 
            $_SESSION['login_id'] = $user['id'];
            $_SESSION['login_name'] = $user['name'];


            if (password_verify($password, $user['password'])) {
                foreach ($user as $key => $value) {
                    if ($key != 'password' && !is_numeric($key)) {
                        $_SESSION['login_' . $key] = $value;
                    }
                }
                return 1; // Successful login
            } else {
                return 3; // Incorrect password
            }
        } else {
            return 2; // User not found
        }
    }

    function logout()
    {
        session_destroy();
        $_SESSION = [];
        header("location: login.php");
    }


    function save_user()
    {
        extract($_POST);
        // Save or update user with prepared statements
        if (empty($name) || empty($username)) {
            return json_encode(['status' => 'error', 'message' => 'Name and username are required']);
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? " . (isset($id) ? "AND id != ?" : ""));
        if (isset($id)) {
            $stmt->bind_param("si", $username, $id);
        } else {
            $stmt->bind_param("s", $username);
        }
        $stmt->execute();
        $check = $stmt->get_result();

        if ($check->num_rows > 0) {
            return json_encode(['status' => 'error', 'message' => 'Username already exists']);
        }

        // Prepare data
        $data = [
            'name' => $name,
            'username' => $username,
            'contact' => $contact ?? '',
            'address' => $address ?? '',
            'type' => $type ?? 3
        ];

        // Add password if provided
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Update or Insert logic
        if (isset($id) && $id > 0) {
            // Update existing user
            $set = implode(', ', array_map(function ($k) {
                return "$k = ?";
            }, array_keys($data)));

            $stmt = $this->db->prepare("UPDATE users SET $set WHERE id = ?");
            $params = array_values($data);
            $params[] = $id; // Add ID to the end of the parameters
            $stmt->bind_param(str_repeat('s', count($data)) . 'i', ...$params);

            if ($stmt->execute()) {
                return json_encode(['status' => 'success', 'message' => 'User  updated successfully']);
            }
        } else {
            // Insert new user
            $keys = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $this->db->prepare("INSERT INTO users ($keys) VALUES ($placeholders)");

            $stmt->bind_param(str_repeat('s', count($data)), ...array_values($data));
            if ($stmt->execute()) {
                return json_encode(['status' => 'success', 'message' => 'User  created successfully']);
            }
        }

        // If query fails
        return json_encode(['status' => 'error', 'message' => 'Failed to save user: ' . $this->db->error]);
    }

    function signup()
    {
        extract($_POST);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return json_encode(['status' => 'error', 'message' => 'Username already exists']);
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO users (name, contact, address, username, password, type) VALUES (?, ?, ?, ?, ?, 3)");
        $stmt->bind_param("sssss", $name, $contact, $address, $username, $hashed_password);

        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Registration successful!']);
        }

        return json_encode(['status' => 'error', 'message' => 'Registration failed.']);
    }

    function save_settings()
    {
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

    function save_airlines()
    {
        extract($_POST);
        $data = "airlines = '$airlines'";

        if (!empty($_FILES['img']['tmp_name'])) {
            $fname = strtotime(date("Y-m-d H:i")) . "_" . $_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
            if ($move) {
                $data .= ", logo_path = '$fname'";
            }
        }

        if (!empty($id)) {
            $stmt = $this->db->prepare("UPDATE airlines_list SET $data WHERE id = ?");
            $stmt->bind_param("i", $id);
        } else {
            $stmt = $this->db->prepare("INSERT INTO airlines_list SET $data");
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    function delete_airlines()
    {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM airlines_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    function delete_user()
    {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return json_encode(['status' => 'success', 'message' => 'User  successfully deleted']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Deletion failed or no ID provided']);
    }

    function save_airports()
    {
        extract($_POST);

        $data = "airport = '$airport', location = '$location'";

        if (!empty($id)) {
            $stmt = $this->db->prepare("UPDATE airport_list SET $data WHERE id = ?");
            $stmt->bind_param("i", $id);
        } else {
            $stmt = $this->db->prepare("INSERT INTO airport_list SET $data");
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return !empty($id) ? 2 : 1;
        }
        return 0;
    }

    function delete_airports()
    {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM airport_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        }
        return 0;
    }

    function save_flight()
    {
        extract($_POST);
        $data = "airline_id = '$airline', plane_no = '$plane_no', departure_airport_id = '$departure_airport_id',
            arrival_airport_id = '$arrival_airport_id', departure_datetime = '$departure_datetime',
            arrival_datetime = '$arrival_datetime', seats = '$seats', price = '$price'";

        $stmt = $this->db->prepare("INSERT INTO flight_list SET $data ON DUPLICATE KEY UPDATE $data");
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Flight successfully saved.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save flight.']);
        }
    }

    function delete_flight()
    {
        extract($_POST);
        $stmt = $this->db->prepare("DELETE FROM flight_list WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Flight successfully deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete flight.']);
        }
    }

    function book_flight()
    {
        extract($_POST);
        $success = true;
        $messages = [];

        // Prepare the SQL statement once
        $stmt = $this->db->prepare("INSERT INTO booked_flight (flight_id, name, address, contact) VALUES (?, ?, ?, ?)");

        foreach ($name as $k => $value) {
            // Bind parameters for each booking
            $stmt->bind_param("isss", $flight_id, $name[$k], $address[$k], $contact[$k]);

            // Execute the statement
            if (!$stmt->execute()) {
                $success = false;
                $messages[] = "Failed to book flight for " . htmlspecialchars($name[$k]) . ": " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();

        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Flight successfully booked.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => implode(", ", $messages)]);
        }
    }

    function update_booked()
    {
        extract($_POST);

        // Prepare the SQL statement
        $stmt = $this->db->prepare("UPDATE booked_flight SET name = ?, address = ?, contact = ? WHERE id = ?");

        // Bind parameters
        $stmt->bind_param("sssi", $name, $address, $contact, $id);

        // Execute the statement
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                return json_encode(['status' => 'success', 'message' => 'Booking successfully updated.']);
            } else {
                return json_encode(['status' => 'info', 'message' => 'No changes made to the booking.']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'Failed to update booking: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    }
}
