<?php

$this->data['header'] = sprintf(
    'Your password will expire in %s %s',
    $this->data['daysleft'],
    $this->data['dayOrDays']
);
$this->data['autofocus'] = 'yesbutton';

$this->includeAtTemplateBase('includes/header.php');

?>
<p>
  The password for your <?= htmlentities($this->data['accountName']); ?>
  account will expire on <?= htmlentities($this->data['expireOnDate']); ?>.
</p>
<p>
  Would you like to update your password now?
</p>

<form action="<?= htmlspecialchars($this->data['formTarget']); ?>">
  
    <?php foreach ($this->data['formData'] as $name => $value): ?>
        <input type="hidden"
               name="<?= htmlspecialchars($name); ?>"
               value="<?= htmlspecialchars($value); ?>" />
    <?php endforeach; ?>
    
    <button type="submit" id="yesbutton" name="changepwd"
            style="padding: 4px 8px;">
        Yes, update password
    </button>
    
    <button type="submit" id="nobutton" name="continue"
            style="padding: 4px 8px;">
        No, continue where I was going
    </button>
</form>
<?php

$this->includeAtTemplateBase('includes/footer.php');
