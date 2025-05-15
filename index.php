<?php
session_start();

if (isset($_POST['mobile']) && isset($_POST['msg'])) {
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Bulk Email Sender</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="./script-scheduler.js"></script>

</head>
<body class="p-4">

  <h2>Schedule Bulk Email Campaign</h2>
  <hr>
  <form action="" method="POST" class="row gy-3">

    <!-- 1. Account selector -->
    <div class="col-md-4">	
	<label for="selectinput" class="form-label"><b>1. Select Account:</b></label>
	<select id="selectinput" name="account" class="form-select account" aria-label="Default select example">
		<option value="">- SELECT ACCOUNT -</option>
		<option value="TrustBook">TrustBook</option>
		<option value="Pblondon.live">Pblondon.live</option>
		<option value="Hostessmodel.work">Hostessmodel.work</option>
		<option value="Suite-Devs">Social Suite (Devs)</option>
		<option value="Trustpilot-Alerts">Trustpilot Alerts</option>
		<option value="Modeljobs.ageny-Jo">Modeljobs.ageny (Jo)</option>
		<option value="Imagemodels.pro-Jo">Imagemodels.pro (Jo)</option>
		<option value="Companions.social">Companions.social (Valentina)</option>
		<!--<option value="Trustpilot-Survey">Trustpilot Survey</option>
		<option value="TrustLeads">TrustLeads</option>
		<option value="TrustLeads-Meetup">TrustLeads (Meetup)</option>
		<option value="TrustLeads-Eventbrite">TrustLeads (Eventbrite)</option>					
		<option value="HomeChefs">Home Chefs</option>
		<option value="Survey-Meetup">Survey-Meetup</option>
		<option value="Survey-Eventbrite">Survey-Eventbrite</option>-->
	</select>
    </div>

    <!-- 2. Contacts CSV selector -->
    <div class="col-md-4">
	
	<div class="form-group">
		<label for="csvFileDropdown"><b>2. Select CSV</b></label>
		<select class="form-control" id="csvFileDropdown" onchange="loadCsvFileContent()">
			<option value="" disabled selected>Select a .csv file</option>
			<!-- Options will be dynamically added here using JavaScript -->
		</select>
		<div class="form-text">From /contacts folder</div>
	</div>

    </div>

    <!-- 3. Template selector -->
    <div class="col-md-4">
	<div class="form-group">
		<label for="txtFileDropdown"><b>3. Select Message</b></label>
		<select class="form-control" id="txtFileDropdown" onchange="loadTxtFileContent()">
			<option value="" disabled selected>Select a .txt file</option>
		</select>
		<div class="form-text">From /templates folder</div>
	</div>	
    </div>
	
    <!-- 4. Contacts -->
    <div class="col-12">
      <label for="fileContentCSV" class="form-label">Or Enter Recipients (no spaces between comma):</label>
<textarea class="form-control mobile" id="fileContentCSV" rows="3" placeholder="Mobile Numbers" name="mobile">
Jay,message.uk+Jay@gmail.com,suite.social
Jack,message.uk+Jack@gmail.com,modelx.chat
</textarea>
    </div>

    <!-- 5. Subject & Body -->
    <div class="col-12">
      <label for="subject" class="form-label">Or Enter Subject:</label>
      <input type="text" id="subject" class="form-control subject" name="subject" placeholder="Subject">
    </div>
    <div class="col-12">
      <label for="msg" class="form-label">Or Enter Body:</label>
	  <textarea id="msg" class="form-control msg" placeholder="Message" rows="6" name="msg"></textarea>
    </div>
	
    <!-- Begin Code - SAMUEL ADELOWOKAN -->
    <div class="col-12">
      <label for="days" class="form-label">Select Days</label><br>
      <div class="btn-group" role="group">
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Monday" value="Monday">
          <label class="btn btn-outline-primary" for="day-Monday">M</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Tuesday" value="Tuesday">
          <label class="btn btn-outline-primary" for="day-Tuesday">T</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Wednesday" value="Wednesday">
          <label class="btn btn-outline-primary" for="day-Wednesday">W</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Thursday" value="Thursday">
          <label class="btn btn-outline-primary" for="day-Thursday">T</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Friday" value="Friday">
          <label class="btn btn-outline-primary" for="day-Friday">F</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Saturday" value="Saturday">
          <label class="btn btn-outline-primary" for="day-Saturday">S</label>
                  <input type="checkbox" class="btn-check days" name="days[]" id="day-Sunday" value="Sunday">
          <label class="btn btn-outline-primary" for="day-Sunday">S</label>
              </div>
    </div>

    <div class="col-md-2">
      <label for="time" class="form-label">Time</label>
      <input type="time" name="time" id="time" class="form-control time" value="09:00" required>
    </div>
    <!-- End of code - SAMUEL ADELOWOKAN -->

    <br><br>

    <!-- 6. Submit -->
    <div class="col-12">
	  <button type="submit" class="btn btn-primary ajax_btn">Save Campaign</button>
    </div>
  </form>

	<div style="display: none;" class="spinner-border ajax_load" role="status">
		<span class="visually-hidden">Loading...</span>
	</div>
	
	<br>
	
	<!-- <p class="j_quantity_box" style="display: none;">
		Number of remaining emails:&nbsp;&nbsp; <span class="j_quantity badge text-bg-danger">0</span>
	</p> -->

	<br>

	<div class="box-message"></div>
    
    <!-- Begin Code - SAMUEL ADELOWOKAN -->
    <?php
    include_once('db/db.php');
    ?>

    <br><br>
    <hr>
    <h2>Existing Campaigns</h2>
<table class="table table-striped">
  <thead>
    <tr>
      <th>Account</th>
      <th>Days</th>
      <th>Time</th>
      <th>CSV/Mobile</th>
      <th>Sent/Total</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($campaigns as $idx => $campaign): ?>
      <?php
        // Count how many emails have been sent for this campaign
        $stmt = $db->prepare("SELECT COUNT(*) FROM campaign_logs WHERE campaign_id = :cid");
        $stmt->execute([':cid' => $campaign['id']]);
        $sentCount = $stmt->fetchColumn();

        // You can calculate total from CSV later
        $total = 0;
      ?>
      <tr>
        <td><?= htmlspecialchars($campaign['account']) ?></td>
        <td><?= htmlspecialchars($campaign['days']) ?></td>
        <td><?= htmlspecialchars($campaign['time']) ?></td>
        <td><?= nl2br(htmlspecialchars($campaign['mobile'])) ?></td>
        <td><span id="sent-<?= $idx ?>"><?= $sentCount ?></span>/<?= $total ?></td>
        <td>
          <button class="btn btn-sm btn-primary btn-edit" data-idx="<?= $idx ?>" data-id="<?= $campaign['id'] ?>">Edit</button>
          <button class="btn btn-sm btn-danger" onclick="deleteCampaign(<?= $campaign['id'] ?>)">Delete</button>
          <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#statsModal" data-idx="<?= $idx ?>" data-id="<?= $campaign['id'] ?>">Stats</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if (count($campaigns) == 0): ?>
<div class="m-2"> No Campaigns </div>
<?php endif; ?>
<br><br><br>

<!-- End Code - SAMUEL ADELOWOKAN -->

    

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Fetch the list of .txt and .csv files in the folders
        fetchFileList('txt', 'txtFileDropdown', 'templates');
        fetchFileList('csv', 'csvFileDropdown', 'contacts');
    });
	
    function fetchFileList(extension, dropdownId, folder) {
        // Fetch the list of files in the specified folder with the given extension
        fetch('file-list.php?extension=' + extension + '&folder=' + folder)
            .then(response => response.json())
            .then(data => {
                // Populate the dropdown with file names
                var dropdown = document.getElementById(dropdownId);
                dropdown.innerHTML = '<option value="" disabled selected>Select a .' + extension + ' file</option>'; // Clear existing options
                data.forEach(fileName => {
                    var option = document.createElement("option");
                    option.value = fileName;
                    option.text = fileName.replace('.' + extension, ''); // Remove file extension
                    dropdown.add(option);
                });
            })
            .catch(error => console.error('Error fetching file list:', error));
    }

    function loadTxtFileContent() {
        loadFileContent('txt', 'txtFileDropdown', ['subject', 'msg'], 'templates');
    }

    function loadCsvFileContent() {
        loadFileContent('csv', 'csvFileDropdown', 'fileContentCSV', 'contacts');
    }

    function loadFileContent(extension, dropdownId, textareaIds, folder) {
        var dropdown = document.getElementById(dropdownId);
        var selectedFileName = dropdown.options[dropdown.selectedIndex].value;

        if (selectedFileName) {
            // Construct the file path based on the selected file and folder
            var filePath = folder + '/' + selectedFileName;

            // Using fetch to load the file content asynchronously
            fetch(filePath)
                .then(response => response.text())
                .then(data => {
                    if (extension === 'txt') {
                        // Splitting the comment by semicolon and displaying in respective fields
                        var parts = data.split(';');
                        for (var i = 0; i < textareaIds.length; i++) {
                            document.getElementById(textareaIds[i]).value = parts[i].trim();
                        }
                    } else if (extension === 'csv') {
                        // Display CSV content in the textarea
                        document.getElementById(textareaIds).value = data;
                    }
                })
                .catch(error => console.error('Error loading file:', error));
        } else {
            // Clear the fields or textarea if no file is selected
            if (Array.isArray(textareaIds)) {
                textareaIds.forEach(textareaId => {
                    document.getElementById(textareaId).value = "";
                });
            } else {
                document.getElementById(textareaIds).value = "";
            }
        }
    }

    /** Begin Code - SAMUEL ADELOWOKAN */
    function deleteCampaign(id) {
  if (!confirm("Are you sure you want to delete this campaign?")) return;

  $.ajax({
    url: 'service/deletecampaign.php',
    method: 'POST',
    data: { id: id },
    success: function(response) {
      alert(response.message || "Deleted successfully");
      location.reload(); // reload page to refresh campaigns list
    },
    error: function() {
      alert("Error deleting campaign.");
    }
  });
}

$(document).ready(function () {
  $('.btn-edit').click(function () {
    const idx = $(this).data('idx');
    const campaign = window.campaigns[idx];
    if (!campaign) return;

    // Fill the form fields with the campaign data
    $('select[name="account"]').val(campaign.account);
    $('textarea[name="mobile"]').val(campaign.csv || '');  // Assuming csv stored in 'csv'
    $('input[name="subject"]').val(campaign.subject);
    $('textarea[name="msg"]').val(campaign.msg);

    // Reset all days checkboxes
    $('.days').prop('checked', false);
    if (campaign.days) {
      campaign.days.split(',').forEach(day => {
        $(`.days[value="${day.trim()}"]`).prop('checked', true);
      });
    }

    $('input[name="time"]').val(campaign.time);

    // Store campaign id for update
    $('form').attr('data-edit-id', campaign.id);

    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});

/** End Code - SAMUEL ADELOWOKAN */
</script>
<script>
  // Make campaigns available as JS array for easy access
  window.campaigns = <?php echo json_encode($campaigns); ?>;
</script>

</body>
</html>