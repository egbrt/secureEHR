<div id="calendarAppointments">
<input type="date" id="appointmentDate" value="<?php echo date('Y-m-d') ?>"/>
<div id="patientsBooked">
</div>
</div>

<div id="patientsNotes">
  <textarea id="patientsNotesInfo" placeholder="Write your notes about the next appointment for this patient here. These are displayed on the dashboard and can then be added to the structured information about this contact.&#10;&#10;It is possible to add some structuring to the note by preceding a line with R, S, O, A or P, like this:&#10;&#10;R fever&#10;S tired&#10;O no abnormalities&#10;A tired&#10;P blood test"></textarea>
  <button id="patientsNotesSave" class="fancyButton" disabled="disabled">Save</button>
</div>
    
