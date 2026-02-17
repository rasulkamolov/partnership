<?php
session_start();
ob_start();

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/includes/functions.php';

// Auth Controller
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $s = $db->prepare("SELECT * FROM schools WHERE username = ?");
    $s->execute([$_POST['user']]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['pass'], $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['name'] = $u['name'];
        $_SESSION['role'] = $u['role'] ?? 'partner';
        header("Location: index.php"); exit;
    } $err = "Login yoki parol noto'g'ri";
}

$is_admin = ($_SESSION['role'] ?? '') === 'admin';
$uid = $_SESSION['user_id'] ?? 0;

// Export Handler (Must be before HTML)
if (isset($_POST['export_report'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="davomat_hisoboti_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ["O'quvchi Ismi", "Maktab", "Guruh", "Jami Kelgan", "Jami Kelmagan", "Davomat Foizi (%)"]);

    $start = $_POST['start_date'] ?? date('Y-m-01');
    $end = $_POST['end_date'] ?? date('Y-m-t');

    // Secure SQL using Prepared Statements
    $sql = "SELECT s.name, sc.name as sname, s.group_name,
            SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status='Absent' THEN 1 ELSE 0 END) as absent,
            COUNT(a.id) as total
            FROM students s
            JOIN schools sc ON s.school_id = sc.id
            LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN :start AND :end
            WHERE 1=1 " . ($is_admin ? "" : " AND s.school_id = :uid") .
            " GROUP BY s.id ORDER BY sname, s.name";

    $params = [':start' => $start, ':end' => $end];
    if (!$is_admin) {
        $params[':uid'] = $uid;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    foreach ($stmt->fetchAll() as $row) {
        $total = $row['total'] > 0 ? $row['total'] : 1;
        $rate = round(($row['present'] / $total) * 100, 1);
        fputcsv($output, [$row['name'], $row['sname'], $row['group_name'], $row['present'], $row['absent'], $rate]);
    }
    fclose($output);
    exit;
}

// POST Actions Controller
if ($is_admin && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_school'])) {
        $db->prepare("INSERT INTO schools (name, username, password, contact) VALUES (?, ?, ?, ?)")
           ->execute([$_POST['sch_name'], $_POST['sch_user'], password_hash($_POST['sch_pass'], PASSWORD_DEFAULT), $_POST['sch_contact']]);
        $redirect = isset($_GET['p']) && $_GET['p'] == 'schools' ? 'schools' : 'dashboard';
        header("Location: ?p=$redirect&msg=Hamkor ro'yxatga olindi"); exit;
    }
    if (isset($_POST['edit_school'])) {
        $params = [$_POST['sch_name'], $_POST['sch_user'], $_POST['sch_contact'], $_POST['school_id']];
        $sql = "UPDATE schools SET name = ?, username = ?, contact = ? WHERE id = ?";
        if (!empty($_POST['sch_pass'])) {
            $sql = "UPDATE schools SET name = ?, username = ?, contact = ?, password = ? WHERE id = ?";
            array_splice($params, 3, 0, password_hash($_POST['sch_pass'], PASSWORD_DEFAULT)); // Insert password into params
        }
        $db->prepare($sql)->execute($params);
        header("Location: ?p=schools&msg=Hamkor ma'lumotlari yangilandi"); exit;
    }
    if (isset($_POST['delete_school'])) {
        $db->prepare("DELETE FROM schools WHERE id = ?")->execute([$_POST['school_id']]);
        header("Location: ?p=schools&msg=Hamkor o'chirildi"); exit;
    }
    if (isset($_POST['add_student'])) {
        $pid = $_POST['existing_profile_id'] ?? '';
        $name = $_POST['st_name'];
        $sid = $_POST['st_school'];

        if (empty($pid)) {
            // Check if profile exists by name/school just in case
            $chk = $db->prepare("SELECT id FROM student_profiles WHERE name = ? AND school_id = ?");
            $chk->execute([$name, $sid]);
            $pid = $chk->fetchColumn();

            if (!$pid) {
                // Create new profile
                $db->prepare("INSERT INTO student_profiles (name, school_id) VALUES (?, ?)")->execute([$name, $sid]);
                $pid = $db->lastInsertId();
            }
        }

        // Loop through multiple groups
        // st_group, st_fee, st_schedule are arrays
        $groups = $_POST['st_group'];
        $fees = $_POST['st_fee'];
        $schedules = $_POST['st_schedule'];

        if (is_array($groups)) {
            $stmt = $db->prepare("INSERT INTO students (name, school_id, group_name, monthly_fee, schedule_type, profile_id, lessons_limit) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $grp_fetch = $db->prepare("SELECT lessons_per_month FROM groups WHERE name = ?");

            for ($i = 0; $i < count($groups); $i++) {
                $group = $groups[$i];
                $fee = $fees[$i];
                $schedule = $schedules[$i];

                // Fetch default lessons limit from group template
                $grp_fetch->execute([$group]);
                $limit = $grp_fetch->fetchColumn() ?: 12;

                $stmt->execute([$name, $sid, $group, $fee, $schedule, $pid, $limit]);
            }
        }

        header("Location: ?p=dashboard&msg=O'quvchi qo'shildi"); exit;
    }
    if (isset($_POST['add_enrollment_to_profile'])) {
        $groups = $_POST['st_group'];
        $fees = $_POST['st_fee'];
        $schedules = $_POST['st_schedule'];
        $pid = $_POST['profile_id'];
        $sid = $_POST['school_id'];
        $name = $_POST['st_name'];

        if (is_array($groups)) {
            $stmt = $db->prepare("INSERT INTO students (name, school_id, group_name, monthly_fee, schedule_type, profile_id, lessons_limit) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $grp_fetch = $db->prepare("SELECT lessons_per_month FROM groups WHERE name = ?");

            for ($i = 0; $i < count($groups); $i++) {
                $group = $groups[$i];
                $fee = $fees[$i];
                $schedule = $schedules[$i];

                // Fetch default lessons limit from group template
                $grp_fetch->execute([$group]);
                $limit = $grp_fetch->fetchColumn() ?: 12;

                $stmt->execute([$name, $sid, $group, $fee, $schedule, $pid, $limit]);
            }
        }
        header("Location: ?p=students&msg=Guruhlar qo'shildi"); exit;
    }
    if (isset($_POST['save_pricing'])) {
        foreach ($_POST['fee'] as $sid => $fee) {
            $db->prepare("UPDATE students SET monthly_fee = ?, schedule_type = ? WHERE id = ?")
               ->execute([$fee, $_POST['sch'][$sid], $sid]);
        }
        header("Location: ?p=pricing&msg=Narxlar yangilandi"); exit;
    }
    if (isset($_POST['save_att'])) {
        foreach ($_POST['att'] as $sid => $status) {
            // Check if already marked for today
            $exists = $db->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = date('now')");
            $exists->execute([$sid]);
            if ($exists->fetch()) {
                $db->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND date = date('now')")->execute([$status, $sid]);
            } else {
                $db->prepare("INSERT INTO attendance (student_id, status) VALUES (?, ?)")->execute([$sid, $status]);
            }
        }
        header("Location: ?p=attendance&msg=Davomat saqlandi"); exit;
    }
    if (isset($_POST['delete_student'])) {
        $db->prepare("DELETE FROM students WHERE id = ?")->execute([$_POST['student_id']]);
        header("Location: ?p=students&msg=O'quvchi o'chirildi"); exit;
    }
    if (isset($_POST['save_settings'])) {
        foreach ($_POST['settings'] as $key => $val) {
            $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")->execute([$key, $val]);
        }
        header("Location: ?p=settings&msg=Sozlamalar saqlandi"); exit;
    }
    if (isset($_POST['update_admin_profile'])) {
        $user = $_POST['admin_user'];
        $pass = $_POST['admin_pass'];

        $sql = "UPDATE schools SET username = ?";
        $params = [$user];

        if (!empty($pass)) {
            $sql .= ", password = ?";
            $params[] = password_hash($pass, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $_SESSION['user_id']; // Assuming master admin ID is in session, typically 1 or seeded

        // Also check if username already exists for another user?
        // Since we are updating, if unique constraint fails, PDO throws exception.

        try {
            $db->prepare($sql)->execute($params);
            $_SESSION['username'] = $user; // Update session
            header("Location: ?p=settings&msg=Profil yangilandi"); exit;
        } catch (PDOException $e) {
            header("Location: ?p=settings&err=Bu login band"); exit;
        }
    }
    if (isset($_POST['add_group'])) {
        // Schedule type removed from group creation
        $db->prepare("INSERT INTO groups (school_id, name, price, lessons_per_month) VALUES (NULL, ?, ?, ?)")
           ->execute([$_POST['name'], $_POST['price'], $_POST['lessons']]);
        header("Location: ?p=settings&msg=Guruh qo'shildi"); exit;
    }
    if (isset($_POST['delete_group'])) {
        $db->prepare("DELETE FROM groups WHERE id = ?")->execute([$_POST['group_id']]);
        header("Location: ?p=settings&msg=Guruh o'chirildi"); exit;
    }
    if (isset($_POST['update_student'])) {
        // Update enrollment
        $db->prepare("UPDATE students SET name = ?, school_id = ?, group_name = ?, monthly_fee = ?, schedule_type = ?, lessons_limit = ? WHERE id = ?")
           ->execute([$_POST['st_name'], $_POST['st_school'], $_POST['st_group'], $_POST['st_fee'], $_POST['st_schedule'], $_POST['st_lessons_limit'], $_POST['student_id']]);

        // Update profile name too? Yes, usually fix typo.
        // Get profile_id
        $prof = $db->prepare("SELECT profile_id FROM students WHERE id = ?");
        $prof->execute([$_POST['student_id']]);
        $pid = $prof->fetchColumn();

        if ($pid) {
            $db->prepare("UPDATE student_profiles SET name = ?, school_id = ? WHERE id = ?")->execute([$_POST['st_name'], $_POST['st_school'], $pid]);
            // Sync all other enrollments for this profile
            $db->prepare("UPDATE students SET name = ?, school_id = ? WHERE profile_id = ?")->execute([$_POST['st_name'], $_POST['st_school'], $pid]);
        }

        header("Location: ?p=students&msg=O'quvchi ma'lumotlari yangilandi"); exit;
    }
}

// View Router
if (!isset($_SESSION['user_id'])) {
    if (file_exists('app/pages/login.php')) {
        include 'app/pages/login.php';
    } else {
        // Fallback if file doesn't exist (should happen only once)
        echo "Login page missing.";
    }
} else {
    include 'app/includes/header.php';

    $page = $_GET['p'] ?? 'dashboard';
    $allowed_pages = ['dashboard', 'attendance', 'reports', 'monthly'];
    if ($is_admin) $allowed_pages = array_merge($allowed_pages, ['students', 'pricing', 'schools', 'settings', 'edit_student', 'add_enrollment']);

    if (in_array($page, $allowed_pages) && file_exists("app/pages/$page.php")) {
        include "app/pages/$page.php";
    } else {
        echo "<div class='text-center p-20 text-slate-400 font-bold'>Sahifa topilmadi (404 Not Found)</div>";
    }

    include 'app/includes/footer.php';
}
?>