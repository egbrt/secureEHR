<p id="visitPatientId" patientId="<?php echo $patient->id ?>"><?php echo $patient->label ?></p>
<div id="dashboardButtons">
  <button class="fancyButton" id="visitSave" disabled>Save Contact</button>
  <button class="fancyButton" id="visitDone">Close Dashboard</button>
</div>

<div id="visitInfo">
  <input type="date" id="appointmentDate" name="appointmentDate" value="<?php echo date("Y-m-d") ?>"/>
</div>

<div id="visitAllFindingSet">
    <input type="text" id="visitAllFinding" name="visitAllFinding" autofocus placeholder="Choose Code (on the left), or enter free text."/>
    <button id="codeAllFinding">Code</button>
    <button id="addAllFinding">Add</button>
    <div id="visitAllFindings"><ul></ul></div>
</div>

<div id="visitPatientDetails">
  <p class="subHeader">Patient info</p>
  <div class="contents">
    <?php
      $text = str_replace("\n", "<br/>", $patient->info);
      echo $text;
    ?>
  </div>
</div>

<div id="visitEpisodes">
  <p class="subHeader">Episodes</p>
  <div class="contents"></div>
</div>

<div id="episodesMenu">
  <ul>
    <li value="visits">Show contacts</li>
    <li value="toggle">Re-Open</li>
  </ul>
</div>

<div id="visitPatientDrugs">
  <p class="subHeader">Medication</p>
  <div class="contents"></div>
</div>

<div id="visitPatientLaboratory">
  <p class="subHeader">Measurements and laboratory results</p>
  <div class="contents"></div>
</div>

<div id="visitPatientFunctioning">
  <p class="subHeader">Functioning and Patient Objectives</p>
  <div class="contents"></div>
</div>

<div id="functioningImportance">
  <ul>
    <li value="1">*</li>
    <li value="2">**</li>
    <li value="3">***</li>
    <li value="4">****</li>
    <li value="5">*****</li>
  </ul>
</div>

<div id="visitEpisodeVisits">
  <p class="subHeader">Visits in episode: <span id="episodeName"></span></p>
  <button id="closeVisits">x</button>
  <div class="contents"></div>
</div>

<div id="warningMedication"><p>First enter a diagnosis (under Assessment) for which the medication is intended.</p></div>
<div id="warningExistingEpisode"><p>There already is an episode with this diagnosis. If you want, select the existing episode to add this contact to it.</p></div>

<div id="icpc3-ct">
  <iframe id="codingtool" src="https://ct.icpc-3.info" width="100%" height="100%"/>
</div>
    
<div id="visitDrugs"></div>
<div id="question"></div>

