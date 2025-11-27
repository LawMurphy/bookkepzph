<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

require '../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

// GCS bucket name
$bucketName = 'bookkepzfile';

// load GCS
$storage = new StorageClient([
    'keyFilePath' => __DIR__ . '/../credentials/bookkepz-key.json'
]);

$bucket = $storage->bucket($bucketName);

$user_id = $_SESSION['user_id'] ?? 1;

// =========================
//  FETCH USER INFO
// =========================
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// =========================
//  HANDLE FORM SUBMISSION
// =========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';
    $email      = $_POST['email'] ?? '';
    $job_title  = $_POST['job_title'] ?? '';
    $bio        = $_POST['bio'] ?? '';
    $address    = $_POST['address'] ?? '';
    $country    = $_POST['country'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $website    = $_POST['website'] ?? '';
    $delete_image = $_POST['delete_image'] ?? '';

    $profile_image = $user['profile_image'] ?? null;

    // =========================
    //  DELETE IMAGE FROM GCS
    // =========================
    if ($delete_image === '1' && !empty($profile_image)) {

        // extract Cloud Storage path
        $objectPath = str_replace("https://storage.googleapis.com/$bucketName/", "", $profile_image);

        $object = $bucket->object($objectPath);
        
        if ($object->exists()) {
            $object->delete();
        }

        $profile_image = null;
    }

    // =========================
    //  UPLOAD NEW IMAGE TO GCS
    // =========================
    elseif (!empty($_FILES['profile_image']['name'])) {

        $file_tmp  = $_FILES['profile_image']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['profile_image']['name']);

        // folder in bucket
        $objectPath = "profile/" . $file_name;

        // upload to Cloud Storage
        $bucket->upload(
            fopen($file_tmp, 'r'),
            [
                'name' => $objectPath,
            ]
        );

        // store public URL
        $profile_image = "https://storage.googleapis.com/$bucketName/$objectPath";
    }

    // =========================
    //  UPDATE USER RECORD
    // =========================
    $update = "UPDATE users SET 
        first_name=?, last_name=?, email=?, job_title=?, bio=?, address=?, 
        country=?, phone=?, website=?, profile_image=? WHERE id=?";

    $stmt = $pdo->prepare($update);
    $success = $stmt->execute([
        $first_name, $last_name, $email, $job_title, $bio, $address,
        $country, $phone, $website, $profile_image, $user_id
    ]);

    // =========================
    //  FRONTEND RESPONSE JS
    // =========================
    if ($success) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const alertBox = document.createElement('div');
                alertBox.textContent = '✅ Profile updated successfully!';
                Object.assign(alertBox.style, {
                    position: 'fixed', top: '70px', right: '20px',
                    background: '#28a745', color: '#fff',
                    padding: '12px 20px', borderRadius: '8px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
                    fontSize: '14px', zIndex: '99999',
                    opacity: '0', transition: 'opacity 0.3s ease'
                });
                document.body.appendChild(alertBox);
                setTimeout(() => alertBox.style.opacity = '1', 100);

                // loader
                setTimeout(() => {
                    const loader = document.createElement('div');
                    loader.className = 'loading-overlay';
                    loader.innerHTML = `
                        <div class='spinner-logo'>
                            <img src='../assets/img/bookkepz_logo.png' alt='Loading...'>
                        </div>`;
                    Object.assign(loader.style, {
                        position: 'fixed', top: '0', left: '0',
                        width: '100%', height: '100%',
                        background: 'rgba(255,255,255,0.9)',
                        display: 'flex', justifyContent: 'center',
                        alignItems: 'center', zIndex: '999999'
                    });
                    document.body.appendChild(loader);

                    const style = document.createElement('style');
                    style.innerHTML = `
                        .spinner-logo img { width: 90px; height: 90px; animation: spin 1.5s linear infinite; }
                        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
                    `;
                    document.head.appendChild(style);

                    setTimeout(() => window.location = 'settings.php', 2500);
                }, 2000);
            });
        </script>";
    } 
    else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                const alertBox = document.createElement('div');
                alertBox.textContent = '❌ Error updating profile.';
                Object.assign(alertBox.style, {
                    position: 'fixed', top: '70px', right: '20px',
                    background: '#dc3545', color: '#fff',
                    padding: '12px 20px', borderRadius: '8px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
                    fontSize: '14px', zIndex: '99999'
                });
                document.body.appendChild(alertBox);
                setTimeout(() => alertBox.remove(), 3000);
            });
        </script>";
    }
}
?>


<div class="profile-settings">

  <h2>Basic Information</h2>
  <hr>

  <form class="profile-form" method="POST" enctype="multipart/form-data">

    <div class="profile-image-section" 
     style="display:flex; flex-direction:column; align-items:center; gap:12px; margin-bottom:25px;">

  <div class="image-preview" 
       style="width:130px; height:130px; border-radius:50%; overflow:hidden; border:2px solid #ddd; background:#f5f5f5; display:flex; justify-content:center; align-items:center; cursor:pointer;"
       id="profileImageContainer">
  <img 
    id="profilePreview"
    src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '../assets/img/default-avatar.png' ?>"
    alt="Profile Picture"
    style="width:100%; height:100%; object-fit:cover; display:block;"
  >
  </div>

  <div style="display:flex; gap:8px;">
    <label for="profile_image" class="upload-btn" 
           style="display:inline-block; padding:8px 14px; background:#007bff; color:white; border-radius:6px; cursor:pointer; font-size:14px; transition:0.3s;">
      Upload Image
    </label>
    <button type="button" id="deleteImageBtn" 
            style="padding:8px 10px; font-size:13px; border:none; border-radius:6px; background:#dc3545; color:white; cursor:pointer;">
      Delete
    </button>
  </div>

  <input type="file" id="profile_image" name="profile_image" accept="image/*" hidden>
  <input type="hidden" id="delete_image" name="delete_image" value="0">
</div>



    <div class="form-row">
      <label>Name <span class="required">*</span></label>
      <div class="name-fields">
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <label>Email <span class="required">*</span></label>
      <div class="name-fields">
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly required>
      </div>
    </div>

    <div class="form-row">
      <label>Job Title</label>
      <input type="text" name="job_title" value="<?= htmlspecialchars($user['job_title'] ?? '') ?>">
    </div>

    <div class="form-row">
      <label>Brief Bio</label>
      <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>">
    </div>

    <div class="form-row">
      <label>Country</label>
      <select name="country">
        <option <?= $user['country'] === 'Philippines' ? 'selected' : '' ?>>Philippines</option>
        <option <?= $user['country'] === 'USA' ? 'selected' : '' ?>>USA</option>
        <option <?= $user['country'] === 'UK' ? 'selected' : '' ?>>UK</option>
      </select>
    </div>

    <h3>Contact Details</h3>
    <hr>

    <div class="form-row">
      <label>Phone</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
    </div>

    <div class="form-row">
      <label>Website</label>
      <input type="url" name="website" value="<?= htmlspecialchars($user['website'] ?? '') ?>">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-save">Save</button>
      <button type="reset" class="btn-cancel">Cancel</button>
    </div>

  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const fileInput = document.getElementById('profile_image');
  const profilePreview = document.getElementById('profilePreview');
  const deleteBtn = document.getElementById('deleteImageBtn');
  const imageContainer = document.getElementById('profileImageContainer');
  const deleteField = document.getElementById('delete_image');

  // 📸 Preview when selecting file
  fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = e => profilePreview.src = e.target.result;
      reader.readAsDataURL(file);
      deleteField.value = "0"; // reset delete flag
    }
  });

  // 🗑 Delete image — reset to default & mark deletion
  deleteBtn.addEventListener('click', () => {
    profilePreview.src = '../assets/img/default-avatar.png';
    fileInput.value = '';
    deleteField.value = "1"; // mark deletion
  });

  // 👁 Preview full image in modal
  imageContainer.addEventListener('click', () => {
    const modal = document.createElement('div');
    modal.classList.add('image-modal');
    modal.innerHTML = `
      <div style="
        position:fixed; top:0; left:0; width:100%; height:100%;
        background:rgba(0,0,0,0.7); display:flex; justify-content:center; align-items:center;
        z-index:9999;">
        <img src='${profilePreview.src}' style='max-width:90%; max-height:90%; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.5);'>
      </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', () => modal.remove());
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const alertBox = document.querySelector('.alert-success, .alert-error');
  if (alertBox) {
    alertBox.style.position = 'fixed';
    alertBox.style.top = '20px';
    alertBox.style.right = '20px';
    alertBox.style.zIndex = '9999';
    alertBox.style.padding = '12px 20px';
    alertBox.style.borderRadius = '8px';
    alertBox.style.color = '#fff';
    alertBox.style.fontSize = '14px';
    alertBox.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
    alertBox.style.transition = 'opacity 0.5s ease';
  }
});

const css = `
.loading-overlay {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.spinner-logo img {
  width: 80px;
  height: 80px;
  animation: spin 2s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
`;
const style = document.createElement('style');
style.textContent = css;
document.head.appendChild(style);
</script>
