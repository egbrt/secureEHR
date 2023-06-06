<div id="patientsDetails">
  <input type="text" id="patientsDetailsLabel" autofocus=true placeholder="This field consists of two parts separated by a colon: lastname:password." />
  <input type="text" id="patientsDetailsEmail" placeholder="The email address of the patient (needed to access the patient portal)" />
  <textarea id="patientsDetailsInfo" placeholder="This is essentially free text, and can be used in any way you see fit. In most cases this will contain the name and address information, but can also include social security number and/or background information about the patient.&#10;&#10;This is also a good place to put information about allergies, risk factors and contra-indications."></textarea>
  <button id="patientsDetailsVisit" class="fancyButton">Dashboard</button>
  <button id="patientsDetailsChange" class="fancyButton" disabled="disabled">Change patient info</button>
  <button id="patientsDetailsAdd" class="fancyButton" disabled="disabled">Add new patient</button>
</div>

<div id="patientsHelp">
  <p class="subHeader">Background information</p>
  <p>This is a secure EHR. All data is encrypted by a password that is only known by the patient.</p>
  <p>In the field identifying the patient it is suggested to use the National Healthcare Patient ID or if not available the last name of the patient as an identifier. The idenfier <strong>MUST</strong> consist of at least 8 characters. The identifier <strong>MUST</strong> be followed by a colon and a password. The password <strong>MUST</strong> consist of at least 8 characters and is used to encrypt the data. The password is not stored in the database.</p><p>It makes sense to use a password that is easy to remember for the patient. For example the password could consist of the birthdate of the patient in ISO format (yyyymmdd) followed by the first two letters of their first name, followed by the first two letters of the first name of the father, followed by the first two letters of the first name of the mother.</p>
  <p>For example: lastname:19980203ppffmm</p>
</div>

<div id="patientsNotes">
  <p class="subHeader">Notes</p>
  <textarea id="patientsNotesInfo" placeholder="Your notes for an appointment with this patient. These notes are displayed on the dashboard and can then be added to the structured information about this contact.&#10;&#10;It is possible to add some structuring to the note by preceding a line with R, S, O, A or P, like this:&#10;&#10;R fever&#10;S tired&#10;O no abnormalities&#10;A tired&#10;P blood test"></textarea>
  <button id="patientsNotesSave" class="fancyButton" disabled="disabled">Save</button>
  <button id="patientsNotesClear" class="fancyButton" disabled="disabled">Clear</button>
</div>

<?php if ($role=="admin") {?>
<div id="patientsPort">
  <p class="subHeader">Patient database management</p>
  <p>Press this button to enter database management, if you want to:<ul>
  <li>delete the current patient</li>
  <li>export the patient data</li>
  <li>import data for a new patient.</li>
  </ul></p>
  <p>In case you want to delete or export the current patient, first select the patient.<p>
  <form method="post" action="patientport.php">
    <input type="submit" id="submitManagement" value="Database management">
  </form>
</div>
<?php }?>

<!--
<div id="patientsAppointments">
  <input type="date" id="appointmentDate" value="<?php echo date("Y-m-d") ?>"/>
  <input type="time" id="appointmentTime" value="<?php echo date("H:i") ?>"/>
  <button id="appointmentSet" class="fancyButton">Create appointment</button>
  <div id="patientsBooked"></div>
</div>
-->
