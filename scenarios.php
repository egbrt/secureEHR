<?php
include "templates/header.php";
?>

<div style="position:absolute;left:20px;right:20px;">
<h3>What do I need to use ICPC secureEHR?</h3>
<p>The software contains the backend of the ICPC secureEHR which should run on a <a href="https://en.wikipedia.org/wiki/Web_server" target-"_blank">web server<a/> that supports <a href="https://en.wikipedia.org/wiki/MySQL" target="_blank">MySQL</a>. The frontend of ICPC secureEHR is any (modern) <a href="https://en.wikipedia.org/wiki/Web_browser" target="_blank">web browser<a/>.<br/>

<p>Below are a number of possible scenarios to run ICPC secureEHR.</p>

<h4>On one computer</h4>
<p>Although it is possible to run the backend and frontend on the same computer this should only be used for development purposes. Just install <a href="https://en.wikipedia.org/wiki/XAMPP" target="_blank">XAMPP</a> on your computer and download (and unpack) the sources for ICPC secureEHR into the <em>htdocs</em> directory of your XAMPP installation. Next, start a web browser and point it to <em>localhost</em>.</p>

<h4>On a local area network</h4>
<p>In situations where there is no reliable internet connection, it might be a good idea to run ICPC secureEHR on a local network consisting of two or more computers. One of these computers acts as the server with an installation of a <a href="https://en.wikipedia.org/wiki/Web_server" target-"_blank">web server<a/> and <a href="https://en.wikipedia.org/wiki/MySQL" target="_blank">MySQL</a> database(s). The other computers on the network run <a href="https://en.wikipedia.org/wiki/Web_browser" target="_blank">web browsers<a/> that connect to the local server. A small server (e.g. <a href="https://en.wikipedia.org/wiki/Raspberry_Pi" target="_blank">https://en.wikipedia.org/wiki/Raspberry PI</a> could even be powered by solar power and batteries.</p>

<h4>Via a web hoster</h4>
<p>If the internet connection is reliable it is possible to book a <a href="https://en.wikipedia.org/wiki/Web_hosting_service" target="_blank">web hosting service</a> that includes <a href="https://en.wikipedia.org/wiki/MySQL" target="_blank">MySQL</a> database(s) and supports the programming language <a href="https://en.wikipedia.org/wiki/PHP">PHP</a>. The backend can then be run at the web hosting service and is accessible over the internet.</p>

</div>

<?php
include "templates/footer.php";
?>
