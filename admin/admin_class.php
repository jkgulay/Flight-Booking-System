<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

class Action
{
    private $db;

    public function __construct()
    {
        ob_start();
        include 'db_connect.php'; // Ensure this file contains your PDO connection code
        $this->db = $conn;
    }

    function __destruct()
    {
        // No need to close PDO connection explicitly; it will close when the script ends
        ob_end_flush();
    }

    function login()
    {
        // Extract POST variables
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Clear previous session data
        session_unset();

        // Prepare the SQL statement to prevent SQL injection
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user exists
        if ($user) {
            // Store user type and ID in session
            $_SESSION['type'] = $user['type'];
            $_SESSION['login_id'] = $user['id'];
            $_SESSION['login_name'] = $user['name'];

            if (password_verify($password, $user['password'])) {
                return json_encode(['status' => 'success', 'message' => 'Login successful']);
            } else {
                return json_encode(['status' => 'error', 'message' => 'Incorrect password']);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => 'User  not found']);
        }
    }

    function logout()
    {
        session_destroy();
        header("Location: login.php");
        exit();
    }

    function save_user()
    {
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $password = $_POST['password'] ?? '';
        $id = $_POST['id'] ?? null;

        if (empty($name) || empty($username)) {
            return json_encode(['status' => 'error', 'message' => 'Name and username are required']);
        }

        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username" . ($id ? " AND id != :id" : ""));
        $stmt->bindParam(':username', $username);
        if ($id) {
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return json_encode(['status' => 'error', 'message' => 'Username already exists']);
        }

        // Prepare data
        $data = [
            'name' => $name,
            'username' => $username,
            'contact' => $contact,
            'address' => $address,
            'type' => $_POST['type'] ?? 3
        ];

        // Add password if provided
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Update or Insert logic
        if ($id) {
            // Update existing user
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $stmt = $this->db->prepare("UPDATE users SET $set WHERE id = :id");
            foreach ($data as $key => &$value) {
                $stmt->bindParam(":$key", $value);
            }
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return json_encode(['status' => 'success', 'message' => 'User  updated successfully']);
            }
        } else {
            // Insert new user
            $keys = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));
            $stmt = $this->db->prepare("INSERT INTO users ($keys) VALUES ($placeholders)");
            foreach ($data as $key => &$value) {
                $stmt->bindParam(":$key", $value);
            }
            if ($stmt->execute()) {
                return json_encode(['status' => 'success', 'message' => 'User  created successfully']);
            }
        }

        // If query fails
        return json_encode(['status' => 'error', 'message' => 'Failed to save user: ' . $this->db->errorInfo()[2]]);
    }

    function signup()
    {
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return json_encode(['status' => 'error', 'message' => 'Username already exists']);
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, contact, address, username, password, type) VALUES (:name, :contact, :address, :username, :password, 3)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'Registration successful!']);
        }

        return json_encode(['status' => 'error', 'message' => 'Registration failed.']);
    }

    function save_settings()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $about = $_POST['about'] ?? '';

        $data = "name = :name, email = :email, contact = :contact, about_content = :about_content";

        if (!empty($_FILES['img']['tmp_name'])) {
            $fname = strtotime(date('y-m-d H:i')) . '_' . $_FILES['img']['name'];
            $move = move_uploaded_file($_FILES['img']['tmp_name'], '../assets/img/' . $fname);
            if ($move) {
                $data .= ", cover_img = :cover_img";
            }
        }

        $stmt = $this->db->prepare("SELECT * FROM system_settings");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare("UPDATE system_settings SET $data");
        } else {
            $stmt = $this->db->prepare("INSERT INTO system_settings SET $data");
        }

        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':about_content', htmlentities($about));

        if (isset($fname)) {
            $stmt->bindParam(':cover_img', $fname);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $query = $this->db->query("SELECT * FROM system_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            foreach ($query as $key => $value) {
                if (!is_numeric($key)) {
                    $_SESSION['setting_' . $key] = $value;
                }
            }
            return json_encode(['status' => 'success', 'message' => 'Settings saved successfully']);
        }
        return json_encode(['status' => 'error', 'message' => 'Failed to save settings']);
    }

    function save_airlines()
    {
        global $conn; // Ensure you have access to the database connection

        // Disable output buffering
        ob_clean();

        // Set content type to JSON
        header('Content-Type: application/json');

        // Input validation
        $airlines = trim($_POST['airlines'] ?? '');
        $id = $_POST['id'] ?? null;

        // Validate airline name
        if (empty($airlines)) {
            echo json_encode(['status' => 'error', 'message' => 'Airline name cannot be empty.']);
            exit;
        }

        // File upload handling
        $fname = null;

        try {
            // Handle file upload if a file is present
            if (!empty($_FILES['img']['tmp_name'])) {
                // Validate file
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                $fileType = $_FILES['img']['type'];
                $fileSize = $_FILES['img']['size'];

                if (!in_array($fileType, $allowedTypes)) {
                    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.']);
                    exit;
                }

                if ($fileSize > $maxFileSize) {
                    echo json_encode(['status' => 'error', 'message' => 'File size exceeds 5MB limit.']);
                    exit;
                }

                // Generate unique filename
                $fname = strtotime(date("Y-m-d H:i")) . "_" . basename($_FILES['img']['name']);
                $uploadPath = '../assets/img/' . $fname;

                // Ensure upload directory exists
                if (!is_dir('../assets/img/')) {
                    mkdir('../assets/img/', 0755, true);
                }

                // Move uploaded file
                if (!move_uploaded_file($_FILES['img']['tmp_name'], $uploadPath)) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to upload logo.']);
                    exit;
                }
            }

            // Prepare SQL statement
            if ($id) {
                // Update existing airline
                if ($fname) {
                    // Update with new logo
                    $stmt = $conn->prepare("
                        UPDATE airlines_list 
                        SET airlines = :airlines, 
                            logo_path = :logo_path 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':logo_path', $fname);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                } else {
                    // Update without changing logo
                    $stmt = $conn->prepare("
                        UPDATE airlines_list 
                        SET airlines = :airlines 
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                }
            } else {
                // Insert new airline
                if ($fname) {
                    $stmt = $conn->prepare("
                        INSERT INTO airlines_list (airlines, logo_path) 
                        VALUES (:airlines, :logo_path)
                    ");
                    $stmt->bindParam(':logo_path', $fname);
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO airlines_list (airlines) 
                        VALUES (:airlines)
                    ");
                }
            }

            // Bind airlines name
            $stmt->bindParam(':airlines', $airlines);

            // Execute statement
            $result = $stmt->execute();

            if ($result) {
                echo json_encode([
                    'status' => 'success',
                    'message' => $id ? 'Airline updated successfully.' : 'Airline added successfully.'
                ]);
            } else {
                // Log detailed error information
                error_log("Airline save error: " . print_r($stmt->errorInfo(), true));

                // If a logo was uploaded but save failed, remove the uploaded file
                if ($fname && file_exists('../assets/img/' . $fname)) {
                    unlink('../assets/img/' . $fname);
                }

                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save airline details.'
                ]);
            }
        } catch (PDOException $e) {
            // Log the full error
            error_log("PDO Error in save_airlines: " . $e->getMessage());

            // If a logo was uploaded but an exception occurred, remove the uploaded file
            if ($fname && file_exists('../assets/img/' . $fname)) {
                unlink('../assets/img/' . $fname);
            }

            echo json_encode([
                'status' => 'error',
                'message' => 'Database error occurred: ' . $e->getMessage()
            ]);
        }

        exit;
    }
    function delete_airlines()
    {
        global $conn;

        ob_clean();
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid airline ID.'
            ]);
            exit;
        }

        try {
            $conn->beginTransaction();

            $checkStmt = $conn->prepare("
            SELECT COUNT(*) as flight_count 
            FROM flight_list 
            WHERE airline_id = :id
        ");
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            $dependencyCheck = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($dependencyCheck['flight_count'] > 0) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Cannot delete airline. There are existing flights associated with this airline.'
                ]);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM airlines_list WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if ($result && $stmt->rowCount() > 0) {
                $conn->commit();

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Airline successfully deleted.'
                ]);
            } else {
                $conn->rollBack();

                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to delete airline. Airline not found.'
                ]);
            }
        } catch (PDOException $e) {
            $conn->rollBack();

            error_log("Airline delete error: " . $e->getMessage());

            echo json_encode([
                'status' => 'error',
                'message' => 'Database error occurred. Please try again.'
            ]);
        }

        exit;
    }

    function delete_user()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return json_encode(['status' => 'success', 'message' => 'User  successfully deleted']);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Deletion failed or no ID provided']);
    }

    function save_airports()
    {
        global $conn;
        $airport = $_POST['airport'] ?? '';
        $location = $_POST['location'] ?? '';
        $id = $_POST['id'] ?? null;

        ob_clean();

        if (empty($airport) || empty($location)) {
            echo "0";
            exit;
        }

        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE airport_list SET airport = :airport, location = :location WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':airport', $airport);
                $stmt->bindParam(':location', $location);
                $result = $stmt->execute();

                echo $result ? "2" : "0";
            } else {
                $stmt = $conn->prepare("INSERT INTO airport_list (airport, location) VALUES (:airport, :location)");
                $stmt->bindParam(':airport', $airport);
                $stmt->bindParam(':location', $location);
                $result = $stmt->execute();

                echo $result ? "1" : "0";
            }
            exit;
        } catch (PDOException $e) {
            error_log("Airport save error: " . $e->getMessage());
            echo "0";
            exit;
        }
    }

    function delete_airports()
    {
        global $conn;
        $id = $_POST['id'] ?? null;

        ob_clean();

        if (!$id) {
            echo "0";
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM airport_list WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if ($result && $stmt->rowCount() > 0) {
                echo "1";
            } else {
                echo "0";
            }
            exit;
        } catch (PDOException $e) {
            error_log("Airport delete error: " . $e->getMessage());
            echo "0";
            exit;
        }
    }

    function save_flight()
    {
        global $conn;

        // Disable output buffering and clear any previous output
        ob_clean();

        // Set JSON content type
        header('Content-Type: application/json');

        // Input validation
        $flight_id = $_POST['id'] ?? null;
        $airline_id = $_POST['airline'] ?? '';
        $plane_no = $_POST['plane_no'] ?? '';
        $departure_airport_id = $_POST['departure_airport_id'] ?? '';
        $arrival_airport_id = $_POST['arrival_airport_id'] ?? '';
        $departure_datetime = $_POST['departure_datetime'] ?? '';
        $arrival_datetime = $_POST['arrival_datetime'] ?? '';
        $seats = $_POST['seats'] ?? 0;
        $price = $_POST['price'] ?? 0.00;

        // Server-side validation
        $errors = [];
        if (empty($airline_id)) $errors[] = 'Airline is required';
        if (empty($plane_no)) $errors[] = 'Plane number is required';
        if (empty($departure_airport_id)) $errors[] = 'Departure airport is required';
        if (empty($arrival_airport_id)) $errors[] = 'Arrival airport is required';
        if (empty($departure_datetime)) $errors[] = 'Departure datetime is required';
        if (empty($arrival_datetime)) $errors[] = 'Arrival datetime is required';
        if ($seats <= 0) $errors[] = 'Seats must be a positive number';
        if ($price <= 0) $errors[] = 'Price must be a positive number';

        // Check for validation errors
        if (!empty($errors)) {
            echo json_encode([
                'status' => 'error',
                'message' => implode(', ', $errors)
            ]);
            exit;
        }

        try {
            // Begin transaction
            $conn->beginTransaction();

            if ($flight_id) {
                // Update existing flight
                $stmt = $conn->prepare("
                UPDATE flight_list 
                SET airline_id = :airline_id, 
                    plane_no = :plane_no, 
                    departure_airport_id = :departure_airport_id, 
                    arrival_airport_id = :arrival_airport_id, 
                    departure_datetime = :departure_datetime, 
                    arrival_datetime = :arrival_datetime, 
                    seats = :seats, 
                    price = :price 
                WHERE id = :id
            ");
                $stmt->bindParam(':id', $flight_id);
            } else {
                // Insert new flight
                $stmt = $conn->prepare("
                INSERT INTO flight_list (
                    airline_id, plane_no, departure_airport_id, 
                    arrival_airport_id, departure_datetime, 
                    arrival_datetime, seats, price
                ) VALUES (
                    :airline_id, :plane_no, :departure_airport_id, 
                    :arrival_airport_id, :departure_datetime, 
                    :arrival_datetime, :seats, :price
                )
            ");
            }

            // Bind parameters
            $stmt->bindParam(':airline_id', $airline_id);
            $stmt->bindParam(':plane_no', $plane_no);
            $stmt->bindParam(':departure_airport_id', $departure_airport_id);
            $stmt->bindParam(':arrival_airport_id', $arrival_airport_id);
            $stmt->bindParam(':departure_datetime', $departure_datetime);
            $stmt->bindParam(':arrival_datetime', $arrival_datetime);
            $stmt->bindParam(':seats', $seats, PDO::PARAM_INT);
            $stmt->bindParam(':price', $price, PDO::PARAM_STR);

            // Execute and commit
            $stmt->execute();
            $conn->commit();

            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Flight successfully saved.'
            ]);
        } catch (PDOException $e) {
            // Rollback transaction
            $conn->rollBack();

            // Log the error
            error_log("Flight save error: " . $e->getMessage());

            // Return error response
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        // Ensure no further output
        exit;
    }

    function delete_flight()
    {
        global $conn;
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? null;

        if ($id) {
            try {
                $stmt = $conn->prepare("DELETE FROM flight_list WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Flight successfully deleted.']);
                    exit;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No flight found with the provided ID.']);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete flight: ' . $e->getMessage()]);
                exit;
            }
        }

        echo json_encode(['status' => 'error', 'message' => 'No ID provided for deletion.']);
        exit;
    }


    function book_flight()
    {
        $flight_id = $_POST['flight_id'] ?? '';
        $names = $_POST['name'] ?? [];
        $addresses = $_POST['address'] ?? [];
        $contacts = $_POST['contact'] ?? [];
        $success = true;
        $messages = [];

        // Prepare the SQL statement once
        $stmt = $this->db->prepare("INSERT INTO booked_flight (flight_id, name, address, contact, status) VALUES (:flight_id, :name, :address, :contact, :status)");

        foreach ($names as $k => $name) {
            // Bind parameters for each booking
            $stmt->bindParam(':flight_id', $flight_id);
            $stmt->bindParam(':name', $names[$k]);
            $stmt->bindParam(':address', $addresses[$k]);
            $stmt->bindParam(':contact', $contacts[$k]);
            $status = 'pending'; // Set a default status or get it from POST if needed
            $stmt->bindParam(':status', $status);
            // Execute the statement
            if (!$stmt->execute()) {
                $success = false;
                $messages[] = "Failed to book flight for " . htmlspecialchars($names[$k]) . ": " . $stmt->errorInfo()[2];
            }
        }

        if ($success) {
            return json_encode(['status' => 'success', 'message' => 'Flight successfully booked.']);
        } else {
            return json_encode(['status' => 'error', 'message' => implode(", ", $messages)]);
        }
    }

    function update_booked()
    {
        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $address = $_POST['address'] ?? '';
        $contact = $_POST['contact'] ?? '';

        if ($id) {
            // Prepare the SQL statement
            $stmt = $this->db->prepare("UPDATE booked_flight SET name = :name, address = :address, contact = :contact WHERE id = :id");

            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':id', $id);

            // Execute the statement
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    return json_encode(['status' => 'success', 'message' => 'Booking successfully updated.']);
                } else {
                    return json_encode(['status' => 'info', 'message' => 'No changes made to the booking.']);
                }
            } else {
                return json_encode(['status' => 'error', 'message' => 'Failed to update booking: ' . $stmt->errorInfo()[2]]);
            }
        }
        return json_encode(['status' => 'error', 'message' => 'No ID provided for update.']);
    }
}
