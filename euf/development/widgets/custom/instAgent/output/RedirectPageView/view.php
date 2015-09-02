<rn:meta controller_path="custom/instAgent/output/RedirectPageView"/>

<!-- Add HTML/PHP view code here -->

<?php
// redirect to location determined by widget
header("Location: {$this->data['js']['location']}");
?>