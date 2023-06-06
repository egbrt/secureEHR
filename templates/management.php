<div id="management">
  <p class="subHeader">Patient database management</p>
  <p>New patient data in the form of secPML can be uploaded to the database. Select the secPML file and press the Import button.</p>
  <form method="post" enctype="multipart/form-data" action="patientport.php">
    <p><input type="file" name="fileToUpload" id="fileToUpload"></p>
    <input type="submit" id="submitImport" value="Import" name="submit">
  </form>
  <hr/><hr/>
  <?php if (isset($_SESSION["currentPatient"])) {
    $patient = unserialize($_SESSION["currentPatient"]); ?>
    <p>The patient data can be exported to a secPML file. Click the Export button to create a secPML file with
    the data of the currently selected patient ("<?php echo $patient->label ?>").</p>
    <form method="post" action="patientport.php">
        <input type="submit" id="submitExport" value="Export" name="submit">
    </form>
    <hr/><hr/>
    <p>Press the Delete button to delete the currently selected patient from the database.</p>
    <form method="post" action="patientport.php">
        <input type="submit" id="submitDelete" value="Delete" name="submit">
    </form>
 <?php } ?>
</div>

<script>
  $("#submitImport").attr("disabled", true);
  $("#fileToUpload").change(function() {
    $("#submitImport").attr("disabled", ($("#fileToUpload").val() == ""))
  });
</script>

