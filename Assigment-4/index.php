<?php
session_start();

$errors = [];
$old = [];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'student_name' => 'Student Name',
        'pob' => 'Place of Birth',
        'dob' => 'Date of Birth',
        'address' => 'Address',
        'gender' => 'Gender',
        'telephone' => 'Student Telephone',
        'email' => 'Email',
        'parent_name' => 'Parent/Guardian Name',
        'parent_telephone' => 'Parent/Guardian Telephone',
        'relationship' => 'Relationship',
        'student_id' => 'Student ID',
        'faculty' => 'Faculty',
        'department' => 'Department',
        'class' => 'Class',
        'registration_date' => 'Registration Date'
    ];

    foreach ($fields as $key => $label) {
        $old[$key] = trim($_POST[$key] ?? '');
        if ($old[$key] === '') {
            $errors[$key] = "$label is required.";
        }
    }

    if (!isset($errors['student_name']) &&
        !preg_match("/^[\p{L}\s'-]+$/u", $old['student_name'])) {
        $errors['student_name'] = 'Student Name must contain only letters, spaces, apostrophes, or hyphens.';
    }

    if (!isset($errors['email']) && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }

    if (!isset($errors['gender']) && !in_array($old['gender'], ['Male', 'Female', 'Other'], true)) {
        $errors['gender'] = 'Choose a valid gender option.';
    }

    if (!isset($errors['dob']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['dob'])) {
        $errors['dob'] = 'Enter a valid date of birth.';
    }

    if (!isset($errors['registration_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $old['registration_date'])) {
        $errors['registration_date'] = 'Enter a valid registration date.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['image'] = 'Student image is required. Please choose an image file.';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = 'The image upload failed. Please try again.';
    } else {
        $file = $_FILES['image'];
        $maxSize = 3 * 1024 * 1024;
        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        if ($file['size'] > $maxSize) {
            $errors['image'] = 'Image must be 3 MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            if (!isset($allowedMimeTypes[$mime])) {
                $errors['image'] = 'Only PNG, JPG, JPEG, GIF, or WEBP image files are allowed.';
            }
        }
    }

    if (!$errors) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = $allowedMimeTypes[$mime];
        $safeFileName = bin2hex(random_bytes(12)) . '.' . $extension;
        $destination = $uploadDir . $safeFileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $errors['image'] = 'Could not save the uploaded image. Check the uploads folder permissions.';
        } else {
            $_SESSION['student_data'] = $old;
            $_SESSION['student_data']['image'] = 'uploads/' . $safeFileName;
            header('Location: result.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <p class="eyebrow">Jamhuriya University of Science &amp; Technology</p>
        <h1>Student Registration Form</h1>
        <p>Complete all sections to register student information.</p>
    </header>

    <?php if ($errors): ?>
        <div class="alert alert-error" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $message): ?>
                    <li><?= h($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="index.php" method="post" enctype="multipart/form-data" novalidate>
        <section class="form-section">
            <h2>1. Personal Information</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="student_name">Full Name *</label>
                    <input id="student_name" name="student_name" type="text" required
                           value="<?= h($old['student_name'] ?? '') ?>"
                           pattern="[\p{L}\s'-]+" autocomplete="name">
                    <?php if (isset($errors['student_name'])): ?><small class="error"><?= h($errors['student_name']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="pob">Place of Birth (POB) *</label>
                    <input id="pob" name="pob" type="text" required value="<?= h($old['pob'] ?? '') ?>">
                    <?php if (isset($errors['pob'])): ?><small class="error"><?= h($errors['pob']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="dob">Date of Birth (DOB) *</label>
                    <input id="dob" name="dob" type="date" required value="<?= h($old['dob'] ?? '') ?>">
                    <?php if (isset($errors['dob'])): ?><small class="error"><?= h($errors['dob']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="address">Address *</label>
                    <input id="address" name="address" type="text" required value="<?= h($old['address'] ?? '') ?>" autocomplete="street-address">
                    <?php if (isset($errors['address'])): ?><small class="error"><?= h($errors['address']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <?php foreach (['Male', 'Female', 'Other'] as $gender): ?>
                            <option value="<?= h($gender) ?>" <?= (($old['gender'] ?? '') === $gender) ? 'selected' : '' ?>><?= h($gender) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['gender'])): ?><small class="error"><?= h($errors['gender']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="telephone">Telephone *</label>
                    <input id="telephone" name="telephone" type="tel" required value="<?= h($old['telephone'] ?? '') ?>" autocomplete="tel">
                    <?php if (isset($errors['telephone'])): ?><small class="error"><?= h($errors['telephone']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="email">Email *</label>
                    <input id="email" name="email" type="email" required value="<?= h($old['email'] ?? '') ?>" autocomplete="email">
                    <?php if (isset($errors['email'])): ?><small class="error"><?= h($errors['email']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="image">Student Image (PNG, JPG, JPEG, GIF, WEBP) *</label>
                    <input id="image" name="image" type="file" accept=".png,.jpg,.jpeg,.gif,.webp,image/png,image/jpeg,image/gif,image/webp" required>
                    <small>Maximum file size: 3 MB.</small>
                    <?php if (isset($errors['image'])): ?><small class="error"><?= h($errors['image']) ?></small><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h2>2. Parent Information</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="parent_name">Parent/Guardian Name *</label>
                    <input id="parent_name" name="parent_name" type="text" required value="<?= h($old['parent_name'] ?? '') ?>">
                    <?php if (isset($errors['parent_name'])): ?><small class="error"><?= h($errors['parent_name']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="parent_telephone">Parent/Guardian Telephone *</label>
                    <input id="parent_telephone" name="parent_telephone" type="tel" required value="<?= h($old['parent_telephone'] ?? '') ?>">
                    <?php if (isset($errors['parent_telephone'])): ?><small class="error"><?= h($errors['parent_telephone']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="relationship">Relationship *</label>
                    <select id="relationship" name="relationship" required>
                        <option value="">Select relationship</option>
                        <?php foreach (['Father', 'Mother', 'Guardian', 'Other'] as $relationship): ?>
                            <option value="<?= h($relationship) ?>" <?= (($old['relationship'] ?? '') === $relationship) ? 'selected' : '' ?>><?= h($relationship) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['relationship'])): ?><small class="error"><?= h($errors['relationship']) ?></small><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h2>3. Academic Information</h2>
            <div class="form-grid">
                <div class="field">
                    <label for="student_id">Student ID *</label>
                    <input id="student_id" name="student_id" type="text" required value="<?= h($old['student_id'] ?? '') ?>">
                    <?php if (isset($errors['student_id'])): ?><small class="error"><?= h($errors['student_id']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="faculty">Faculty *</label>
                    <input id="faculty" name="faculty" type="text" required value="<?= h($old['faculty'] ?? '') ?>">
                    <?php if (isset($errors['faculty'])): ?><small class="error"><?= h($errors['faculty']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="department">Department *</label>
                    <input id="department" name="department" type="text" required value="<?= h($old['department'] ?? '') ?>">
                    <?php if (isset($errors['department'])): ?><small class="error"><?= h($errors['department']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="class">Class *</label>
                    <input id="class" name="class" type="text" required value="<?= h($old['class'] ?? '') ?>">
                    <?php if (isset($errors['class'])): ?><small class="error"><?= h($errors['class']) ?></small><?php endif; ?>
                </div>
                <div class="field">
                    <label for="registration_date">Registration Date *</label>
                    <input id="registration_date" name="registration_date" type="date" required value="<?= h($old['registration_date'] ?? '') ?>">
                    <?php if (isset($errors['registration_date'])): ?><small class="error"><?= h($errors['registration_date']) ?></small><?php endif; ?>
                </div>
            </div>
        </section>

        <p class="required-note">* All fields are required.</p>
        <button class="button" type="submit">Register Student</button>
    </form>
</main>
</body>
</html>
