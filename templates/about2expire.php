<?php

/**
 * Template form for warning about password expiring soon.
 *
 * Parameters:
 * - 'formTarget': Target URL for this page. This URL will receive a POST request.
 * - 'formData': Parameters which should be included as hidden inputs.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */


//$this->data['header'] = $this->t('{expirycheck:warning:warning_header}');

# netid will expire today
if ($this->data['daysleft'] == 0) {
  $this->data['header'] = $this->t('{iidpexpirycheck:warning:warning_header_today}', array(
        '%NETID%' => htmlspecialchars($this->data['netId'])
      ));
  
  $warning = $this->t('{iidpexpirycheck:warning:warning_today}', array(
        '%NETID%' => htmlspecialchars($this->data['netId'])
      ));

}
# netid will expire in one day
elseif ($this->data['daysleft'] == 1) {

  $this->data['header'] = $this->t('{iidpexpirycheck:warning:warning_header}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
        '%DAYS%' => $this->t('{iidpexpirycheck:warning:day}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
      ));
  
  $warning = $this->t('{iidpexpirycheck:warning:warning}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
        '%DAYS%' => $this->t('{iidpexpirycheck:warning:day}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
      ));

}
# netid has already expired
elseif ($this->data['daysleft'] < 0) {

  $this->data['header'] = $this->t('{iidpexpirycheck:warning:warning_header_past}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
      ));
  
  $warning = $this->t('{iidpexpirycheck:warning:warning_past}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
      ));

}
# netid will expire in next <daysleft> days
else {
  $this->data['header'] = $this->t('{iidpexpirycheck:warning:warning_header}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
        '%DAYS%' => $this->t('{iidpexpirycheck:warning:days}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
      ));
  
  $warning = $this->t('{iidpexpirycheck:warning:warning}', array(
        '%NETID%' => htmlspecialchars($this->data['netId']),
        '%DAYS%' => $this->t('{iidpexpirycheck:warning:days}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
      ));


}

//$this->data['header'] = str_replace("%DAYSLEFT%", $this->data['daysleft'], str_replace("%NETID%", $this->data['netId'], $this->t('{expirycheck:warning:warning_header}')));
$this->data['autofocus'] = 'yesbutton';

$this->includeAtTemplateBase('includes/header.php');

?>

<h3><?php echo $warning; ?></h3>
<p><?php echo $this->t('{iidpexpirycheck:warning:expiry_date_text}') . " " . $this->data['expireOnDate']; ?></p>


<form style="display: inline; margin: 0px; padding: 0px" action="<?php echo htmlspecialchars($this->data['formTarget']); ?>">

        <?php
                // Embed hidden fields...
                foreach ($this->data['formData'] as $name => $value) {
                        echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
                }
        ?>

    <input type="submit" name="changepwd" id="send" style="width:170px;" value="<?php echo htmlspecialchars($this->t('{iidpexpirycheck:warning:password_change_title}')) ?>" />

</form>         

<form style="display: inline; margin: 0px; padding: 0px" action="<?php echo htmlspecialchars($this->data['formTarget']); ?>">

  <?php
    // Embed hidden fields...
    foreach ($this->data['formData'] as $name => $value) {
      echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
    }
  ?>
  <input type="submit" name="continue" id="send" style="width:250px; border:0" value="<?php echo htmlspecialchars($this->t('{iidpexpirycheck:warning:btn_continue}')) ?>" />

</form>
<br>
<div id="trouble"></div>

<?php

$this->includeAtTemplateBase('includes/footer.php');
?>
