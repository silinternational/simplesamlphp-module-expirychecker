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


//$this->data['header'] = $this->t('{expirychecker:warning:warning_header}');

if ($this->data['daysleft'] == 0) {
    
    // Expires today.
    
    $this->data['header'] = $this->t('{expirychecker:warning:warning_header_today}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName'])
    ]);
    
    $warning = $this->t('{expirychecker:warning:warning_today}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName'])
    ]);
} elseif ($this->data['daysleft'] == 1) {
    
    // Expires tomorrow.
    
    $this->data['header'] = $this->t('{expirychecker:warning:warning_header}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
        '%DAYS%' => $this->t('{expirychecker:warning:day}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
    ]);
    
    $warning = $this->t('{expirychecker:warning:warning}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
        '%DAYS%' => $this->t('{expirychecker:warning:day}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
    ]);
} elseif ($this->data['daysleft'] < 0) {
    
    // Has already expired.
    
    $this->data['header'] = $this->t('{expirychecker:warning:warning_header_past}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
    ]);
    
    $warning = $this->t('{expirychecker:warning:warning_past}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
    ]);
} else {
    
    // Will expire in <daysleft> days.
    
    $this->data['header'] = $this->t('{expirychecker:warning:warning_header}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
        '%DAYS%' => $this->t('{expirychecker:warning:days}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
    ]);
    
    $warning = $this->t('{expirychecker:warning:warning}', [
        '%ACCOUNTNAME%' => htmlspecialchars($this->data['accountName']),
        '%DAYS%' => $this->t('{expirychecker:warning:days}'),
        '%DAYSLEFT%' => htmlspecialchars($this->data['daysleft']),
    ]);
}

$this->data['autofocus'] = 'yesbutton';

$this->includeAtTemplateBase('includes/header.php');

?>

<h3><?php echo $warning; ?></h3>
<p><?php echo $this->t('{expirychecker:warning:expiry_date_text}') . " " . $this->data['expireOnDate']; ?></p>


<form style="display: inline; margin: 0px; padding: 0px" action="<?php echo htmlspecialchars($this->data['formTarget']); ?>">

        <?php
                // Embed hidden fields...
                foreach ($this->data['formData'] as $name => $value) {
                        echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
                }
        ?>

    <input type="submit" name="changepwd" id="send" style="width:170px;" value="<?php echo htmlspecialchars($this->t('{expirychecker:warning:password_change_title}')) ?>" />

</form>         

<form style="display: inline; margin: 0px; padding: 0px" action="<?php echo htmlspecialchars($this->data['formTarget']); ?>">

  <?php
    // Embed hidden fields...
    foreach ($this->data['formData'] as $name => $value) {
      echo('<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />');
    }
  ?>
  <input type="submit" name="continue" id="send" style="width:250px; border:0" value="<?php echo htmlspecialchars($this->t('{expirychecker:warning:btn_continue}')) ?>" />

</form>
<br>
<div id="trouble"></div>

<?php

$this->includeAtTemplateBase('includes/footer.php');
