<?php
// Check authentication first
require_once 'constants/auth_check.php';
require_once 'constants/dbconnect.php';

if (!isset($_GET['permit_number'])) {
    echo '<div class="no-images">No permit number provided</div>';
    exit();
}

$permit_number = $_GET['permit_number'];

// Query to get all images for this PTW
$sql = "SELECT image_path, image_type FROM ptw_images WHERE permit_number = ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $permit_number);
$stmt->execute();
$result = $stmt->get_result();

$attachments = [];
$nonCompliance = [];

while ($row = $result->fetch_assoc()) {
    $imagePath = htmlspecialchars($row['image_path']);
    if ($row['image_type'] == 'noncompliance') {
        $nonCompliance[] = $imagePath;
    } else {
        $attachments[] = $imagePath;
    }
}

if (empty($attachments) && empty($nonCompliance)) {
    echo '<div class="no-images">No images found for this PTW</div>';
} else {
    echo '<div style="display: flex; flex-direction: column; gap: 15px;">';

    if (!empty($attachments)) {
        echo '<div><h5 style="margin: 5px 0; color: #555; border-bottom: 1px solid #eee;">General Attachments</h5>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">';
        foreach ($attachments as $path) {
            renderImage($path);
        }
        echo '</div></div>';
    }

    if (!empty($nonCompliance)) {
        echo '<div><h5 style="margin: 5px 0; color: #d32f2f; border-bottom: 1px solid #eee;">Non-Compliance Images</h5>';
        echo '<div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">';
        foreach ($nonCompliance as $path) {
            renderImage($path);
        }
        echo '</div></div>';
    }

    echo '</div>';
    
    // Add image modal HTML
    echo '<div id="imageModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; 
           width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); text-align: center;"
           onclick="closeImageModal()">
           <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                max-width: 90%; max-height: 90%;">
               <img id="modalImage" src="" style="max-width: 100%; max-height: 100%; border-radius: 10px;" />
               <div style="position: absolute; top: -40px; right: -40px; font-size: 30px; 
                    color: white; cursor: pointer; background: rgba(0,0,0,0.5); 
                    border-radius: 50%; width: 40px; height: 40px; display: flex; 
                    align-items: center; justify-content: center;" onclick="closeImageModal()">&times;</div>
           </div>
         </div>';
    
    // Add JavaScript for modal functionality
    echo '<script>
    function openImageModal(imageSrc) {
        document.getElementById("modalImage").src = imageSrc;
        document.getElementById("imageModal").style.display = "block";
        event.stopPropagation();
    }
    
    function closeImageModal() {
        document.getElementById("imageModal").style.display = "none";
    }
    
    // Close modal with Escape key
    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            closeImageModal();
        }
    });
    </script>';
}

$stmt->close();
$conn->close();

function renderImage($imagePath) {
    if (strpos($imagePath, 'assests/uploads/ptw_closure/') === 0) {
        $webPath = ltrim($imagePath, '/');
    } else {
        $webPath = 'assests/uploads/ptw_closure/' . ltrim($imagePath, '/');
    }
    
    echo '<div style="position: relative;">';
    echo '<img src="' . $webPath . '" alt="PTW Image" 
           style="max-width: 150px; max-height: 150px; border: 2px solid #ddd; 
                  border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); 
                  transition: transform 0.3s ease; cursor: pointer;"
           onclick="openImageModal(\'' . $webPath . '\')"
           onmouseover="this.style.transform=\'scale(1.1)\'"
           onmouseout="this.style.transform=\'scale(1)\'" />';
    echo '</div>';
}
?>