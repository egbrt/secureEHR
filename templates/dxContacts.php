<div id="reports">
<ul>
<?php
if ($assessments = $statements->getStatements(array("Assessment"))) {
    /*
    foreach ($assessments as $assessment) {
        echo "<li>" . $assessment['text'] . "</li>";
    }
    */
    
    $i = 1;
    $counter = 1;
    while ($i < count($assessments)) {
        if ($assessments[$i]['text'] == $assessments[$i-1]['text']) {
            $counter++;
        }
        else {
            echo "<li>" . $counter . " x " . $assessments[$i-1]['text'] . "</li>";
            $counter = 1;
        }
        $i++;
    }
    echo "<li>" . $counter . " x " . $assessments[$i-1]['text'] . "</li>";
}
?>
</ul>
</div>
<a id="backToReports" class="fancyButton" href="index.php?page=reports">Back to reports</a>

