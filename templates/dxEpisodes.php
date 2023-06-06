<div id="reports">
<ul>
<?php
if ($rows = $episodes->getAll()) {
    $i = 1;
    $counter = 1;
    while ($i < count($rows)) {
        if ($rows[$i]['name'] == $rows[$i-1]['name']) {
            $counter++;
        }
        else {
            echo "<li>" . $counter . " x " . $rows[$i-1]['name'] . "</li>";
            $counter = 1;
        }
        $i++;
    }
    echo "<li>" . $counter . " x " . $rows[$i-1]['name'] . "</li>";
}
?>
</ul>
</div>
<a id="backToReports" class="fancyButton" href="index.php?page=reports">Back to reports</a>


