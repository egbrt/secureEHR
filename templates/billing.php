<div id="reports">
<ul>
<?php
if ($unpaid_bills = $bills->getUnpaid()) {
    foreach ($unpaid_bills as $bill) {
        echo "<li id=" . $bill['id'] . ">" . $bill['date'] . " ";
        echo $bill['patient'] . "<br/>";
        echo "&nbsp; Intervention: " . $bill['intervention'] . "</br>";
        echo "&nbsp;&nbsp; for diagnosis: " . $bill['diagnosis'] . "<br/>";
        echo "&nbsp;&nbsp; billing code/price: " . $bill['billing'] . "</li>";
    }
}
?>
</ul>
</div>
<a id="backToReports" class="fancyButton" href="index.php?page=reports">Back to reports</a>
